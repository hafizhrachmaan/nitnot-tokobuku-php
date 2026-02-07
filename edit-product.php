<?php
// File: edit-product.php
session_start();

// --- AUTHENTICATION GUARD ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'STOCKER') {
    header('Location: /login');
    exit();
}
// --- END AUTHENTICATION GUARD ---

require __DIR__ . '/database.php';

// Get Product ID from URL
$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: /stocker-dashboard?error=missing_id');
    exit();
}

// Fetch the product from the database
$stmt = $pdo->prepare("SELECT id, name, description, price, stock FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

// If product not found, redirect back
if (!$product) {
    header('Location: /stocker-dashboard?error=product_not_found');
    exit();
}

$username = $_SESSION['username'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Produk - NITNOT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/lucide.min.js"></script>
    <style>
        .logo-font { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); min-height: 100vh; }
        .glass-nav { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .premium-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 2.5rem; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); }
        .input-field { background: rgba(248, 250, 252, 0.8); border: 1px solid #e2e8f0; }
        .input-field:focus { background: white; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="body-font">

<nav class="glass-nav text-white px-4 md:px-10 py-5 sticky top-0 z-50 shadow-2xl">
    <div class="max-w-[1600px] mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-6">
            <div class="flex flex-col border-r border-white/10 pr-6">
                <h1 class="logo-font text-xl md:text-2xl font-black tracking-tighter leading-none">NITNOT</h1>
                <p class="text-[8px] tracking-[0.3em] text-blue-400 font-bold uppercase mt-1">Inventaris</p>
            </div>
             <a href="/stocker-dashboard" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-white">
                &larr; Kembali ke Dasbor
            </a>
        </div>
        <div class="flex items-center space-x-4">
             <div class="text-right hidden sm:block">
                <p class="text-[8px] text-blue-400 uppercase font-black">Stokis</p>
                <p class="text-xs font-bold leading-none"><?= htmlspecialchars($username) ?></p>
            </div>
            <form action="/logout.php" method="post" class="m-0">
                <button type="submit" class="w-9 h-9 bg-red-500/80 hover:bg-red-600 rounded-full flex items-center justify-center shadow-lg transition-all border border-white/20">
                    <i data-lucide="power" class="w-4 h-4 text-white"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="max-w-3xl mx-auto p-5 md:p-10">
    <div class="premium-card overflow-hidden">
        <div class="bg-slate-900 p-6 text-center">
            <h2 class="text-white text-xs font-black uppercase tracking-[0.3em]">Edit Produk: <?= htmlspecialchars($product['name']) ?></h2>
        </div>
        
        <form action="stocker_action.php?action=update" method="post" class="p-8 space-y-5">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>" />
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-4">Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required
                       class="input-field w-full rounded-2xl p-4 text-sm font-semibold outline-none transition-all">
            </div>
            
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-4">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="input-field w-full rounded-2xl p-4 text-sm font-semibold outline-none resize-none transition-all"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-4">Harga</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required
                           class="input-field w-full rounded-2xl p-4 text-sm font-black outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-4">Stok</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required
                           class="input-field w-full rounded-2xl p-4 text-sm font-black outline-none transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl uppercase text-xs tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95 mt-4 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Perbarui Produk
            </button>
        </form>
    </div>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>