<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/stripe-config.php';

require_login();

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: ../cart/view.php?message=Your cart is empty");
    exit;
}

$error = '';
$order_id = null;
$payment_intent = null;

<<<<<<< HEAD
// Step 1: Create temporary order (before payment)
if ($_POST && isset($_POST['place_order'])) {
    $delivery_datetime = $_POST['delivery_datetime'];
    $notes = trim($_POST['notes'] ?? '');
=======
// if ($_POST && isset($_POST['place_order'])) {
//     $delivery_datetime = $_POST['delivery_datetime'];
//     $notes = trim($_POST['notes'] ?? '');
>>>>>>> 4023996 (added the payment getway to stripe)
    
//     // Validation
//     if (empty($delivery_datetime)) {
//         $error = 'Please select a delivery date and time';
//     } elseif (strtotime($delivery_datetime) <= time()) {
//         $error = 'Delivery date must be in the future';
//     } else {
//         try {
//             $pdo->beginTransaction();
            
//             // Calculate total
//             $total = 0;
//             foreach ($_SESSION['cart'] as $item) {
//                 $total += $item['price'] * $item['quantity'];
//             }
            
<<<<<<< HEAD
            // Create order with payment_status = 'pending'
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_datetime, total, status, payment_status) VALUES (?, ?, ?, 'pending', 'pending')");
            $stmt->execute([$_SESSION['user']['id'], $delivery_datetime, $total]);
            $order_id = $pdo->lastInsertId();
=======
//             // Create order
//             $stmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_datetime, total, status) VALUES (?, ?, ?, 'pending')");
//             $stmt->execute([$_SESSION['user']['id'], $delivery_datetime, $total]);
//             $order_id = $pdo->lastInsertId();
<<<<<<< HEAD
>>>>>>> 4023996 (added the payment getway to stripe)
=======

>>>>>>> 05da684 (integrated stripe payment flow and moved secret key to env)
            
//             // Add order items
//             $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
//             foreach ($_SESSION['cart'] as $product_id => $item) {
//                 $stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
//             }
            
//             $pdo->commit();
            
<<<<<<< HEAD
            // Create Stripe payment intent
            $payment_result = create_payment_intent($total, $order_id, $_SESSION['user']['email']);
            
            if ($payment_result['success']) {
                $payment_intent = $payment_result;
            } else {
                error_log('Stripe Payment Intent Failed: ' . print_r($payment_result, true));
                $error = 'Payment setup failed: ' . ($payment_result['error'] ?? 'Unknown error');
                // Delete the temporary order
                $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
                $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
                $order_id = null;
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Order creation failed. Please try again.';
            error_log('Checkout Error: ' . $e->getMessage());
        }
=======
//             // Send order confirmation email
//             include '../includes/mailer.php';
//             notify_order_placed($order_id);
            
//             // Clear cart
//             unset($_SESSION['cart']);
            
//             // Redirect to confirmation
//             header("Location: confirm.php?order_id=" . $order_id);
//             exit;
            
//         } catch (Exception $e) {
//             $pdo->rollBack();
//             $error = 'Order placement failed. Please try again.';
//         }
//     }
// }


if ($_POST && isset($_POST['pay_with_stripe'])) {

    require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    $line_items = [];

    foreach ($_SESSION['cart'] as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'lkr',
                'product_data' => [
                    'name' => $item['name'],
                ],
                'unit_amount' => $item['price'] * 100,
            ],
            'quantity' => $item['quantity'],
        ];
>>>>>>> 4023996 (added the payment getway to stripe)
    }

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',

        'success_url' => 'http://localhost/diffindo/order/success.php?order_id=' . $order_id,
        'cancel_url' => 'http://localhost/diffindo/cart/view.php'
    ]);

    header("Location: " . $session->url);
    exit();
}


// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="font-body">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-pink-700">Diffindo</h1>
                    <p class="text-sm text-gray-600">Cakes and Bakes</p>
                </div>
                <div class="flex space-x-4">
                    <a href="../index.php" class="text-pink-600 hover:text-pink-800">Home</a>
                    <a href="../cart/view.php" class="text-pink-600 hover:text-pink-800">Cart</a>
                    <a href="../user/dashboard.php" class="text-pink-600 hover:text-pink-800">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Checkout</h2>
            <p class="text-gray-600">Review your order and schedule delivery</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h3>
                
                <div class="space-y-4 mb-6">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="flex items-center justify-between border-b pb-4">
                            <div class="flex items-center space-x-3">
                                <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="w-12 h-12 object-cover rounded"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCA0OCA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yNCAyMEMyMS43OTA5IDIwIDIwIDIxLjc5MDkgMjAgMjRDMjAgMjYuMjA5MSAyMS43OTA5IDI4IDI0IDI4QzI2LjIwOTEgMjggMjggMjYuMjA5MSAyOCAyNEMyOCAyMS43OTA5IDI2LjIwOTEgMjAgMjQgMjBaIiBmaWxsPSIjOUIxMDhEIi8+Cjwvc3ZnPgo='">
                                <div>
                                    <h4 class="font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="text-sm text-gray-600">Qty: <?= $item['quantity'] ?></p>
                                </div>
                            </div>
                            <p class="font-medium text-gray-800">Rs <?= number_format($item['price'] * $item['quantity']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-800">
                        <span>Total:</span>
                        <span>Rs <?= number_format($cart_total) ?></span>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Delivery Information</h3>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
<<<<<<< HEAD
                <!-- Step 1: Order Details Form -->
                <?php if (!$payment_intent): ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Customer Name</label>
                            <input type="text" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        
                        <div>                                               
                            <label class="block text-gray-700 text-sm font-bold mb-2">Delivery Date & Time *</label>
                            <input type="datetime-local" name="delivery_datetime" required 
                                   min="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-pink-500">
                            <p class="text-xs text-gray-500 mt-1">Minimum 2 hours from now</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Special Notes (Optional)</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-pink-500"
                                      placeholder="Any special instructions for your order..."></textarea>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="place_order" 
                                    class="w-full bg-pink-600 text-white py-3 px-4 rounded-lg hover:bg-pink-700 font-semibold">
                                Proceed to Payment (Rs <?= number_format($cart_total) ?>)
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="../cart/view.php" class="text-pink-600 hover:text-pink-800 text-sm">
                                ← Back to Cart
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Step 2: Payment Form -->
                    <div id="payment-section" class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-800">Payment Details</h3>
                        
                        <div id="card-element" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white"></div>
                        <div id="card-errors" class="text-red-600 text-sm"></div>
                        
                        <button id="pay-button" 
=======
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Customer Name</label>
                        <input type="text" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>
                    
                    <div>                                               
                        <label class="block text-gray-700 text-sm font-bold mb-2">Delivery Date & Time *</label>
                        <input type="datetime-local" name="delivery_datetime" required 
                               min="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-pink-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 2 hours from now</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Special Notes (Optional)</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-pink-500"
                                  placeholder="Any special instructions for your order..."></textarea>
                    </div>
                    
                    <div class="pt-4">
                        <!-- <button type="submit" name="place_order" 
                                class="w-full bg-pink-600 text-white py-3 px-4 rounded-lg hover:bg-pink-700 font-semibold">
                            Place Order (Rs <?= number_format($cart_total) ?>)
                        </button> -->
                        <button type="submit" name="pay_with_stripe" 
>>>>>>> 4023996 (added the payment getway to stripe)
                                class="w-full bg-pink-600 text-white py-3 px-4 rounded-lg hover:bg-pink-700 font-semibold">
                            Pay Now - Rs <?= number_format($cart_total) ?>
                        </button>
                        
                        <div class="text-center">
                            <a href="../cart/view.php" class="text-pink-600 hover:text-pink-800 text-sm">
                                ← Back to Cart
                            </a>
                        </div>
                    </div>
                    
                    <script src="https://js.stripe.com/v3/"></script>
                    <script>
                        const stripe = Stripe('<?= htmlspecialchars($stripe_config['publishable_key']) ?>');
                        const elements = stripe.elements();
                        const cardElement = elements.create('card');
                        
                        cardElement.mount('#card-element');
                        
                        // Handle card errors
                        cardElement.addEventListener('change', (e) => {
                            const errorDiv = document.getElementById('card-errors');
                            if (e.error) {
                                errorDiv.textContent = e.error.message;
                            } else {
                                errorDiv.textContent = '';
                            }
                        });
                        
                        // Handle payment
                        document.getElementById('pay-button').addEventListener('click', async (e) => {
                            e.preventDefault();
                            
                            const button = e.target;
                            button.disabled = true;
                            button.textContent = 'Processing...';
                            
                            const { error, paymentIntent } = await stripe.confirmCardPayment(
                                '<?= htmlspecialchars($payment_intent['client_secret']) ?>',
                                {
                                    payment_method: {
                                        card: cardElement,
                                        billing_details: {
                                            name: '<?= htmlspecialchars($_SESSION['user']['name']) ?>',
                                            email: '<?= htmlspecialchars($_SESSION['user']['email']) ?>'
                                        }
                                    }
                                }
                            );
                            
                            if (error) {
                                document.getElementById('card-errors').textContent = error.message;
                                button.disabled = false;
                                button.textContent = 'Pay Now - Rs <?= number_format($cart_total) ?>';
                            } else if (paymentIntent.status === 'succeeded') {
                                // Send payment confirmation to server
                                await fetch('/diffindo-cakes-and-bakes/order/process-payment.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        payment_intent_id: paymentIntent.id,
                                        order_id: <?= $order_id ?>
                                    })
                                }).then(response => response.json())
                                  .then(data => {
                                      if (data.success) {
                                        // Clear cart
                                        fetch('/diffindo-cakes-and-bakes/cart/clear.php')
                                            .then(() => {
                                              // Redirect to confirmation
                                              window.location.href = '/diffindo-cakes-and-bakes/order/confirm.php?order_id=' + data.order_id;
                                            });
                                      } else {
                                          document.getElementById('card-errors').textContent = data.error;
                                          button.disabled = false;
                                          button.textContent = 'Pay Now - Rs <?= number_format($cart_total) ?>';
                                      }
                                  });
                            }
                        });
                    </script>
                <?php endif; ?>