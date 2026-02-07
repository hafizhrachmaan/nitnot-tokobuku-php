<?php
// File: hrd-dashboard.php
session_start();

// --- AUTHENTICATION GUARD ---
// Check if user is logged in and is an HRD
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HRD') {
    header('Location: /login');
    exit();
}
// --- END AUTHENTICATION GUARD ---

// --- DATABASE & DATA FETCHING (Simulated) ---
// In a real app, you would fetch this data from the database.
// For now, we use placeholder data to avoid errors.
require __DIR__ . '/database.php';

// Fetch the username from session
$username = $_SESSION['username'] ?? 'HRD User';

// Fetch pending employees
$stmt_pending = $pdo->query("SELECT * FROM users WHERE status = 'PENDING'");
$pendingEmployees = $stmt_pending->fetchAll();

// Fetch verified employees (non-HRD)
$stmt_verified = $pdo->query("SELECT id, username, role FROM users WHERE status = 'VERIFIED' AND role != 'HRD'");
$verifiedEmployees = $stmt_verified->fetchAll();

// Define all possible roles for the 'Add Employee' form
$allRoles = ['KASIR', 'STOCKER', 'HRD'];

// Handle notifications from URL parameters
$successMessage = $_GET['success'] ?? null;
$errorMessage = $_GET['error'] ?? null;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>HRD Dashboard - NITNOT TOKO ATK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .logo-font { font-family: 'Playfair Display', serif; }
        .body-font { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(-45deg, #a881af, #f8c9d4, #a3d8f4, #b9fbc0);
            background-size: 400% 400%;
            animation: moveGradient 12s ease infinite;
            color: #1e293b;
        }
        @keyframes moveGradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 2rem; }
        .glass-input { background: rgba(255, 255, 255, 0.3) !important; border: 1px solid rgba(255, 255, 255, 0.4) !important; backdrop-filter: blur(5px); }
        .nav-glass { background: rgba(0, 31, 63, 0.7); backdrop-filter: blur(10px); }
        .nav-link { position: relative; transition: all 0.3s; color: rgba(255,255,255,0.6); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
        .nav-link.active { color: white; border-bottom: 2px solid white; padding-bottom: 4px; }
        @media (max-width: 640px) { .desktop-table { display: none; } }
        @media (min-width: 641px) { .mobile-view { display: none; } }
    </style>
</head>
<body class="body-font">

<nav class="nav-glass text-white shadow-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="flex flex-col">
                <span class="logo-font text-xl leading-none">NITNOT</span>
                <span class="text-[8px] uppercase tracking-widest text-blue-300 font-bold mt-1">Sistem HRD</span>
            </div>
            <div class="flex space-x-4 ml-4">
                <a href="/hrd-dashboard" class="nav-link active">Dasbor</a>
                <a href="/hrd-products" class="nav-link">Produk</a>
                <a href="/hrd-transactions" class="nav-link">Transaksi</a>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-right hidden sm:block">
                <p class="text-[8px] text-blue-200 uppercase font-black">Akses Admin</p>
                <p class="text-xs font-bold leading-none"><?= htmlspecialchars($username) ?></p>
            </div>
            <form action="/logout.php" method="post" class="m-0">
                <button type="submit" class="w-9 h-9 bg-red-500/80 hover:bg-red-600 rounded-full flex items-center justify-center shadow-lg transition-all border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto mt-8 px-4 pb-12">
    <!-- Notifikasi Sukses/Error -->
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-emerald-500/80 text-white font-black py-3 px-5 rounded-xl shadow-lg mb-8 transition-all">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-rose-500/80 text-white font-black py-3 px-5 rounded-xl shadow-lg mb-8 transition-all">
            Terjadi kesalahan: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    <!-- Akhir Notifikasi -->

    <div class="glass-card shadow-2xl overflow-hidden mb-8 border border-white/40">
        <div class="bg-blue-700/60 p-5 flex items-center justify-between backdrop-blur-md">
            <h2 class="text-white font-black text-sm md:text-lg flex items-center uppercase tracking-tight italic">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Permohonan Baru
            </h2>
            <span class="bg-white/30 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest"><?= count($pendingEmployees) ?> Menunggu</span>
        </div>
        
        <!-- Mobile View -->
        <div class="mobile-view p-4 bg-white/10">
            <?php if (empty($pendingEmployees)): ?>
                <div class="py-10 text-center text-slate-700 italic text-sm">Tidak ada permohonan baru.</div>
            <?php endif; ?>
            
            <?php foreach ($pendingEmployees as $employee): ?>
            <div class="bg-white/40 backdrop-blur-md rounded-3xl p-5 mb-4 border border-white/50 shadow-lg">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[9px] font-black text-blue-800 uppercase tracking-tighter"><?= htmlspecialchars($employee['username']) ?></p>
                        <h3 class="font-black text-slate-800 text-base"><?= htmlspecialchars($employee['fullName']) ?></h3>
                        <p class="text-xs text-slate-600 font-medium"><?= htmlspecialchars($employee['email']) ?></p>
                    </div>
                    <span class="bg-blue-600 text-white text-[9px] font-black px-2.5 py-1 rounded-lg uppercase"><?= htmlspecialchars($employee['role']) ?></span>
                </div>
                <div class="flex space-x-2 mt-4">
                    <form action="/hrd_action.php?action=accept&id=<?= $employee['id'] ?>" method="post" class="flex-1">
                        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black py-3 rounded-xl shadow-md uppercase transition-all">Terima</button>
                    </form>
                    <form action="/hrd_action.php?action=reject&id=<?= $employee['id'] ?>" method="post" class="flex-1">
                        <button type="submit" class="w-full bg-white/50 border border-rose-200 text-rose-600 text-[10px] font-black py-3 rounded-xl uppercase transition-all">Tolak</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table overflow-x-auto bg-white/5">
            <table class="min-w-full">
                <thead class="bg-white/20 border-b border-white/30">
                    <tr class="text-left text-[10px] font-black text-slate-700 uppercase tracking-widest">
                        <th class="px-6 py-4 italic">Pelamar</th>
                        <th class="px-6 py-4 italic">Posisi</th>
                        <th class="px-6 py-4 text-center italic">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/20">
                    <?php foreach ($pendingEmployees as $employee): ?>
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-black text-slate-800"><?= htmlspecialchars($employee['fullName']) ?></p>
                            <p class="text-xs text-slate-600 font-medium"><?= htmlspecialchars($employee['username']) ?> • <?= htmlspecialchars($employee['email']) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-600/80 text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase"><?= htmlspecialchars($employee['role']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <form action="/hrd_action.php?action=accept&id=<?= $employee['id'] ?>" method="post">
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black px-4 py-2 rounded-lg transition-transform hover:scale-105 shadow-md">TERIMA</button>
                                </form>
                                <form action="/hrd_action.php?action=reject&id=<?= $employee['id'] ?>" method="post">
                                    <button type="submit" class="bg-white/40 border border-rose-300 text-rose-600 text-[10px] font-black px-4 py-2 rounded-lg hover:bg-rose-500 hover:text-white transition-all">TOLAK</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
            <div class="glass-card p-6 shadow-2xl h-full border border-white/40">
                <h3 class="font-black text-slate-800 text-sm mb-6 flex items-center uppercase tracking-tight italic">
                    <div class="w-8 h-8 bg-blue-600/20 rounded-lg flex items-center justify-center mr-3 text-blue-700"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg></div>
                    Tambah Karyawan
                </h3>
                <form action="/hrd_action.php?action=add" method="post" class="space-y-4">
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase ml-1">Username</label>
                        <input type="text" name="username" class="glass-input w-full rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 text-sm outline-none" placeholder="Username baru..." required>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase ml-1">Password</label>
                        <input type="password" name="password" class="glass-input w-full rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 text-sm outline-none" placeholder="••••••••" required>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase ml-1">Role / Jabatan</label>
                        <select name="role" class="glass-input w-full rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 text-sm outline-none font-bold">
                            <?php foreach ($allRoles as $r): ?>
                                <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-4 rounded-xl shadow-lg active:scale-95 transition-all uppercase tracking-widest text-[10px] mt-2">
                        Simpan Karyawan
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="glass-card p-6 shadow-2xl border border-white/40 h-full">
                <h3 class="font-black text-slate-800 text-sm mb-6 flex items-center uppercase tracking-tight italic">
                    <div class="w-8 h-8 bg-emerald-600/20 rounded-lg flex items-center justify-center mr-3 text-emerald-700"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div>
                    Karyawan Aktif
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-left text-[9px] font-black text-slate-700 uppercase tracking-widest border-b border-white/20">
                                <th class="pb-4">Username</th>
                                <th class="pb-4 text-center">Role</th>
                                <th class="pb-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <?php foreach ($verifiedEmployees as $employee): ?>
                            <tr class="hover:bg-white/20 transition-colors">
                                <td class="py-4">
                                    <p class="font-black text-slate-800 text-sm"><?= htmlspecialchars($employee['username']) ?></p>
                                    <p class="text-[10px] text-slate-600 font-bold">ID: #<?= htmlspecialchars($employee['id']) ?></p>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="bg-white/40 text-slate-700 text-[9px] font-black px-3 py-1 rounded-full uppercase border border-white/50"><?= htmlspecialchars($employee['role']) ?></span>
                                </td>
                                <td class="py-4 text-center">
                                     <form action="/hrd_action.php?action=cut&id=<?= $employee['id'] ?>" method="post">
                                        <button type="submit" class="bg-rose-500/80 hover:bg-rose-600 text-white text-[10px] font-black px-4 py-2 rounded-lg transition-transform hover:scale-105 shadow-md">PECAT</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // This is a placeholder for a more robust icon solution if needed
  });
</script>
</body>
</html>