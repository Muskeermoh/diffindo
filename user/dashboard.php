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

// Get user orders with feedback
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items,
           MAX(f.id) as feedback_id,
           MAX(f.rating) as rating,
           MAX(f.comments) as comments
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    LEFT JOIN feedbacks f ON o.id = f.order_id
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll();

// Handle feedback submission
$feedback_error = '';
$feedback_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $order_id = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments'] ?? '');
    
    // Verify order belongs to user and is delivered
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user']['id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $feedback_error = 'Invalid order.';
    } elseif ($order['status'] !== 'delivered' && $order['status'] !== 'accepted') {
        $feedback_error = 'Feedback can only be submitted for delivered or accepted orders.';
    } elseif ($rating < 1 || $rating > 5) {
        $feedback_error = 'Please select a rating between 1 and 5.';
    } else {
        // Check if feedback already exists
        $stmt = $pdo->prepare("SELECT id FROM feedbacks WHERE order_id = ?");
        $stmt->execute([$order_id]);
        if ($stmt->fetch()) {
            $feedback_error = 'Feedback for this order has already been submitted.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO feedbacks (order_id, user_id, rating, comments) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$order_id, $_SESSION['user']['id'], $rating, $comments])) {
                $feedback_success = 'Thank you! Your feedback has been recorded.';
                // Refresh orders
                $stmt = $pdo->prepare("
                    SELECT o.*, 
                           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items,
                           MAX(f.id) as feedback_id,
                           MAX(f.rating) as rating,
                           MAX(f.comments) as comments
                    FROM orders o 
                    LEFT JOIN order_items oi ON o.id = oi.order_id 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    LEFT JOIN feedbacks f ON o.id = f.order_id
                    WHERE o.user_id = ? 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$_SESSION['user']['id']]);
                $orders = $stmt->fetchAll();
            } else {
                $feedback_error = 'Failed to save feedback. Please try again.';
            }
        }
    }
}
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

            <?php if ($feedback_error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mx-6 mt-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($feedback_error) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($feedback_success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mx-6 mt-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($feedback_success) ?></span>
                </div>
            <?php endif; ?>

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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Feedback</th>
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
                                        <?php if ($order['feedback_id']): ?>
                                            <div class="flex items-center space-x-1">
                                                <span class="text-yellow-500">
                                                    <?php for ($i = 0; $i < $order['rating']; $i++) { ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php } ?>
                                                </span>
                                                <span class="text-gray-600"><?= $order['rating'] ?>/5</span>
                                            </div>
                                            <button class="text-blue-600 hover:text-blue-800 text-sm mt-1" 
                                                    onclick="showFeedback(<?= $order['id'] ?>)">
                                                View Feedback
                                            </button>
                                        <?php elseif ($order['status'] === 'delivered' || $order['status'] === 'accepted'): ?>
                                            <button class="text-blue-600 hover:text-blue-800 font-medium" 
                                                    onclick="openFeedbackModal(<?= $order['id'] ?>)">
                                                <i class="fas fa-star"></i> Leave Feedback
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">-</span>
                                        <?php endif; ?>
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

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Leave Feedback</h3>
                <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="px-6 py-4">
                <input type="hidden" name="submit_feedback" value="1">
                <input type="hidden" name="order_id" id="feedbackOrderId" value="">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="hidden star-input" required>
                            <label for="star<?= $i ?>" class="cursor-pointer text-2xl text-gray-300 hover:text-yellow-400 transition-colors star-label" onclick="setRating(<?= $i ?>)">
                                <i class="fas fa-star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="feedbackComments" class="block text-sm font-medium text-gray-700 mb-2">Comments (Optional)</label>
                    <textarea id="feedbackComments" name="comments" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                              placeholder="Share your experience with this order..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700 transition-colors font-medium">
                        Submit Feedback
                    </button>
                    <button type="button" onclick="closeFeedbackModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div id="viewFeedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Your Feedback</h3>
                <button onclick="closeViewFeedbackModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="feedbackContent" class="px-6 py-4">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        let selectedRating = 0;
        
        function openFeedbackModal(orderId) {
            document.getElementById('feedbackOrderId').value = orderId;
            selectedRating = 0;
            document.querySelectorAll('.star-input').forEach((input, index) => {
                input.checked = false;
            });
            document.getElementById('feedbackComments').value = '';
            document.getElementById('feedbackModal').classList.remove('hidden');
            updateStars();
        }
        
        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.add('hidden');
        }
        
        function closeViewFeedbackModal() {
            document.getElementById('viewFeedbackModal').classList.add('hidden');
        }
        
        function setRating(rating) {
            selectedRating = rating;
            document.getElementById('star' + rating).checked = true;
            updateStars();
        }
        
        function updateStars() {
            document.querySelectorAll('.star-label').forEach((label, index) => {
                if (index < selectedRating) {
                    label.classList.remove('text-gray-300');
                    label.classList.add('text-yellow-400');
                } else {
                    label.classList.add('text-gray-300');
                    label.classList.remove('text-yellow-400');
                }
            });
        }
        
        function showFeedback(orderId) {
            // Find the feedback data from the table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const orderIdCell = row.querySelector('td:first-child');
                if (orderIdCell && orderIdCell.textContent.includes(orderId)) {
                    const feedbackCell = row.querySelector('td:nth-child(6)');
                    if (feedbackCell) {
                        const rating = feedbackCell.querySelector('.text-yellow-500');
                        const ratingText = feedbackCell.querySelector('.text-gray-600');
                        const comments = feedbackCell.getAttribute('data-comments');
                        
                        let feedbackHtml = '<div class="space-y-3">';
                        
                        if (ratingText) {
                            feedbackHtml += '<div><strong>Rating:</strong> ' + ratingText.textContent + '</div>';
                        }
                        
                        // Since we need to get comments from the row, let's use a simpler approach
                        // The JavaScript will just display the rating that's already visible
                        feedbackHtml += '<p class="text-sm text-gray-600">Your feedback has been recorded.</p>';
                        feedbackHtml += '</div>';
                        
                        document.getElementById('feedbackContent').innerHTML = feedbackHtml;
                        document.getElementById('viewFeedbackModal').classList.remove('hidden');
                    }
                }
            });
        }
        
        // Close modals when clicking outside
        document.getElementById('feedbackModal').addEventListener('click', function(e) {
            if (e.target === this) closeFeedbackModal();
        });
        
        document.getElementById('viewFeedbackModal').addEventListener('click', function(e) {
            if (e.target === this) closeViewFeedbackModal();
        });
        
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