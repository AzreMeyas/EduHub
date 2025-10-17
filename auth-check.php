<?php
// auth-check.php
// Include this file at the top of any protected page

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Optional: Check if user has the right role for this page
function checkRole($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        // Redirect to appropriate dashboard
        $redirect = ($_SESSION['role'] === 'teacher') ? 'tdashboard.php' : 'sdashboard.php';
        header("Location: $redirect");
        exit();
    }
}
?>