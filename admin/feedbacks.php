<?php
include '../includes/db.php';
include '../includes/auth.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

// Fetch feedbacks
$stmt = $pdo->query("SELECT f.*, o.user_id as order_user_id, o.delivery_datetime, u.name as customer_name, u.email as customer_email FROM feedbacks f JOIN orders o ON f.order_id = o.id LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedbacks - Admin - Diffindo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<nav class="bg-gray-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
            </div>
            <div class="flex space-x-4">
                <a href="dashboard.php" class="text-white hover:text-gray-300 font-bold">Dashboard</a>
                <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                <a href="feedbacks.php" class="text-white hover:text-gray-300">Feedbacks</a>
                <a href="../index.php" class="text-white hover:text-gray-300">View Site</a>
                <a href="../logout.php" class="text-white hover:text-gray-300">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto py-8 px-4">
    <h2 class="text-2xl font-bold mb-4">Customer Feedbacks</h2>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <?php if (empty($feedbacks)): ?>
            <div class="p-8 text-center text-gray-500">No feedbacks yet.</div>
        <?php else: ?>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($feedbacks as $f): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">#<?= $f['id'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><a href="orders.php?view=<?= $f['order_id'] ?>" class="text-blue-600">#<?= $f['order_id'] ?></a></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($f['customer_name'] ?? $f['customer_email']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $f['rating'] ? intval($f['rating']) . ' / 5' : '-' ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= nl2br(htmlspecialchars($f['comments'])) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($f['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
