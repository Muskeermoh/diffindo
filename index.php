<!-- Homepage logic -->
 <?php include 'includes/db.php'; ?>
<?php include 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Diffindo (Cakes and Bakes) - Premium Artisan Cakes</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .font-heading { font-family: 'Playfair Display', serif; }
    .font-body { font-family: 'Inter', sans-serif; }
    .gradient-bg { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #fbcfe8 100%); }
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-8px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .navbar-blur { backdrop-filter: blur(10px); background: rgba(253, 242, 248, 0.9); }
    
    /* Hero Background Styles */
    .hero-background {
      background-image: 
        linear-gradient(135deg, rgba(253, 242, 248, 0.6) 0%, rgba(252, 231, 243, 0.5) 50%, rgba(251, 207, 232, 0.4) 100%),
        url('assets/images/bakery-bg.png');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      position: relative;
    }
    .hero-background::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(1px);
    }
    .hero-content {
      position: relative;
      z-index: 1;
    }
    
    /* Popup Notification Styles */
    .notification-popup {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      max-width: 400px;
      transform: translateX(100%);
      opacity: 0;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .notification-popup.show {
      transform: translateX(0);
      opacity: 1;
    }
    .notification-popup.hide {
      transform: translateX(100%);
      opacity: 0;
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(100%); opacity: 0; }
    }
  </style>
