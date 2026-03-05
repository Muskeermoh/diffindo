<?php
include '../includes/db.php';
include '../includes/auth.php';

if (!is_admin()) {
    header("Location: login.php");
    exit;
}

// Get statistics
$stats = [];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch()['count'];

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['count'];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $stmt->fetch()['count'];

// Total revenue (include accepted and delivered orders)
$stmt = $pdo->query("SELECT SUM(total) as revenue FROM orders WHERE status IN ('accepted','delivered')");
$stats['total_revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent orders
// Ensure GROUP_CONCAT can hold longer lists and use a safe separator
$pdo->exec("SET SESSION group_concat_max_len = 10000");
$stmt = $pdo->query("SELECT o.*, u.name as customer_name, u.email as customer_email,\n           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '|||') as items\n    FROM orders o\n    JOIN users u ON o.user_id = u.id\n    LEFT JOIN order_items oi ON o.id = oi.order_id\n    LEFT JOIN products p ON oi.product_id = p.id\n    GROUP BY o.id\n    ORDER BY o.created_at DESC\n    LIMIT 10");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-300">Diffindo (Cakes and Bakes)</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300 font-bold">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="image-manager.php" class="text-white hover:text-gray-300">Images</a>
                    <a href="../reach-us.php" class="text-white hover:text-gray-300">Reach Us</a>
                    <!-- <a href="email-monitor.php" class="text-white hover:text-gray-300">Email Monitor</a>
                    <a href="email-test.php" class="text-white hover:text-gray-300">Email Test</a> -->
                    <a href="../index.php" class="text-white hover:text-gray-300">View Site</a>
                    <a href="../logout.php" class="text-white hover:text-gray-300">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Overview</h2>
            <p class="text-gray-600">Manage your cake business</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Orders</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Pending Orders</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['pending_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Products</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_products'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Revenue</h3>
                        <p class="text-2xl font-bold text-gray-900">Rs <?= number_format($stats['total_revenue']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="products.php?action=add" class="bg-blue-600 text-white p-6 rounded-lg hover:bg-blue-700 text-center">
                <h4 class="text-lg font-semibold mb-2">Add New Product</h4>
                <p class="text-blue-100">Create a new cake product</p>
            </a>
            <a href="orders.php" class="bg-yellow-600 text-white p-6 rounded-lg hover:bg-yellow-700 text-center">
                <h4 class="text-lg font-semibold mb-2">Manage Orders</h4>
                <p class="text-yellow-100">Review and process orders</p>
            </a>
            <a href="products.php" class="bg-green-600 text-white p-6 rounded-lg hover:bg-green-700 text-center">
                <h4 class="text-lg font-semibold mb-2">Manage Products</h4>
                <p class="text-green-100">Edit and update cake listings</p>
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($recent_orders)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500">No orders yet.</p>
                    </div>
                <?php else: ?>
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <a href="orders.php?view=<?= $order['id'] ?>" class="text-blue-600 hover:text-blue-800">
                                            #<?= $order['id'] ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php
                                        // recent_orders uses '|||' as separator; render as comma-separated one-liner
                                        $items_display = isset($order['items']) ? htmlspecialchars(str_replace('|||', ', ', $order['items'])) : '';
                                        echo $items_display;
                                    ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900">Rs <?= number_format($order['total']) ?></td>
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
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>