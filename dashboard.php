<?php
include 'includes/db.php';
include 'includes/auth.php';

// If not logged in, redirect to login
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Route to correct dashboard based on role
$role = get_user_role();

if ($role === 'admin') {
    header("Location: admin/dashboard.php");
} elseif ($role === 'support_staff') {
    header("Location: support/dashboard.php");
} elseif ($role === 'customer') {
    header("Location: user/dashboard.php");
} else {
    // Fallback for unknown roles
    header("Location: index.php");
}
exit;
?>
