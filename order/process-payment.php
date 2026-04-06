<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/stripe-config.php';

require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $payment_intent_id = $data['payment_intent_id'] ?? null;
    $order_id = $data['order_id'] ?? null;
    
    if (!$payment_intent_id || !$order_id) {
        throw new Exception('Missing payment intent ID or order ID');
    }
    
    // Verify order belongs to current user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user']['id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Confirm payment with Stripe
    $payment_result = confirm_payment_intent($payment_intent_id);
    
    if (!$payment_result['success']) {
        throw new Exception('Payment failed: ' . $payment_result['error']);
    }
    
    // Update order with payment details
    $stmt = $pdo->prepare("UPDATE orders SET 
        stripe_payment_intent_id = ?, 
        stripe_charge_id = ?,
        payment_status = 'completed'
        WHERE id = ?");
    $stmt->execute([
        $payment_intent_id,
        $payment_result['charge_id'],
        $order_id
    ]);
    
    // Send payment confirmation email
    include '../includes/mailer.php';
    notify_order_placed($order_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment successful',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    error_log('Payment Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
