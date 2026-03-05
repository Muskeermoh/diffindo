<?php 
include '../includes/db.php';
include '../includes/auth.php';

// Redirect if already logged in as admin
if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

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
        
        if ($user && hash('sha256', $password) === $user['password'] && $user['email'] === 'admin@diffindo.com') {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid admin credentials';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Diffindo (Cakes and Bakes)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Admin Panel</h1>
            <p class="text-gray-600">Diffindo (Cakes and Bakes)</p>
        </div>
        
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Admin Login</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Admin Email</label>
                <input type="email" name="email" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <button type="submit" 
                    class="w-full bg-gray-800 text-white py-2 px-4 rounded-lg hover:bg-gray-900 transition duration-200">
                Login to Admin Panel
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-600">
                <a href="../index.php" class="text-blue-600 hover:underline">Back to Main Site</a>
            </p>
        </div>
        
        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
            <h4 class="font-bold text-sm text-gray-700 mb-2">Admin Access:</h4>
            <p class="text-xs text-gray-600">Email: admin@diffindo.com</p>
            <p class="text-xs text-gray-600">Password: admin123</p>
        </div>
    </div>
</body>
</html>