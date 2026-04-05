<?php
include '../includes/db.php';
include '../includes/auth.php';

require_support_staff();

$success = '';
$error = '';
$order_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';
$filter = $_GET['filter'] ?? 'all';

// Handle order status updates
if ($action && $order_id) {
    if ($action === 'accept') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            include '../includes/mailer.php';
            notify_order_status_change($order_id, 'accepted');
            $success = 'Order accepted successfully. Customer has been notified.';
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            include '../includes/mailer.php';
            notify_order_status_change($order_id, 'rejected');
            $success = 'Order rejected successfully. Customer has been notified.';
        }
    }
}

// Get order details if viewing a specific order
$order = null;
$order_items = [];
if ($order_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi
                              LEFT JOIN products p ON oi.product_id = p.id
                              WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
    }
}

// Get all orders with filtering
$query = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
          GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '|||') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id";

if ($filter !== 'all') {
    $query .= " WHERE o.status = " . $pdo->quote($filter);
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";
$stmt = $pdo->query($query);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Orders - Support Staff</title>
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
                    <p class="text-sm text-blue-100">Manage Orders</p>
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
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!$order_id): ?>
            <!-- Orders List View -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">All Orders</h2>
                <p class="text-gray-600">Review and manage customer orders</p>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-4 mb-8 flex-wrap">
                <a href="?filter=all" 
                   class="px-4 py-2 rounded-lg font-semibold <?= $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 border border-gray-300' ?>">
                    All Orders
                </a>
                <a href="?filter=pending" 
                   class="px-4 py-2 rounded-lg font-semibold <?= $filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-800 border border-gray-300' ?>">
                    Pending
                </a>
                <a href="?filter=accepted" 
                   class="px-4 py-2 rounded-lg font-semibold <?= $filter === 'accepted' ? 'bg-green-600 text-white' : 'bg-white text-gray-800 border border-gray-300' ?>">
                    Accepted
                </a>
                <a href="?filter=rejected" 
                   class="px-4 py-2 rounded-lg font-semibold <?= $filter === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-gray-800 border border-gray-300' ?>">
                    Rejected
                </a>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $ord['id'] ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div><?= htmlspecialchars($ord['customer_name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($ord['customer_email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?php 
                                            $items = explode('|||', $ord['items']);
                                            echo htmlspecialchars(implode(', ', $items)); 
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Rs. <?= number_format($ord['total'], 2) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?= date('d M, Y - H:i', strtotime($ord['delivery_datetime'])) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php
                                            $status_colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'accepted' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-gray-100 text-gray-800'
                                            ];
                                            $color = $status_colors[$ord['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                                                <?= ucfirst($ord['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="?id=<?= $ord['id'] ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                                View <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No orders found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Order Detail View -->
            <?php if ($order): ?>
                <div class="mb-8">
                    <a href="orders.php" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                    </a>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Order #<?= $order['id'] ?></h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Details -->
                    <div class="lg:col-span-2">
                        <!-- Customer Information -->
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Name</p>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_email']) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Phone</p>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Address</p>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_address'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Order Items</h3>
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="py-4 flex justify-between items-center">
                                        <div class="flex items-center gap-4">
                                            <?php if ($item['image']): ?>
                                                <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                     class="w-16 h-16 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <i class="fas fa-cake text-gray-400 text-xl"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></p>
                                                <p class="text-sm text-gray-600">Quantity: <?= $item['quantity'] ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-900">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                            <p class="text-sm text-gray-600"><?= number_format($item['price'], 2) ?> each</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Delivery Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Delivery Date & Time</p>
                                    <p class="font-semibold text-gray-900"><?= date('d M, Y - H:i', strtotime($order['delivery_datetime'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Order Date</p>
                                    <p class="font-semibold text-gray-900"><?= date('d M, Y - H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar - Order Summary & Actions -->
                    <div>
                        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h3>
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-semibold">Rs. <?= number_format($order['total'], 2) ?></span>
                                </div>
                                <div class="border-t border-gray-200 pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-900">Total</span>
                                        <span class="text-2xl font-bold text-pink-600">Rs. <?= number_format($order['total'], 2) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-2">Current Status</p>
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'accepted' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800'
                                ];
                                $color = $status_colors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-4 py-2 rounded-lg text-sm font-semibold <?= $color ?> w-full block text-center">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <?php if ($order['status'] === 'pending'): ?>
                                <div class="space-y-3">
                                    <a href="?id=<?= $order['id'] ?>&action=accept" 
                                       class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition text-center"
                                       onclick="return confirm('Accept this order?');">
                                        <i class="fas fa-check mr-2"></i> Accept Order
                                    </a>
                                    <a href="?id=<?= $order['id'] ?>&action=reject" 
                                       class="block w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition text-center"
                                       onclick="return confirm('Reject this order? The customer will be notified.');">
                                        <i class="fas fa-times mr-2"></i> Reject Order
                                    </a>
                                </div>
                            <?php elseif ($order['status'] === 'accepted'): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p class="text-sm text-green-700">
                                        <i class="fas fa-check-circle mr-2"></i> This order has been accepted
                                    </p>
                                </div>
                            <?php elseif ($order['status'] === 'rejected'): ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-sm text-red-700">
                                        <i class="fas fa-times-circle mr-2"></i> This order has been rejected
                                    </p>
                                </div>
                            <?php endif; ?>

                            <a href="orders.php" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition text-center mt-3">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    Order not found
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
