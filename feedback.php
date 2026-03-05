<?php
include 'includes/db.php';
session_start();
$error = '';
$success = '';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);

if (!$order_id) {
    $error = 'Invalid order specified.';
} else {
    // Verify order exists and is delivered
    $stmt = $pdo->prepare("SELECT o.*, u.email as customer_email, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {
        $error = 'Order not found.';
    } elseif ($order['status'] !== 'delivered') {
        $error = 'Feedback can only be submitted for delivered orders.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    $comments = trim($_POST['comments'] ?? '');

    if ($rating === null || $rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5.';
    } else {
        // Prevent duplicate feedback for same order
        $stmt = $pdo->prepare("SELECT id FROM feedbacks WHERE order_id = ?");
        $stmt->execute([$order_id]);
        if ($stmt->fetch()) {
            $error = 'Feedback for this order has already been submitted. Thank you.';
        } else {
            $user_id = $order['user_id'] ?? null;
            $stmt = $pdo->prepare("INSERT INTO feedbacks (order_id, user_id, rating, comments) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$order_id, $user_id, $rating, $comments])) {
                $success = 'Thank you! Your feedback has been recorded.';
            } else {
                $error = 'Failed to save feedback. Please try again.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Feedback - Diffindo Cakes & Bakes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Make range thumb bigger for touch devices and ensure good hit area */
        input[type=range] { -webkit-appearance: none; appearance: none; width: 100%; height: 36px; }
        input[type=range]::-webkit-slider-runnable-track { height: 6px; background: #ffd6e8; border-radius: 10px; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 22px; height: 22px; margin-top: -8px; background: #ec4899; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        input[type=range]::-moz-range-track { height: 6px; background: #ffd6e8; border-radius: 10px; }
        input[type=range]::-moz-range-thumb { width: 22px; height: 22px; background: #ec4899; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        /* Star preview line-height and responsive sizing */
        #star-preview { line-height: 1; }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-50 to-white min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-6 sm:p-8 mx-4 sm:mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Feedback</h1>
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (empty($success) && empty($error)): ?>
            <p class="text-gray-600 mb-4">Please rate your experience with order #<?= intval($order_id) ?>.</p>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="order_id" value="<?= intval($order_id) ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="mb-3">
                        <div id="star-preview" class="text-2xl sm:text-3xl md:text-4xl text-yellow-500 mb-2 text-center" aria-hidden="true">★★★★★</div>
                        <input id="rating-slider" type="range" name="rating" min="1" max="5" value="5" step="1" class="w-full" aria-label="Rating slider">
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>Poor</span>
                            <span>Excellent</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comments (optional)</label>
                    <textarea name="comments" rows="4" class="w-full p-3 border rounded-lg" placeholder="Tell us what you liked or what we can improve..."></textarea>
                </div>
                <button type="submit" class="bg-pink-600 text-white px-4 py-3 rounded-lg">Submit Feedback</button>
            </form>
        <?php endif; ?>

        <div class="mt-6 text-sm text-gray-500">
            <a href="index.php" class="text-pink-600">Back to Home</a>
        </div>
    </div>
        <script>
            // Live star preview for rating slider
            (function(){
                var slider = document.getElementById('rating-slider');
                var preview = document.getElementById('star-preview');
                if (!slider || !preview) return;
                function renderStars(val) {
                    var full = '★'.repeat(val);
                    var empty = '☆'.repeat(5 - val);
                    preview.textContent = full + empty;
                }
                // initialize
                renderStars(parseInt(slider.value, 10) || 5);
                slider.addEventListener('input', function(e){
                    renderStars(parseInt(e.target.value, 10));
                });
            })();
        </script>
</body>
</html>
