<?php
include '../includes/auth.php';

require_login();

// Clear the cart
unset($_SESSION['cart']);

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
