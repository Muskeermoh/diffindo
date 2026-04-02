<?php
session_start();
include 'includes/db.php';

$error = '';
$success = '';
$show_form = true;
$email = $_SESSION['password_reset_email'] ?? '';
$otp_verified = $_SESSION['password_reset_verified'] ?? false;
$remaining_seconds = 0;
$otp_expired = false;
$redirect_seconds = 0;
$password_reset_complete = false;

if ($email) {
    $stmt = $pdo->prepare('SELECT password_reset_expires FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && !empty($row['password_reset_expires'])) {
        $expires_ts = strtotime($row['password_reset_expires']);
        $remaining_seconds = max(0, $expires_ts - time());
        if ($remaining_seconds <= 0) {
            $otp_expired = true;
        }
    } else {
        $otp_expired = true;
    }
}

if ($otp_expired && !$otp_verified) {
    $error = 'OTP has expired. Please request a new OTP.';
    $show_form = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($email)) {
        $error = 'Please request a password reset from the Forgot Password page first.';
    } elseif (!$otp_verified) {
        $otp = trim($_POST['otp'] ?? '');

        if (empty($otp)) {
            $error = 'Please enter the OTP sent to your email.';
        } elseif ($otp_expired) {
            $error = 'OTP has expired. Please request a new OTP.';
            $show_form = false;
        } else {
            $otp = trim($_POST['otp'] ?? '');
            $otp = preg_replace('/[^0-9]/', '', $otp);

            $stmt = $pdo->prepare('SELECT id, password_reset_token, password_reset_expires FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            error_log('=== OTP VERIFICATION DEBUG ===');
            error_log('Email: ' . $email);
            error_log('OTP Submitted: [' . $otp . '] Type: ' . gettype($otp) . ' Length: ' . strlen($otp));
            error_log('OTP in DB: [' . ($user ? $user['password_reset_token'] : 'USER NOT FOUND') . '] Type: ' . ($user ? gettype($user['password_reset_token']) : 'N/A') . ' Length: ' . ($user ? strlen($user['password_reset_token']) : 0));
            error_log('Expiry in DB: ' . ($user ? $user['password_reset_expires'] : 'N/A'));
            error_log('Current time: ' . date('Y-m-d H:i:s'));
            
            $verified_user = false;
            if ($user && $user['password_reset_token'] !== null) {
                $expires_time = strtotime($user['password_reset_expires']);
                $current_time = time();
                error_log('Expiry timestamp: ' . $expires_time . ' | Current timestamp: ' . $current_time . ' | Remaining: ' . ($expires_time - $current_time) . ' seconds');
                error_log('OTP Match (===): ' . ($user['password_reset_token'] === $otp ? 'YES' : 'NO'));
                error_log('OTP Match (==): ' . ($user['password_reset_token'] == $otp ? 'YES' : 'NO'));
                error_log('Not Expired: ' . ($expires_time >= $current_time ? 'YES' : 'NO'));

                if ($user['password_reset_token'] === $otp && $expires_time >= $current_time) {
                    $verified_user = true;
                }
            }

            error_log('Manual Verification Result: ' . ($verified_user ? 'FOUND' : 'NOT FOUND'));
            error_log('=== END DEBUG ===');

            if ($verified_user) {
                $_SESSION['password_reset_verified'] = true;
                $otp_verified = true;
                $success = 'OTP verified successfully. You may now choose a new password.';
            } else {
                $error = 'Invalid OTP or expired code. Please try again.';
            }
        }
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $new_password = hash('sha256', $password);
                $stmt = $pdo->prepare('UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?');
                $stmt->execute([$new_password, $user['id']]);

                unset($_SESSION['password_reset_email']);
                unset($_SESSION['password_reset_verified']);
                $success = 'Your password has been reset successfully. You can now log in with your new password.';
                $show_form = false;
                $redirect_seconds = 10;
                $password_reset_complete = true;
            } else {
                $error = 'Unable to update password for this account.';
            }
        }
    }
}

