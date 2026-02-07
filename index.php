<?php

// Simple Front Controller / Router

// Get the requested URL path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove query string from path
$path = strtok($path, '?');

// Define routes
switch ($path) {
    case '/':
        // Redirect to login page by default
        header('Location: /login');
        exit();

    case '/login':
        require __DIR__ . '/login.php';
        break;

    case '/register':
        require __DIR__ . '/register.php';
        break;

    // HRD Routes
    case '/hrd-dashboard':
        require __DIR__ . '/hrd-dashboard.php';
        break;
    case '/hrd-products':
        echo "<h1>HRD Products Page</h1><p>Work in progress.</p>";
        break;
    case '/hrd-transactions':
        echo "<h1>HRD Transactions Page</h1><p>Work in progress.</p>";
        break;

    // Kasir Routes
    case '/kasir-dashboard':
        require __DIR__ . '/kasir-dashboard.php';
        break;
    case '/kasir-history':
        echo "<h1>Kasir History Page</h1><p>Work in progress.</p>";
        break;

    // Stocker Routes
    case '/stocker-dashboard':
        require __DIR__ . '/stocker-dashboard.php';
        break;
    case '/edit-product.php': // This is not a clean URL, but matches the form action
        require __DIR__ . '/edit-product.php';
        break;

    default:
        // Handle 404 Not Found
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "The page you are looking for could not be found.";
        break;
}
