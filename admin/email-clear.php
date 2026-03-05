<?php
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$log_file = '../logs/emails.log';
if (file_exists($log_file)) {
    file_put_contents($log_file, '');
}

header('Location: email-monitor.php?message=Email log cleared successfully');
exit;
?>