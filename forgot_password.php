<?php
session_start();
include 'includes/db.php';
include 'includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 150);
            $stmt = $pdo->prepare('UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?');
            $stmt->execute([$otp, $expires, $user['id']]);

            $verify_stmt = $pdo->prepare('SELECT password_reset_token, password_reset_expires FROM users WHERE id = ?');
            $verify_stmt->execute([$user['id']]);
            $verify = $verify_stmt->fetch();
            error_log('OTP Stored in DB - OTP: [' . $verify['password_reset_token'] . '], Expires: ' . $verify['password_reset_expires']);

            $subject = 'Your Diffindo password reset OTP';
            $body = '<h2>Password reset code</h2>' .
                    '<p>Hello ' . htmlspecialchars($user['name']) . ',</p>' .
                    '<p>Use the following OTP to reset your password:</p>' .
                    '<p style="font-size: 24px; font-weight: 700; letter-spacing: 0.15em;">' . htmlspecialchars($otp) . '</p>' .
                    '<p>This code expires in 2.5 minutes (150 seconds).</p>' .
                    '<p>If you did not request this, please ignore this message.</p>';

            $email_config = include 'includes/email-config.php';
            $email_sent = phpmailer_send($email, $subject, $body, $email_config);
            
            if (!$email_sent) {
                $log_file = __DIR__ . '/logs/emails.log';
                if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0777, true);
                file_put_contents($log_file, "=== OTP EMAIL (FALLBACK) ===\nTime: " . date('Y-m-d H:i:s') . "\nTo: $email\nOTP: $otp\nExpires: {$expires}\nSubject: $subject\nContent:\n" . $body . "\n\n", FILE_APPEND | LOCK_EX);
            }
            error_log('OTP generation for ' . $email . ': OTP=' . $otp . ', Sent=' . ($email_sent ? 'true' : 'false'));
        }

        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_verified'] = false;
        $_SESSION['password_reset_otp_display'] = isset($otp) ? $otp : null;
        header('Location: reset_password.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Diffindo (Cakes and Bakes)</title>
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
        <div class="text-center mb-8">
            <a href="login.php" class="inline-flex items-center text-pink-600 hover:text-pink-700 transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="font-medium">Back to Sign In</span>
            </a>
            <div class="flex items-center justify-center space-x-3 mb-2">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-lock-open text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="font-heading text-3xl font-bold text-gray-800">Diffindo</h1>
                    <p class="text-pink-600 font-medium">Password Recovery</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="font-heading text-3xl font-bold text-gray-800 mb-2">Forgot your password?</h2>
                <p class="text-gray-600">Enter your email and we'll send a one-time password (OTP) to your inbox.</p>
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
                <?php if (!empty($_SESSION['password_reset_otp_display'])): ?>
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl mb-6">
                        <p class="text-sm font-semibold mb-2 text-blue-900">Debug - Your OTP (for testing):</p>
                        <p class="text-lg font-bold tracking-widest text-blue-900"><?= htmlspecialchars($_SESSION['password_reset_otp_display']) ?></p>
                        <p class="text-xs mt-2 text-blue-600">This is displayed for development only. Remove this in production.</p>
                    </div>
                    <?php unset($_SESSION['password_reset_otp_display']); ?>
                <?php endif; ?>
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

                <button type="submit"
                        class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white py-4 px-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send OTP
                </button>
            </form>

            <div class="text-center mt-8 text-gray-600">
                <a href="login.php" class="text-pink-600 hover:text-pink-700 font-semibold transition-colors">Back to login</a>
            </div>
        </div>
    </div>
</body>
</html>
