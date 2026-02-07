<?php
// File: cart_api.php
header('Content-Type: application/json');
session_start();

// --- Authentication Guard ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'KASIR') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/database.php';

// --- Initialize cart in session if it doesn't exist ---
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0.0,
    ];
}

/**
 * Recalculates the total price of the cart.
 * @param array &$cart The cart array, passed by reference.
 */
function calculate_total(&$cart) {
    $total = 0.0;
    foreach ($cart['items'] as &$item) {
        $item['subtotal'] = $item['product']['price'] * $item['quantity'];
        $total += $item['subtotal'];
    }
    $cart['total'] = $total;
}

$action = $_GET['action'] ?? 'get';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($action) {
    case 'add':
        if ($id) {
            // Check if product is already in cart
            if (isset($_SESSION['cart']['items'][$id])) {
                $_SESSION['cart']['items'][$id]['quantity']++;
            } else {
                // Fetch product from DB to ensure it exists and get fresh data
                $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                if ($product) {
                    $_SESSION['cart']['items'][$id] = [
                        'product' => $product,
                        'quantity' => 1,
                        'subtotal' => $product['price']
                    ];
                }
            }
        }
        break;

    case 'decrease':
        if ($id && isset($_SESSION['cart']['items'][$id])) {
            $_SESSION['cart']['items'][$id]['quantity']--;
            if ($_SESSION['cart']['items'][$id]['quantity'] <= 0) {
                unset($_SESSION['cart']['items'][$id]);
            }
        }
        break;

    case 'remove':
        if ($id && isset($_SESSION['cart']['items'][$id])) {
            unset($_SESSION['cart']['items'][$id]);
        }
        break;

    case 'get':
    default:
        // Just fall through to the end to send the current cart state
        break;
}

// Recalculate total and send the final cart state
calculate_total($_SESSION['cart']);
echo json_encode($_SESSION['cart']);
exit();
