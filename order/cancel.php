<?php
include '../includes/db.php';
include '../includes/auth.php';

require_login();

$order_id = $_GET['id'] ?? 0;
$message = '';

// Verify order belongs to user and can be cancelled
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$order_id, $_SESSION['user']['id']]);
$order = $stmt->fetch();

if ($order) {
    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $message = "Order #$order_id has been cancelled successfully.";
    } else {
        $message = "Failed to cancel order. Please try again.";
    }
} else {
    $message = "Order not found or cannot be cancelled.";
}

// Redirect back to orders page
header("Location: ../user/orders.php?message=" . urlencode($message));
exit;
?>