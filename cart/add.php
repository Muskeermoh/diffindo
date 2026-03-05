<?php
include '../includes/db.php';
include '../includes/auth.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_POST) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    
    if ($product_id > 0 && $quantity > 0) {
        // Get product details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Add to cart or update quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity
                ];
            }
            
            $message = "Added {$product['name']} to cart!";
        } else {
            $message = "Product not found.";
        }
    } else {
        $message = "Invalid product or quantity.";
    }
}

// Redirect back to the page they came from or to home
$redirect = $_POST['redirect'] ?? '../index.php';
// Make sure redirect path is properly formatted
if (!str_contains($redirect, '/') && !str_contains($redirect, '\\')) {
    $redirect = '../' . $redirect;
}
header("Location: {$redirect}?message=" . urlencode($message));
exit;
?>