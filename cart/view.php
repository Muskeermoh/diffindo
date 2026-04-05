<?php
include '../includes/db.php';
include '../includes/auth.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle quantity updates
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $product_id = (int) $_POST['product_id'];
    $new_quantity = (int) $_POST['quantity'];
    
    if ($new_quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
        $message = "Cart updated successfully.";
    } elseif ($new_quantity <= 0 && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $message = "Item removed from cart.";
    }
}

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

$message = $_GET['message'] ?? ($_POST['message'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .font-heading { font-family: 'Playfair Display', serif; }
        .font-body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #fbcfe8 100%); }
        .navbar-blur { backdrop-filter: blur(10px); background: rgba(253, 242, 248, 0.9); }
        .cart-item { transition: all 0.3s ease; }
        .cart-item:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-50 to-white font-body">
    <nav class="navbar-blur sticky top-0 z-50 border-b border-pink-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                       Home
                    </a>
                    <a href="../reach-us.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Reach Us</a>
                    <?php if (is_logged_in()): ?>
                        <a href="../dashboard.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                            <i class="fas fa-user-circle mr-2"></i>Dashboard
                        </a>
                        <a href="../logout.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Login</a>
                        <a href="../register.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-2.5 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg font-medium">
                            Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-12">
            <div class="flex items-center mb-4">
                <i class="fas fa-shopping-cart text-pink-500 text-3xl mr-4"></i>
                <div>
                    <h2 class="font-heading text-4xl font-bold text-gray-800">Shopping Cart</h2>
                    <p class="text-gray-600 text-lg">Review your selected premium cakes</p>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-white/80 backdrop-blur-sm border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-8 shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl p-12 text-center border border-pink-200">
                <div class="mb-8">
                    <div class="w-24 h-24 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shopping-cart text-pink-500 text-4xl"></i>
                    </div>
                    <h3 class="font-heading text-3xl font-bold text-gray-800 mb-4">Your Cart is Empty</h3>
                    <p class="text-gray-600 text-lg mb-8">Discover our exquisite collection of artisan cakes!</p>
                    <a href="../index.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-8 py-4 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg inline-flex items-center">
                        <i class="fas fa-birthday-cake mr-3"></i>
                        Browse Our Collection
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="space-y-8">
                <!-- Cart Items -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden border border-pink-200">
                    <div class="px-8 py-6 border-b border-pink-200 bg-gradient-to-r from-pink-50 to-rose-50">
                        <div class="flex items-center justify-between">
                            <h3 class="font-heading text-2xl font-bold text-gray-800">
                                Cart Items (<?= count($_SESSION['cart']) ?>)
                            </h3>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total Items</p>
                                <p class="font-bold text-pink-600"><?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-pink-100">
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="cart-item p-8">
                                <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-6">
                                    <div class="flex-shrink-0">
                                        <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="w-24 h-24 object-cover rounded-2xl shadow-lg"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTYiIGhlaWdodD0iOTYiIHZpZXdCb3g9IjAgMCA5NiA5NiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9Ijk2IiBoZWlnaHQ9Ijk2IiBmaWxsPSIjRkNFN0YzIiByeD0iMTYiLz4KPHN2ZyBjbGFzcz0iZmEtYmlydGhkYXktY2FrZSB3LTEyIGgtMTIiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNNDggMTY4YzE4LjEgMCAzMy0xNC45IDMzLTMzIDAtMTguMS0xNC45LTMzLTMzLTMzLTE4LjEgMC0zMyAxNC45LTMzIDMzIDAgMTguMSAxNC45IDMzIDMzIDMzem04LTEzMS4zYzAgMi4yLTEuOCA0LTQgNHMtNC0xLjgtNC00LjhjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC44eiIgZmlsbD0iI0Y5MjY3MiIvPjwvc3ZnPgo='">
                                    </div>
                                    
                                    <div class="flex-1 space-y-2">
                                        <h4 class="font-heading text-xl font-bold text-gray-800"><?= htmlspecialchars($item['name']) ?></h4>
                                        <p class="text-pink-600 font-bold text-lg">Rs <?= number_format($item['price']) ?> each</p>
                                    </div>
                                    
                                    <div class="flex items-center justify-between md:justify-end space-x-6">
                                        <form method="POST" class="flex items-center space-x-4">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                            <div class="flex items-center space-x-2">
                                                <label class="text-gray-700 font-medium">Qty:</label>
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                                       min="0" max="10" 
                                                       class="w-20 px-3 py-2 border-2 border-pink-200 rounded-lg text-center font-semibold focus:border-pink-500 focus:outline-none">
                                            </div>
                                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors font-medium">
                                                <i class="fas fa-sync-alt mr-1"></i>Update
                                            </button>
                                        </form>
                                        
                                        <div class="text-right space-y-2">
                                            <p class="font-heading text-xl font-bold text-gray-800">
                                                Rs <?= number_format($item['price'] * $item['quantity']) ?>
                                            </p>
                                            <a href="remove.php?id=<?= $product_id ?>" 
                                               class="text-red-500 hover:text-red-700 text-sm font-medium inline-flex items-center"
                                               onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash mr-1"></i>Remove
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-pink-200">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
                        <div class="text-center md:text-left">
                            <p class="text-gray-600 text-lg">Cart Total</p>
                            <p class="font-heading text-4xl font-bold text-gray-800">Rs <?= number_format($cart_total) ?></p>
                            <p class="text-gray-500 text-sm mt-1"><?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?> items in cart</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="../index.php" class="bg-gray-100 text-gray-700 px-8 py-4 rounded-xl hover:bg-gray-200 transition-all shadow-lg font-semibold text-center inline-flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                            </a>
                            <?php if (is_logged_in()): ?>
                                <a href="../order/checkout.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-8 py-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-center inline-flex items-center justify-center">
                                    <i class="fas fa-credit-card mr-2"></i>Proceed to Checkout
                                </a>
                            <?php else: ?>
                                <a href="../login.php?redirect=cart/view.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-8 py-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-center inline-flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Checkout
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>