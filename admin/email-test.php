<?php
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

if ($_POST && isset($_POST['send_test'])) {
    $test_email = trim($_POST['test_email']);
    
    if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        include '../includes/mailer.php';
        
        $result = send_order_notification(
            $test_email,
            'Test Customer',
            '999',
            'accepted'
        );
        
        if ($result) {
            $message = "Test email sent successfully to $test_email!";
        } else {
            $error = "Failed to send test email. Check your SMTP configuration.";
        }
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Email Test - Diffindo Cakes & Bakes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .font-heading { font-family: 'Playfair Display', serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 font-body">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow mb-8 p-6">
                <div class="flex items-center justify-between">
                    <h1 class="font-heading text-3xl font-bold text-gray-800">
                        <i class="fas fa-envelope-open text-blue-500 mr-3"></i>
                        Email Configuration & Test
                    </h1>
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Email Configuration -->
            <div class="bg-white rounded-lg shadow mb-8 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-cog text-gray-500 mr-2"></i>
                    SMTP Configuration
                </h2>
                
                <?php 
                $email_config = include '../includes/email-config.php';
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $email_config['enable_sending'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= $email_config['enable_sending'] ? 'Real Email Sending Enabled' : 'Development Mode (Logging Only)' ?>
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Server</label>
                        <p class="text-gray-600"><?= htmlspecialchars($email_config['smtp']['host']) ?>:<?= $email_config['smtp']['port'] ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                        <p class="text-gray-600"><?= htmlspecialchars($email_config['from_email']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <p class="text-gray-600"><?= htmlspecialchars($email_config['smtp']['username']) ?></p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-2">Setup Instructions:</h3>
                    <ol class="text-blue-700 text-sm space-y-1">
                        <li>1. Edit <code>includes/email-config.php</code></li>
                        <li>2. Replace 'your-email@gmail.com' with your Gmail address</li>
                        <li>3. Replace 'your-app-password' with your Gmail App Password</li>
                        <li>4. Set 'enable_sending' to true to send real emails</li>
                    </ol>
                    <p class="text-blue-600 text-sm mt-2">
                        <strong>Note:</strong> For Gmail, you need to use an App Password, not your regular password.
                        <a href="https://support.google.com/accounts/answer/185833" target="_blank" class="underline">
                            Learn how to create Gmail App Password →
                        </a>
                    </p>
                </div>
            </div>

            <!-- Test Email -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-paper-plane text-blue-500 mr-2"></i>
                    Send Test Email
                </h2>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="test_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Test Email Address
                        </label>
                        <input type="email" 
                               id="test_email" 
                               name="test_email" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter email address to test">
                    </div>
                    
                    <button type="submit" 
                            name="send_test"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Test Email
                    </button>
                </form>

                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm">
                        <strong>Test Email Content:</strong> This will send a sample "Order Confirmed" email 
                        to verify your SMTP configuration is working properly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>