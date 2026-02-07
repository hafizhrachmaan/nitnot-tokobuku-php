<?php
// File: database.php

<?php
// File: database.php

// Simple .env file loader
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
        }
    }
}

// Database configuration using environment variables with defaults
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_DATABASE'] ?? 'hrd_app';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

// Options for PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // If connection fails, stop the script and show an error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Now, the $pdo variable can be used in other files to interact with the database.
// For example:
// require __DIR__ . '/database.php';
// $stmt = $pdo->query('SELECT name FROM products');
// while ($row = $stmt->fetch()) {
//     echo $row['name'] . '<br>';
// }
