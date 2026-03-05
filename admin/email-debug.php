<?php
session_start();
// Remove admin check for debugging - anyone can access this temporarily

echo "<h2>Email Configuration Debug</h2>";

// Load email config
$email_config = include '../includes/email-config.php';

echo "<h3>Configuration:</h3>";
echo "<pre>";
print_r($email_config);
echo "</pre>";

echo "<h3>Test Email Sending:</h3>";

if ($_POST && isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    
    // Include mailer
    include '../includes/mailer.php';
    
    echo "<p>Attempting to send test email to: $test_email</p>";
    
    // Try to send email
    $result = send_order_notification($test_email, 'Test User', 999, 'accepted');
    
    echo "<p>Result: " . ($result ? "SUCCESS" : "FAILED") . "</p>";
    
    echo "<h4>Check error log:</h4>";
    echo "<pre>";
    $error_log = error_get_last();
    print_r($error_log);
    echo "</pre>";
}
?>

<form method="POST">
    <input type="email" name="test_email" placeholder="Enter email to test" required>
    <button type="submit">Send Test Email</button>
</form>

<p><a href="dashboard.php">Back to Dashboard</a></p>