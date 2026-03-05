<?php 
include 'includes/db.php';
include 'includes/auth.php';

$error = '';
$success = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/^[0-9+\-\s()]{7,32}$/', $phone)) {
        $error = 'Please enter a valid phone number (digits, spaces, +, -, parentheses allowed)';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email address already registered';
        } else {
            // Create new user
            $hashed_password = hash('sha256', $password);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $hashed_password, $phone])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .font-heading { font-family: 'Playfair Display', serif; }
        .font-body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #fbcfe8 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center font-body p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Back Link -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center text-pink-600 hover:text-pink-700 transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="font-medium">Back to Home</span>
            </a>
            <div class="flex items-center justify-center space-x-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-birthday-cake text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="font-heading text-3xl font-bold text-gray-800">Diffindo</h1>
                    <p class="text-pink-600 font-medium">Cakes & Bakes</p>
                </div>
            </div>
        </div>
        
        <div class="glass-card rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="font-heading text-3xl font-bold text-gray-800 mb-2">Join Our Family</h2>
                <p class="text-gray-600">Create your sweet account today</p>
            </div>
        
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-3">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="name" required 
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                               placeholder="Enter your full name"
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-3">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" required 
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                               placeholder="Enter your email"
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                       <input type="tel" name="phone" required maxlength="32" inputmode="tel" pattern="[0-9+\-\s()]{7,32}"
                           title="Please enter a valid phone number (digits, spaces, +, -, parentheses allowed)"
                           class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                           placeholder="Enter your phone number"
                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                            </div>
                        </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-3">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required minlength="6"
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                               placeholder="Create a password">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1">Minimum 6 characters required</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-3">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="confirm_password" required 
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                               placeholder="Confirm your password">
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white py-4 px-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>
            
            <div class="text-center mt-8">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-pink-600 hover:text-pink-700 font-semibold transition-colors">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>