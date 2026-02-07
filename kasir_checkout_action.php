<?php
// File: kasir_checkout_action.php
session_start();

// --- Authentication Guard ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'KASIR') {
    header('Location: /login?error=unauthorized');
    exit();
}

// --- Cart validation ---
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    header('Location: /kasir-dashboard?error=cart_empty');
    exit();
}

require __DIR__ . '/database.php';

$cart = $_SESSION['cart'];
$userId = $_SESSION['user_id'];
$totalPrice = $cart['total'];

// --- Database Transaction ---
try {
    $pdo->beginTransaction();

    // 1. Insert into 'transactions' table
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, total_price, transaction_date) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $totalPrice]);
    $transactionId = $pdo->lastInsertId();

    // Prepare statements for loops
    $detailStmt = $pdo->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart['items'] as $item) {
        $productId = $item['product']['id'];
        $quantity = $item['quantity'];
        $price = $item['product']['price'];

        // 2. Insert into 'transaction_details' table
        $detailStmt->execute([$transactionId, $productId, $quantity, $price]);
        
        // 3. Update product stock
        $stockStmt->execute([$quantity, $productId]);
    }

    // 4. If all queries were successful, commit the transaction
    $pdo->commit();

    // 5. Clear the cart from the session and redirect
    unset($_SESSION['cart']);
    header('Location: /kasir-dashboard?success=checkout_complete');

} catch (\PDOException $e) {
    // If any query fails, roll back the entire transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redirect with an error. In a real app, log the error.
    header('Location: /kasir-dashboard?error=' . urlencode('Checkout failed: ' . $e->getMessage()));
}

exit();