</head>
<body class="gradient-bg min-h-screen font-body">
  <!-- Premium Navigation -->
  <nav class="navbar-blur sticky top-0 z-50 border-b border-pink-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center shadow-lg">
            <i class="fas fa-birthday-cake text-white text-xl"></i>
          </div>
          <div>
            <h1 class="font-heading text-2xl font-bold text-gray-800">Diffindo</h1>
            <p class="text-sm text-pink-600 font-medium">Cakes & Bakes</p>
          </div>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="index.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Home</a>
          <a href="reach-us.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Reach Us</a>
          <?php 
          // Get cart count
          $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
          ?>
          <a href="cart/view.php" class="relative text-gray-700 hover:text-pink-600 font-medium transition-colors flex items-center">
            <i class="fas fa-shopping-cart mr-2"></i>
            Cart
            <?php if ($cart_count > 0): ?>
              <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold">
                <?= $cart_count ?>
              </span>
            <?php endif; ?>
          </a>
          
          <?php if (!is_logged_in()): ?>
            <a href="login.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Login</a>
            <a href="register.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-2.5 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-medium">
              Sign Up
            </a>
          <?php else: ?>
            <a href="user/dashboard.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
              <i class="fas fa-user-circle mr-2"></i>Dashboard
            </a>
            <a href="logout.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Logout</a>
          <?php endif; ?>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button id="mobile-menu-btn" class="text-gray-700 hover:text-pink-600 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div id="mobile-menu" class="md:hidden hidden bg-white/90 backdrop-blur-sm rounded-lg mt-2 p-4 shadow-xl border border-pink-200">
        <div class="space-y-3">
          <a href="index.php" class="block text-gray-700 hover:text-pink-600 font-medium">Home</a>
          <a href="reach-us.php" class="block text-gray-700 hover:text-pink-600 font-medium">Reach Us</a>
          <a href="cart/view.php" class="block text-gray-700 hover:text-pink-600 font-medium">
            Cart <?php if ($cart_count > 0): ?>(<?= $cart_count ?>)<?php endif; ?>
          </a>
          <?php if (!is_logged_in()): ?>
            <a href="login.php" class="block text-gray-700 hover:text-pink-600 font-medium">Login</a>
            <a href="register.php" class="block text-gray-700 hover:text-pink-600 font-medium">Sign Up</a>
          <?php else: ?>
            <a href="user/dashboard.php" class="block text-gray-700 hover:text-pink-600 font-medium">Dashboard</a>
            <a href="logout.php" class="block text-gray-700 hover:text-pink-600 font-medium">Logout</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <div class="relative overflow-hidden hero-background">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="text-center mb-8 hero-content">
        <h1 class="font-heading text-5xl md:text-7xl font-bold text-gray-800 mb-6 drop-shadow-sm">
          Artisan <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">Cakes</span>
        </h1>
        <p class="text-xl text-gray-700 max-w-3xl mx-auto leading-relaxed mb-8 drop-shadow-sm">
          Handcrafted with love, baked to perfection. Experience the finest selection of premium cakes 
          made with the finest ingredients and artistic flair.
        </p>
        <?php if (!is_logged_in()): ?>
          <div class="space-x-4 hero-content">
            <a href="#products" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-8 py-4 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow-xl hover:shadow-2xl font-semibold text-lg inline-flex items-center">
              <i class="fas fa-eye mr-2"></i>Browse Collection
            </a>
            <a href="register.php" class="bg-white/90 backdrop-blur-sm text-pink-600 px-8 py-4 rounded-full hover:bg-white transition-all shadow-xl hover:shadow-2xl font-semibold text-lg border-2 border-pink-200">
              Join Today
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Popup Notification -->
      <?php if (isset($_GET['message'])): ?>
        <div id="notification" class="notification-popup bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-pink-200/50 overflow-hidden">
          <div class="p-6">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                  <i class="fas fa-check text-white text-lg"></i>
                </div>
              </div>
              <div class="flex-1">
                <h4 class="text-gray-800 font-semibold text-lg mb-1">Success!</h4>
                <p class="text-gray-600"><?= htmlspecialchars($_GET['message']) ?></p>
              </div>
              <button onclick="closeNotification()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
              </button>
            </div>
            <div class="mt-4 h-1 bg-gray-100 rounded-full overflow-hidden">
              <div id="progress-bar" class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-5000 ease-linear w-full"></div>
            </div>
          </div>
        </div>

        <script>
          // Show notification with animation
          setTimeout(() => {
            document.getElementById('notification').classList.add('show');
          }, 100);

          // Auto hide after 5 seconds
          setTimeout(() => {
            closeNotification();
          }, 5000);

          // Progress bar animation
          setTimeout(() => {
            document.getElementById('progress-bar').style.width = '0%';
          }, 200);

          function closeNotification() {
            const notification = document.getElementById('notification');
            notification.classList.remove('show');
            notification.classList.add('hide');
            
            // Remove from URL after animation
            setTimeout(() => {
              const url = new URL(window.location);
              url.searchParams.delete('message');
              window.history.replaceState({}, document.title, url.pathname);
            }, 500);
          }
        </script>
      <?php endif; ?>
    </div>
  </div>

  <!-- Products Section -->
  <section id="products" class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="font-heading text-4xl md:text-5xl font-bold text-gray-800 mb-4">
          Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">Collection</span>
        </h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Each cake is a masterpiece, carefully crafted with premium ingredients and artistic attention to detail.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
          $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
          while ($row = $stmt->fetch()):
        ?>
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden card-hover border border-pink-100">
          <div class="relative overflow-hidden">
            <img src="assets/images/<?= $row['image'] ?>" 
                 class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRkNFN0YzIi8+CjxwYXRoIGQ9Ik0xNTAgMTAwQzEzNy45IDEwMCAxMjggMTA5LjkgMTI4IDEyMkMxMjggMTM0LjEgMTM3LjkgMTQ0IDE1MCAxNDRDMTYyLjEgMTQ0IDE3MiAxMzQuMSAxNzIgMTIyQzE3MiAxMDkuOSAxNjIuMSAxMDAgMTUwIDEwMFoiIGZpbGw9IiNGOTI2NzIiLz4KPHN2ZyBjbGFzcz0iZmEtYmlydGhkYXktY2FrZSB3LTggaC04IiB2aWV3Qm94PSIwIDAgNTEyIDUxMiI+PHBhdGggZD0iTTUzIDM2OGMxOC4xIDAgMzMtMTQuOSAzMy0zMyAwLTE4LjEtMTQuOS0zMy0zMy0zMy0xOC4xIDAtMzMgMTQuOS0zMyAzMyAwIDE4LjEgMTQuOSAzMyAzMyAzM3ptOC0xMzEuM2MwIDIuMi0xLjggNC00IDRzLTQtMS44LTQtNC44YzAtMi4yIDEuOC00IDQtNHM0IDEuOCA0IDQuOHoiLz48L3N2Zz4KPC9zdmc+'">
            <div class="absolute top-4 right-4">
              <div class="bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 shadow-lg">
                <span class="text-pink-600 font-bold text-lg">Rs <?= number_format($row['price']) ?></span>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <h3 class="font-heading text-2xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($row['name']) ?></h3>
            <p class="text-gray-600 mb-6 leading-relaxed"><?= htmlspecialchars($row['description']) ?></p>
            
            <form method="POST" action="cart/add.php" class="space-y-4">
              <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="redirect" value="index.php">
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <label class="text-gray-700 font-medium">Qty:</label>
                  <div class="relative">
                    <input type="number" name="quantity" min="1" value="1" 
                           class="w-20 px-3 py-2 border-2 border-pink-200 rounded-lg focus:border-pink-500 focus:outline-none text-center font-semibold">
                  </div>
                </div>
                
                <button type="submit" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-3 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold flex items-center space-x-2">
                  <i class="fas fa-cart-plus"></i>
                  <span>Add to Cart</span>
                </button>
              </div>
            </form>
          </div>
        </div>
        <?php endwhile; ?>
      </div>

      <?php if ($pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() == 0): ?>
        <div class="text-center py-20">
          <div class="w-24 h-24 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-birthday-cake text-pink-500 text-3xl"></i>
          </div>
          <h3 class="font-heading text-2xl font-bold text-gray-800 mb-4">Coming Soon!</h3>
          <p class="text-gray-600 text-lg">Our delicious cake collection is being prepared. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white/80 backdrop-blur-sm border-t border-pink-200 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <div class="flex items-center justify-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center">
            <i class="fas fa-birthday-cake text-white"></i>
          </div>
          <h3 class="font-heading text-2xl font-bold text-gray-800">Diffindo Cakes & Bakes</h3>
        </div>
        <p class="text-gray-600 mb-6">Crafting sweet memories, one cake at a time.</p>
        <p class="text-sm text-gray-500">© 2026 Diffindo Cakes & Bakes. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('hidden');
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  </script>
</body>
</html>
