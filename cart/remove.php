<?php
include '../includes/db.php';
include '../includes/auth.php';

if (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $product_name = $_SESSION['cart'][$product_id]['name'];
        unset($_SESSION['cart'][$product_id]);
        $message = "Removed {$product_name} from cart.";
    } else {
        $message = "Item not found in cart.";
    }
} else {
    $message = "Invalid request.";
}

header("Location: view.php?message=" . urlencode($message));
exit;
?>