<?php
// File: database.php

// Database configuration
$host = 'localhost'; // atau '127.0.0.1'
$dbname = 'hrd_app';
$username = 'root'; // Ganti dengan username database Anda
$password = '';     // Ganti dengan password database Anda
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

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
