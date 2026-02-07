<?php
// File: login_process.php

// ALWAYS start the session at the very beginning of the file
session_start();

// Include the database connection
require __DIR__ . '/database.php';

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get data from the form
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 2. Basic validation
    if (empty($username) || empty($password)) {
        header('Location: /login?error=1');
        exit();
    }

    // 3. Find the user in the database
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // 4. Verify user and password
        // The original project used bcrypt, so password_verify is the correct function.
        if ($user && password_verify($password, $user['password'])) {

            // 5. Check if the user account is VERIFIED
            if ($user['status'] !== 'VERIFIED') {
                // Account is PENDING, redirect with an error
                // We can create a more specific error later if needed
                header('Location: /login?error=pending');
                exit();
            }

            // 6. Login successful, store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // 7. Redirect to the correct dashboard based on role
            switch ($user['role']) {
                case 'HRD':
                    header('Location: /hrd-dashboard');
                    exit();
                case 'KASIR':
                    header('Location: /kasir-dashboard');
                    exit();
                case 'STOCKER':
                    header('Location: /stocker-dashboard');
                    exit();
                default:
                    // If role is unknown, log out and show an error
                    session_destroy();
                    header('Location: /login?error=1');
                    exit();
            }

        } else {
            // Login failed (user not found or password incorrect)
            header('Location: /login?error=1');
            exit();
        }

    } catch (\PDOException $e) {
        // Handle database errors
        die("Database error during login: " . $e->getMessage());
    }

} else {
    // If not a POST request, redirect to login
    header('Location: /login');
    exit();
}
