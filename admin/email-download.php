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
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="email-log-' . date('Y-m-d') . '.txt"');
    readfile($log_file);
} else {
    echo "No email log found.";
}
?>