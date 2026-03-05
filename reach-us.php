<?php
include 'includes/db.php';
include 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reach Us - Diffindo Cakes & Bakes</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .font-heading { font-family: 'Playfair Display', serif; }
    .font-body { font-family: 'Inter', sans-serif; }
    .navbar-blur { backdrop-filter: blur(10px); background: rgba(253, 242, 248, 0.9); }
  </style>
</head>
<body class="bg-pink-50 font-body">
  <!-- Premium Navigation (same as homepage) -->
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

        <div class="hidden md:flex items-center space-x-8">
          <a href="index.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Home</a>
          <?php $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>
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
            <a href="register.php" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all shadow font-medium">Sign Up</a>
          <?php else: ?>
            <a href="user/dashboard.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors"><i class="fas fa-user-circle mr-2"></i>Dashboard</a>
            <a href="logout.php" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Logout</a>
          <?php endif; ?>
        </div>

        <div class="md:hidden">
          <button id="mobile-menu-btn" class="text-gray-700 hover:text-pink-600 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-white/90 backdrop-blur-sm rounded-lg mt-2 p-4 shadow-xl border border-pink-200">
      <div class="space-y-3">
        <a href="index.php" class="block text-gray-700 hover:text-pink-600 font-medium">Home</a>
        <a href="cart/view.php" class="block text-gray-700 hover:text-pink-600 font-medium">Cart <?php if ($cart_count > 0): ?>(<?= $cart_count ?>)<?php endif; ?></a>
        <?php if (!is_logged_in()): ?>
          <a href="login.php" class="block text-gray-700 hover:text-pink-600 font-medium">Login</a>
          <a href="register.php" class="block text-gray-700 hover:text-pink-600 font-medium">Sign Up</a>
        <?php else: ?>
          <a href="user/dashboard.php" class="block text-gray-700 hover:text-pink-600 font-medium">Dashboard</a>
          <a href="logout.php" class="block text-gray-700 hover:text-pink-600 font-medium">Logout</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="max-w-5xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Reach Us</h1>
    <p class="text-gray-600 mb-6">We'd love to hear from you — here's how to find or contact our shop.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-3">Shop Location</h2>
        <p class="text-gray-700 mb-2"><strong>Diffindo Cakes & Bakes</strong></p>
        <p class="text-gray-600">12/12 St Peter's Lane, Ninthani<br>Puttalam, Northwestern Province</p>

        <div class="mt-4">
          <h3 class="text-sm font-medium text-gray-700 mb-2">Opening Hours</h3>
          <ul class="text-gray-600 text-sm space-y-1">
            <li>Mon - Fri: 8:00 AM — 9:00 PM</li>
            <li>Sat & Sun: 9:00 AM — 5:00 PM</li>
          </ul>
        </div>

        <div class="mt-6">
          <h3 class="text-sm font-medium text-gray-700 mb-2">Map</h3>
          <!-- Lightweight map iframe (replace src with your Google Maps embed if available) -->
          <div class="w-full h-48 bg-gray-100 rounded overflow-hidden">
            <!-- Embedded map centered on provided coordinates -->
            <iframe class="w-full h-full" frameborder="0" style="border:0" loading="lazy"
              src="https://maps.google.com/maps?q=8.024071989665963,79.8486118362895&z=17&output=embed"></iframe>
          </div>
          <div class="mt-3">
            <a href="https://www.google.com/maps/search/?api=1&query=8.024071989665963,79.8486118362895" target="_blank" rel="noopener noreferrer" class="inline-block px-4 py-2 bg-pink-500 text-white rounded hover:bg-pink-600">
              Open in Google Maps
            </a>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-3">Contact & Social</h2>

        <p class="text-gray-700"><strong>Phone</strong></p>
        <p class="text-gray-600 mb-3"><a href="tel:+94720592818" class="text-pink-600">+94 720592818</a></p>

        <p class="text-gray-700"><strong>Email</strong></p>
        <p class="text-gray-600 mb-3"><a href="mailto:diffindocakes@gmail.com" class="text-pink-600">diffindocakes@gmail.com</a></p>

        <p class="text-gray-700"><strong>Instagram</strong></p>
        <p class="text-gray-600 mb-4">Follow us for latest cakes and behind-the-scenes:</p>
        <a href="https://www.instagram.com/diffi_ndo/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center space-x-2 px-4 py-2 bg-pink-500 text-white rounded hover:bg-pink-600">
          <i class="fab fa-instagram"></i><span>@diffindocakes</span>
        </a>

        <div class="mt-6">
          <h3 class="text-sm font-medium text-gray-700 mb-2">Send us a message</h3>
          <p class="text-gray-600 text-sm mb-2">For custom orders, please include your preferred date, cake type and any special requests through WhatsApp:
            <a href="https://wa.me/94720592818" target="_blank" rel="noopener noreferrer" class="text-pink-600 underline">WhatsApp Us</a>
          </p>

        </div>
      </div>
    </div>

  </main>

  <footer class="bg-white/80 backdrop-blur-sm border-t border-pink-200 py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
      © <?= date('Y') ?> Diffindo Cakes & Bakes. All rights reserved.
    </div>
  </footer>

  <script>
    // Mobile menu toggle for the navbar
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
