<?php
include '../includes/db.php';
include '../includes/auth.php';

require_login();

// Check if user is customer - redirect if not
if (!is_customer()) {
    $role = get_user_role();
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($role === 'support_staff') {
        header("Location: ../support/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

// Get user orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Dashboard - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .font-heading { font-family: 'Playfair Display', serif; }
        .font-body { font-family: 'Inter', sans-serif; }
        .navbar-blur { backdrop-filter: blur(10px); background: rgba(253, 242, 248, 0.9); }
    </style>
</head>
<body class="bg-gray-100 font-body">
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

            <!-- Mobile Menu (hidden by default) -->
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>
            <p class="text-gray-600">Manage your orders and account information</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Orders</h3>
                <p class="text-3xl font-bold text-pink-600"><?= count($orders) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Pending Orders</h3>
                <p class="text-3xl font-bold text-yellow-600">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })) ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Accepted Orders</h3>
                <p class="text-3xl font-bold text-green-600">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === 'accepted' || $o['status'] === 'delivered'; })) ?>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Spent</h3>
                <p class="text-3xl font-bold text-blue-600">
                    Rs <?= number_format(array_sum(array_column(array_filter($orders, function($o) { 
                        return $o['status'] === 'accepted' || $o['status'] === 'delivered'; 
                    }), 'total'))) ?>
                </p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($orders)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
                        <a href="../index.php" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">
                            Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach (array_slice($orders, 0, 10) as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($order['items']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900">Rs <?= number_format($order['total']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('M j, Y g:i A', strtotime($order['delivery_datetime'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'delivered' => 'bg-blue-100 text-blue-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-gray-100 text-gray-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$order['status']] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <a href="../order/cancel.php?id=<?= $order['id'] ?>" 
                                               class="text-red-600 hover:text-red-800"
                                               onclick="return confirm('Are you sure you want to cancel this order?')">
                                                Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="../index.php" class="bg-pink-600 text-white p-6 rounded-lg hover:bg-pink-700 text-center">
                <h4 class="text-lg font-semibold mb-2">Browse Cakes</h4>
                <p class="text-pink-100">Explore our delicious cake collection</p>
            </a>
            <a href="../cart/view.php" class="bg-blue-600 text-white p-6 rounded-lg hover:bg-blue-700 text-center">
                <h4 class="text-lg font-semibold mb-2">View Cart</h4>
                <p class="text-blue-100">Check items in your shopping cart</p>
            </a>
            <a href="orders.php" class="bg-green-600 text-white p-6 rounded-lg hover:bg-green-700 text-center">
                <h4 class="text-lg font-semibold mb-2">Order History</h4>
                <p class="text-green-100">View all your past orders</p>
            </a>
        </div>
    </div>
        <script>
            // Mobile menu toggle used by the navbar
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