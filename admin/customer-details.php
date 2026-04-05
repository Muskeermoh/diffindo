<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

$customer_id = $_GET['id'] ?? null;

if (!$customer_id) {
    header("Location: customers.php");
    exit;
}

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit;
}

// Get customer orders
$pdo->exec("SET SESSION group_concat_max_len = 10000");
$stmt = $pdo->prepare("SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '|||') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();

// Get customer statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$stmt->execute([$customer_id]);
$total_orders = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT SUM(total) as amount FROM orders WHERE user_id = ?");
$stmt->execute([$customer_id]);
$total_spent = $stmt->fetch()['amount'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'accepted'");
$stmt->execute([$customer_id]);
$completed_orders = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$customer_id]);
$pending_orders = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Details - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-300">Customer Details</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="customers.php" class="text-white hover:text-gray-300">Customers</a>
                    <a href="support-staff.php" class="text-white hover:text-gray-300">Support Staff</a>
                    <a href="image-manager.php" class="text-white hover:text-gray-300">Images</a>
                    <a href="../reach-us.php" class="text-white hover:text-gray-300">Reach Us</a>
                    <a href="../index.php" class="text-white hover:text-gray-300">View Site</a>
                    <a href="../logout.php" class="text-white hover:text-gray-300">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Back Button -->
        <a href="customers.php" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Customers
        </a>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <?= htmlspecialchars($customer['name']) ?>
            </h2>
            <p class="text-gray-600">Customer ID: #<?= $customer['id'] ?></p>
        </div>

        <!-- Customer Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Main Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Personal Information</h3>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Full Name</label>
                            <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($customer['name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Email Address</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <a href="mailto:<?= htmlspecialchars($customer['email']) ?>" class="text-blue-600 hover:text-blue-900">
                                    <?= htmlspecialchars($customer['email']) ?>
                                </a>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Phone Number</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php if ($customer['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($customer['phone']) ?>" class="text-blue-600 hover:text-blue-900">
                                        <?= htmlspecialchars($customer['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500">Not provided</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Member Since</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <?= date('d M, Y', strtotime($customer['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Order Statistics</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Orders</span>
                            <span class="text-2xl font-bold text-gray-900"><?= $total_orders ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Completed</span>
                            <span class="text-2xl font-bold text-green-600"><?= $completed_orders ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Pending</span>
                            <span class="text-2xl font-bold text-yellow-600"><?= $pending_orders ?></span>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <span class="text-gray-600 font-medium">Total Spent</span>
                            <span class="text-2xl font-bold text-blue-600">Rs. <?= number_format($total_spent, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Order History</h3>
            </div>

            <?php if (count($orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Delivery Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php 
                                        $items = explode('|||', $order['items']);
                                        $items_text = implode(', ', array_filter($items));
                                        echo htmlspecialchars($items_text ?: 'No items'); 
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
                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                            'delivered' => 'bg-blue-100 text-blue-800'
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
            <?php else: ?>
                <div class="px-6 py-4 text-center text-gray-500">
                    <i class="fas fa-shopping-bag text-3xl text-gray-300 mb-2 block"></i>
                    This customer has not placed any orders yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
