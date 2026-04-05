<?php
include '../includes/db.php';
include '../includes/auth.php';

require_support_staff();

// Get statistics
$stats = [];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch()['count'];

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['count'];

// Accepted orders (orders handled by support staff)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'accepted'");
$stats['accepted_orders'] = $stmt->fetch()['count'];

// Rejected orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'rejected'");
$stats['rejected_orders'] = $stmt->fetch()['count'];

// Recent orders
$pdo->exec("SET SESSION group_concat_max_len = 10000");
$stmt = $pdo->query("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '|||') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 15");
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Staff Dashboard - Diffindo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Support Staff Dashboard</h1>
                    <p class="text-sm text-blue-100">Diffindo (Cakes and Bakes)</p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="text-white text-sm">
                        <p class="font-medium"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                        <p class="text-blue-200">Support Staff</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="dashboard.php" class="text-white hover:text-blue-200 font-bold">Dashboard</a>
                        <a href="orders.php" class="text-white hover:text-blue-200">Manage Orders</a>
                        <a href="../index.php" class="text-white hover:text-blue-200">View Site</a>
                        <a href="../logout.php" class="text-white hover:text-blue-200">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Overview</h2>
            <p class="text-gray-600">Monitor and manage customer orders</p>
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
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Accepted Orders</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['accepted_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M10 14l-2-2m0 0L5.586 9.586a2 2 0 010-2.828l.828-.828a2 2 0 012.828 0L10 9.172m0 0l2-2m-2 2L9.586 4.414a2 2 0 012.828 0l.828.828a2 2 0 010 2.828L10 14m4 0l2 2m0 0l2.828 2.828a2 2 0 002.828 0l.828-.828a2 2 0 000-2.828L16 14m0 0l2-2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Rejected Orders</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['rejected_orders'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Recent Orders</h3>
                <a href="orders.php" class="text-blue-600 hover:text-blue-900 font-medium">
                    View All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Delivery Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($recent_orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($order['customer_email']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php 
                                    $items = explode('|||', $order['items']);
                                    echo htmlspecialchars(implode(', ', $items)); 
                                    ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rs. <?= number_format($order['total'], 2) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('d M, Y - H:i', strtotime($order['delivery_datetime'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'accepted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $color = $status_colors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="orders.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                        View <i class="fas fa-external-link-alt ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
