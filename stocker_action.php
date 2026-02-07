<?php
// File: stocker_action.php
session_start();

// --- AUTHENTICATION GUARD ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'STOCKER') {
    header('Location: /login');
    exit();
}

require __DIR__ . '/database.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $name = $_POST['name'] ?? null;
            $description = $_POST['description'] ?? '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

            if (!$name || $price <= 0) {
                header('Location: /stocker-dashboard?error=Invalid data');
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stock]);
            
            header('Location: /stocker-dashboard?success=Product added successfully');
            break;

        case 'update':
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? null;
            $description = $_POST['description'] ?? '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

            if (!$id || !$name || $price <= 0) {
                header('Location: /stocker-dashboard?error=Invalid data for update');
                exit();
            }

            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $stock, $id]);

            header('Location: /stocker-dashboard?success=Product updated successfully');
            break;

        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: /stocker-dashboard?error=Missing product ID');
                exit();
            }

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);

            header('Location: /stocker-dashboard?success=Product deleted successfully');
            break;

        default:
            header('Location: /stocker-dashboard?error=Unknown action');
            break;
    }
} catch (\PDOException $e) {
    header('Location: /stocker-dashboard?error=' . urlencode('Database error: ' . $e->getMessage()));
}

exit();
