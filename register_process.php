<?php
// File: register_process.php

// Include the database connection file
require __DIR__ . '/database.php';

// Check if the form was submitted using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- 1. Get Data From Form ---
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $lastEducation = $_POST['lastEducation'] ?? '';
    $role = $_POST['role'] ?? ''; // KASIR or STOCKER
    $workExperience = $_POST['workExperience'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // --- 2. Validation (Simple) ---
    if (empty($username) || empty($password) || empty($fullName) || empty($email) || empty($role)) {
        // Basic validation: ensure required fields are not empty
        header('Location: /register?error=missing_fields');
        exit();
    }

    // --- 3. Check if username already exists ---
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            // Username exists, redirect back with an error
            header('Location: /register?error=username_taken');
            exit();
        }
    } catch (\PDOException $e) {
        // Handle database errors
        die("Database error during username check: " . $e->getMessage());
    }

    // --- 4. Hash the password for security ---
    // The original project used bcrypt ($2a$), so we use PASSWORD_BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // --- 5. Insert the new user into the database ---
    try {
        $sql = "INSERT INTO users (fullName, email, phone, address, lastEducation, role, workExperience, username, password, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $fullName,
            $email,
            $phone,
            $address,
            $lastEducation,
            $role,
            $workExperience,
            $username,
            $hashedPassword
        ]);

        // --- 6. Redirect on Success ---
        // Redirect to login page with a success message for the user
        header('Location: /login?registrationSuccess=1');
        exit();

    } catch (\PDOException $e) {
        // Handle potential database errors during insertion
        die("Database error during registration: " . $e->getMessage());
    }

} else {
    // If not a POST request, just redirect to the register page
    header('Location: /register');
    exit();
}