if (empty($email) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Please request a password reset first.';
    $show_form = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Diffindo (Cakes and Bakes)</title>
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
                    <i class="fas fa-key text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="font-heading text-3xl font-bold text-gray-800">Diffindo</h1>
                    <p class="text-pink-600 font-medium">Reset Password</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="font-heading text-3xl font-bold text-gray-800 mb-2">Create a new password</h2>
                <p class="text-gray-600">Use the form below to reset your password.</p>
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
                <?php if ($password_reset_complete): ?>
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-4 rounded-xl mb-6">
                        <p class="font-semibold mb-2">Redirecting to the login page in <strong id="redirect-timer"><?= $redirect_seconds ?></strong> seconds.</p>
                        <p class="text-sm text-blue-600">If you are not redirected automatically, <a href="login.php" class="text-blue-700 font-semibold">click here</a>.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <?php if (!$otp_verified): ?>
                    <form method="POST" class="space-y-6">
                        <?php if ($email): ?>
                            <div class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl p-4">
                                OTP was sent to <strong><?= htmlspecialchars($email) ?></strong>.
                            </div>
                        <?php endif; ?>

                        <?php if (!$otp_expired): ?>
                            <div class="text-sm text-gray-700 bg-pink-50 border border-pink-200 rounded-xl p-4">
                                <span>OTP expires in </span><strong id="otp-timer"><?php echo gmdate('i:s', $remaining_seconds); ?></strong>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">OTP Code</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400"></i>
                                </div>
                                <input type="text" name="otp" required pattern="\d{6}"
                                       class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                                       placeholder="Enter the 6-digit OTP">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white py-4 px-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg">
                            <i class="fas fa-check-circle mr-2"></i>
                            Verify OTP
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" class="space-y-6">
                        <div class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl p-4">
                            OTP verified for <strong><?= htmlspecialchars($email) ?></strong>. Enter a new password.
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">New Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" name="password" required minlength="6"
                                       class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                                       placeholder="Enter new password">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" name="confirm_password" required minlength="6"
                                       class="w-full pl-12 pr-4 py-4 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors bg-white/50"
                                       placeholder="Confirm new password">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white py-4 px-4 rounded-xl hover:from-pink-600 hover:to-rose-600 transition-all shadow-lg hover:shadow-xl font-semibold text-lg">
                            <i class="fas fa-unlock-alt mr-2"></i>
                            Change Password
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center mt-6">
                    <a href="forgot_password.php" class="text-pink-600 hover:text-pink-700 font-semibold transition-colors">Request a new OTP</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        (function() {
            function pad(n) { return String(n).padStart(2, '0'); }

            var timerElement = document.getElementById('otp-timer');
            if (timerElement) {
                var remaining = <?php echo (int)$remaining_seconds; ?>;
                var otpInput = document.querySelector('input[name="otp"]');
                var submitButton = document.querySelector('button[type="submit"]');

                var interval = setInterval(function() {
                    if (remaining <= 0) {
                        clearInterval(interval);
                        timerElement.textContent = '00:00';
                        if (otpInput) otpInput.disabled = true;
                        if (submitButton) submitButton.disabled = true;
                        var alert = document.createElement('div');
                        alert.className = 'mt-4 text-sm text-red-700';
                        alert.textContent = 'OTP has expired. Please request a new OTP.';
                        timerElement.parentNode.parentNode.appendChild(alert);
                        return;
                    }
                    remaining -= 1;
                    var minutes = Math.floor(remaining / 60);
                    var seconds = remaining % 60;
                    timerElement.textContent = pad(minutes) + ':' + pad(seconds);
                }, 1000);
            }

            var redirectElement = document.getElementById('redirect-timer');
            if (redirectElement) {
                var remainingRedirect = <?php echo (int)$redirect_seconds; ?>;
                var redirectInterval = setInterval(function() {
                    if (remainingRedirect <= 0) {
                        clearInterval(redirectInterval);
                        window.location.href = 'login.php';
                        return;
                    }
                    redirectElement.textContent = remainingRedirect;
                    remainingRedirect -= 1;
                }, 1000);
            }
        })();
    </script>
</body>
</html>
