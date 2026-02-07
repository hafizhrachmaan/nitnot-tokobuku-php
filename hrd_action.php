<?php
// File: hrd_action.php
session_start();

// --- AUTHENTICATION GUARD ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HRD') {
    header('Content-Type: text/plain');
    echo 'Error: Unauthorized Access';
    exit();
}
// --- END AUTHENTICATION GUARD ---

require __DIR__ . '/database.php';

// Get the action from the URL query string
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // --- ACTION: Accept a pending employee ---
        case 'accept':
            $userId = $_GET['id'] ?? null;
            if ($userId) {
                $stmt = $pdo->prepare("UPDATE users SET status = 'VERIFIED' WHERE id = ? AND status = 'PENDING'");
                $stmt->execute([$userId]);
            }
            header('Location: /hrd-dashboard?success=User has been accepted.');
            break;

        // --- ACTION: Reject (delete) a pending employee ---
        case 'reject':
            $userId = $_GET['id'] ?? null;
            if ($userId) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND status = 'PENDING'");
                $stmt->execute([$userId]);
            }
            header('Location: /hrd-dashboard?success=User has been rejected.');
            break;

        // --- ACTION: Add a new employee ---
        case 'add':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($username) || empty($password) || empty($role)) {
                header('Location: /hrd-dashboard?error=All fields are required for new employee.');
                exit();
            }

            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                header('Location: /hrd-dashboard?error=Username already exists.');
                exit();
            }
            
            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, 'VERIFIED')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hashedPassword, $role]);

            header('Location: /hrd-dashboard?success=New employee has been added.');
            break;

        // --- ACTION: "Cut" (fire) an employee ---
        // We will just delete the user for simplicity. A better approach would be to set status to 'INACTIVE'
        case 'cut':
            $userId = $_GET['id'] ?? null;
            if ($userId) {
                // To prevent deleting other HRDs, add a check
                if ($userId == $_SESSION['user_id']) {
                     header('Location: /hrd-dashboard?error=You cannot fire yourself.');
                     exit();
                }
                
                // Be careful with cascading deletes if foreign keys are set up that way
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'HRD'");
                $stmt->execute([$userId]);
            }
            header('Location: /hrd-dashboard?success=Employee has been removed.');
            break;

        default:
            // If no action is specified, redirect back to the dashboard
            header('Location: /hrd-dashboard?error=Unknown action.');
            break;
    }
} catch (\PDOException $e) {
    // On any database error, redirect with a generic error message
    // In a real production app, log this error instead of showing it to the user
    header('Location: /hrd-dashboard?error=' . urlencode('Database error: ' . $e->getMessage()));
}

exit();
