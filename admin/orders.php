<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

$message = '';

// Handle order status updates
if ($_POST && isset($_POST['action'])) {
    $order_id = (int) $_POST['order_id'];
    $action = $_POST['action'];
    
    if ($action === 'accept') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            include '../includes/mailer.php';
            notify_order_status_change($order_id, 'accepted');
            $message = "Order #$order_id has been accepted and customer notified.";
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            include '../includes/mailer.php';
            notify_order_status_change($order_id, 'rejected');
            $message = "Order #$order_id has been rejected and customer notified.";
        }
    } elseif ($action === 'delivered') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            include '../includes/mailer.php';
            notify_order_status_change($order_id, 'delivered');
            $message = "Order #$order_id has been marked delivered and customer notified.";
        }
    }
    // Prevent duplicate form submission (Post/Redirect/Get)
    if (!session_id()) session_start();
    if (!empty($message)) {
        $_SESSION['admin_message'] = $message;
    }
    header('Location: orders.php');
    exit;
}

// Pull any flash message set after redirect
if (!session_id()) session_start();
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}

// Get all orders with customer details
$status_filter = $_GET['status'] ?? 'all';
$where_clause = '';
$params = [];

if ($status_filter !== 'all') {
    $where_clause = 'WHERE o.status = ?';
    $params[] = $status_filter;
}

// Ensure GROUP_CONCAT can hold long lists of items for orders with many products
$pdo->exec("SET SESSION group_concat_max_len = 10000");

$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, 
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '|||') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order counts for filter tabs
$counts = [];
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
}
$counts['all'] = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-body">
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-300">Diffindo (Cakes and Bakes)</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300 font-bold">Orders</a>
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
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Order Management</h2>
            <p class="text-gray-600">Review and manage customer orders</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <a href="?status=all" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        All Orders (<?= $counts['all'] ?? 0 ?>)
                    </a>
                    <a href="?status=pending" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        Pending (<?= $counts['pending'] ?? 0 ?>)
                    </a>
                    <a href="?status=accepted" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'accepted' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        Accepted (<?= $counts['accepted'] ?? 0 ?>)
                    </a>
                    <a href="?status=delivered" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'delivered' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        Delivered (<?= $counts['delivered'] ?? 0 ?>)
                    </a>
                    <a href="?status=rejected" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        Rejected (<?= $counts['rejected'] ?? 0 ?>)
                    </a>
                    <a href="?status=cancelled" 
                       class="py-4 px-1 border-b-2 font-medium text-sm <?= $status_filter === 'cancelled' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                        Cancelled (<?= $counts['cancelled'] ?? 0 ?>)
                    </a>
                </nav>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if (empty($orders)): ?>
                <div class="p-8 text-center">
                    <p class="text-gray-500">No orders found.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900">#<?= $order['id'] ?></p>
                                            <p class="text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($order['customer_email']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                        <?php
                                            // items are stored with a '|||' separator to avoid HTML/CSV ambiguity
                                            $items_raw = $order['items'] ?? '';
                                            // title should be a short, single-line representation
                                            $title_items = htmlspecialchars(str_replace('|||', ', ', $items_raw));
                                            // display with line breaks
                                            $display_items = nl2br(htmlspecialchars(str_replace('|||', "\n", $items_raw)));
                                        ?>
                                        <div class="truncate" title="<?= $title_items ?>">
                                            <?= $display_items ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($order['delivery_datetime'])) ?><br>
                                        <?= date('g:i A', strtotime($order['delivery_datetime'])) ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        Rs <?= number_format($order['total']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                            'delivered' => 'bg-blue-100 text-blue-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$order['status']] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <div class="flex space-x-2">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" 
                                                            class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                                                            onclick="return confirm('Accept this order?')">
                                                        Accept
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" 
                                                            class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700"
                                                            onclick="return confirm('Reject this order?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($order['status'] === 'accepted'): ?>
                                            <div class="flex space-x-2">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <input type="hidden" name="action" value="delivered">
                                                    <button type="submit" 
                                                            class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                                                            onclick="return confirm('Mark this order as delivered?')">
                                                        Mark Delivered
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">No actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>