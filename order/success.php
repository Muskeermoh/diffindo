<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Cart is empty.";
    exit;
}

try {

    $pdo->beginTransaction();

    $total = 0;

    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'paid')");
    $stmt->execute([$_SESSION['user']['id'], $total]);

    $order_id = $pdo->lastInsertId();

    // Save order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt->execute([
            $order_id,
            $product_id,
            $item['quantity'],
            $item['price']
        ]);
    }

    $pdo->commit();

    // Clear cart
    unset($_SESSION['cart']);

} catch (Exception $e) {

    $pdo->rollBack();
    echo "Order failed.";
    exit;
}

?>

<h2>🎉 Payment Successful</h2>
<p>Your order has been placed successfully.</p>

<p><b>Order ID:</b> <?= $order_id ?></p>
<a href="../index.php">Back to Home</a>