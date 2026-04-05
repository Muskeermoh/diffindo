<?php
include '../includes/db.php';
include '../includes/auth.php';

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

require_login();

$order_id = $_GET['order_id'] ?? 0;

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user']['id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: ../user/dashboard.php");
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation - Diffindo (Cakes and Bakes)</title>
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
                    <a href="../user/dashboard.php" class="text-pink-600 hover:text-pink-800">Dashboard</a>
                    <a href="../user/orders.php" class="text-pink-600 hover:text-pink-800">My Orders</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Success Message -->
        <div class="bg-green-100 border border-green-400 rounded-lg p-6 mb-8">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h2 class="text-2xl font-bold text-green-800">Order Placed Successfully!</h2>
                    <p class="text-green-700">Thank you for your order. We'll prepare your delicious cakes with care.</p>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-xl font-semibold text-gray-800">Order Details</h3>
            </div>
            
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Order Information</h4>
                        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                        <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Pending</span>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Delivery Information</h4>
                        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                        <p><strong>Delivery:</strong> <?= date('M j, Y g:i A', strtotime($order['delivery_datetime'])) ?></p>
                    </div>
                </div>

                <!-- Order Items -->
                <h4 class="font-semibold text-gray-700 mb-4">Items Ordered</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center space-x-3">
                                            <img src="../assets/images/<?= htmlspecialchars($item['product_image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                 class="w-10 h-10 object-cover rounded"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAxOEMxOC4zNDMxIDE4IDE3IDE5LjM0MzEgMTcgMjFDMTcgMjIuNjU2OSAxOC4zNDMxIDI0IDIwIDI0QzIxLjY1NjkgMjQgMjMgMjIuNjU2OSAyMyAyMUMyMyAxOS4zNDMxIDIxLjY1NjkgMTggMjAgMThaIiBmaWxsPSIjOUIxMDhEIi8+Cjwvc3ZnPgo='">
                                            <span class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">Rs <?= number_format($item['price']) ?></td>
                                    <td class="px-4 py-2 text-gray-600"><?= $item['quantity'] ?></td>
                                    <td class="px-4 py-2 font-medium text-gray-800">Rs <?= number_format($item['price'] * $item['quantity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-bold text-gray-800 text-right">Total:</td>
                                <td class="px-4 py-2 font-bold text-gray-800">Rs <?= number_format($order['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">What happens next?</h3>
            <ul class="list-disc list-inside text-blue-700 space-y-2">
                <li>Our team will review your order and confirm availability</li>
                <li>You'll receive an email notification once your order is accepted or if any changes are needed</li>
                <li>We'll start preparing your fresh cakes for the scheduled delivery time</li>
                <li>You can track your order status in your dashboard</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="../index.php" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 text-center">
                Order More Cakes
            </a>
            <a href="../user/orders.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 text-center">
                View All Orders
            </a>
            <a href="../user/dashboard.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 text-center">
                Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>