<?php
include '../includes/db.php';
include '../includes/auth.php';

require_login();

// Get user orders with detailed information
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(
               CONCAT(p.name, ' (x', oi.quantity, ' @ Rs', oi.price, ')') 
               SEPARATOR '<br>'
           ) as items_detail
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <title>My Orders - Diffindo (Cakes and Bakes)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .font-heading { font-family: 'Playfair Display', serif; }
            .font-body { font-family: 'Inter', sans-serif; }
            .navbar-blur { backdrop-filter: blur(10px); background: rgba(253, 242, 248, 0.9); }
        </style>
</head>
<body class="bg-pink-50 font-body">
        <nav class="navbar-blur sticky top-0 z-50 border-b border-pink-200/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-birthday-cake text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="font-heading text-2xl font-bold text-gray-800">Diffindo</h1>
                            <p class="text-sm text-pink-600 font-medium">Cakes & Bakes</p>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center space-x-8">
                        <a href="../index.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Home</a>
                        <a href="../reach-us.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Reach Us</a>
                        <?php $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>
                        <a href="../cart/view.php" class="relative text-gray-700 hover:text-pink-600 font-medium transition-colors flex items-center">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold">
                                    <?= $cart_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <?php if (!is_logged_in()): ?>
                            <a href="../login.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Login</a>
                            <a href="../register.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow font-medium">Sign Up</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors"><i class="fas fa-user-circle mr-2"></i>Dashboard</a>
                            <a href="../logout.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Logout</a>
                        <?php endif; ?>
                    </div>

                    <div class="md:hidden">
                        <button id="mobile-menu-btn" class="text-gray-700 hover:text-pink-600 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="mobile-menu" class="md:hidden hidden bg-white/90 backdrop-blur-sm rounded-lg mt-2 p-4 shadow-xl border border-pink-200">
                <div class="space-y-3">
                      <a href="../index.php" class="block text-gray-700 hover:text-pink-600 font-medium">Home</a>
                      <a href="../reach-us.php" class="block text-gray-700 hover:text-pink-600 font-medium">Reach Us</a>
                    <a href="../cart/view.php" class="block text-gray-700 hover:text-pink-600 font-medium">Cart <?php if ($cart_count > 0): ?>(<?= $cart_count ?>)<?php endif; ?></a>
                    <?php if (!is_logged_in()): ?>
                        <a href="../login.php" class="block text-gray-700 hover:text-pink-600 font-medium">Login</a>
                        <a href="../register.php" class="block text-gray-700 hover:text-pink-600 font-medium">Sign Up</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="block text-gray-700 hover:text-pink-600 font-medium">Dashboard</a>
                        <a href="../logout.php" class="block text-gray-700 hover:text-pink-600 font-medium">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">My Orders</h2>
            <p class="text-gray-600">Track and manage your cake orders</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-500 mb-4">You haven't placed any orders yet. Start browsing our delicious cakes!</p>
                <a href="../index.php" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">
                    Browse Cakes
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Order #<?= $order['id'] ?></h3>
                                <p class="text-sm text-gray-500">
                                    Placed on <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'accepted' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'delivered' => 'bg-blue-100 text-blue-800'
                                ];
                                ?>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusColors[$order['status']] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Items Ordered</h4>
                                    <div class="text-sm text-gray-600">
                                        <?= $order['items_detail'] ?>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Delivery Details</h4>
                                    <p class="text-sm text-gray-600">
                                        <?= date('M j, Y', strtotime($order['delivery_datetime'])) ?><br>
                                        <?= date('g:i A', strtotime($order['delivery_datetime'])) ?>
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Order Total</h4>
                                    <p class="text-lg font-bold text-pink-600">Rs <?= number_format($order['total']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                            <div class="px-6 py-4 bg-gray-50 border-t">
                                <a href="../order/cancel.php?id=<?= $order['id'] ?>" 
                                   class="text-red-600 hover:text-red-800 text-sm font-medium"
                                   onclick="return confirm('Are you sure you want to cancel this order?')">
                                    Cancel Order
                                </a>
                            </div>
                        <?php elseif ($order['status'] === 'rejected'): ?>
                            <div class="px-6 py-4 bg-red-50 border-t">
                                <p class="text-sm text-red-600">
                                    This order has been rejected. Please contact us if you have any questions.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'accepted'): ?>
                            <div class="px-6 py-4 bg-green-50 border-t">
                                <p class="text-sm text-green-600">
                                    Your order has been confirmed! We'll prepare your delicious cakes for delivery.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'delivered'): ?>
                            <div class="px-6 py-4 bg-blue-50 border-t">
                                <p class="text-sm text-blue-600">
                                    Your order has been delivered. We hope you enjoy it! If there are any issues, please contact us.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('mobile-menu-btn');
                var menu = document.getElementById('mobile-menu');
                if (btn && menu) {
                    btn.addEventListener('click', function() {
                        menu.classList.toggle('hidden');
                    });
                }
            });
        </script>
</body>
</html>