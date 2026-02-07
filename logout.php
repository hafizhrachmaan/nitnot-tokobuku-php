<?php
// File: logout.php

// Always start the session to access it
session_start();

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page with a logout message
header('Location: /login?logout=1');
exit();
