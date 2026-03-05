<?php 
include 'includes/db.php';
include 'includes/auth.php';

$error = '';

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && hash('sha256', $password) === $user['password']) {
            $_SESSION['user'] = $user;
            
            // Redirect based on user type
            if ($user['email'] === 'admin@diffindo.com') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Diffindo (Cakes and Bakes)</title>
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
                <h2 class="font-heading text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-gray-600">Sign in to your sweet account</p>
            </div>
        
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
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
                    <label class="block text-gray-700 text-sm font-semibold mb-3">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required 
                               class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                               placeholder="Enter your password">
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white py-4 px-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
            
            <div class="text-center mt-8 space-y-4">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-pink-600 hover:text-pink-700 font-semibold transition-colors">Create Account</a>
                </p>
            </div>
            
            <!-- <div class="mt-8 p-6 bg-gradient-to-r from-pink-50 to-rose-50 rounded-2xl border border-pink-200">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-pink-500"></i>
                    Demo Account
                </h4>
                <div class="text-sm space-y-1">
                    <p class="text-gray-600"><strong>Admin:</strong> admin@diffindo.com</p>
                    <p class="text-gray-600"><strong>Password:</strong> admin123</p>
                </div>
            </div> -->
        </div>
    </div>
</body>
</html>