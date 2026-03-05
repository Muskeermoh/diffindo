<?php
// Simple email test - no authentication required
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email Debug Test</h2>";

// Load email config
$email_config = include 'includes/email-config.php';

echo "<h3>Email Configuration Status:</h3>";
echo "<p><strong>Enable Sending:</strong> " . ($email_config['enable_sending'] ? 'TRUE (will send real emails)' : 'FALSE (logging only)') . "</p>";
echo "<p><strong>SMTP Host:</strong> " . $email_config['smtp']['host'] . "</p>";
echo "<p><strong>SMTP Port:</strong> " . $email_config['smtp']['port'] . "</p>";
echo "<p><strong>Username:</strong> " . $email_config['smtp']['username'] . "</p>";
echo "<p><strong>From Email:</strong> " . $email_config['from_email'] . "</p>";

if ($_POST && isset($_POST['test_email'])) {
    echo "<hr><h3>Test Results:</h3>";
    
    $test_email = $_POST['test_email'];
    echo "<p>Testing email to: <strong>$test_email</strong></p>";
    
    // Include mailer
    include 'includes/mailer.php';
    
    echo "<p>Calling send_order_notification...</p>";
    
    // Try to send email
    ob_start();
    $result = send_order_notification($test_email, 'Test Customer', 999, 'accepted');
    $output = ob_get_clean();
    
    echo "<p><strong>Function Result:</strong> " . ($result ? "SUCCESS ✅" : "FAILED ❌") . "</p>";
    
    if ($output) {
        echo "<p><strong>Output:</strong> <pre>$output</pre></p>";
    }
    
    // Check error log
    $last_error = error_get_last();
    if ($last_error) {
        echo "<p><strong>Last Error:</strong> " . $last_error['message'] . "</p>";
    }
    
    // Check if email was logged
    $log_file = 'logs/emails.log';
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        $recent_logs = substr($log_content, -1000); // Last 1000 characters
        echo "<h4>Recent Email Log:</h4>";
        echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow:auto'>$recent_logs</pre>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
input, button { padding: 10px; margin: 5px; }
button { background: #007cba; color: white; border: none; cursor: pointer; }
</style>

<form method="POST">
    <h3>Send Test Email:</h3>
    <input type="email" name="test_email" placeholder="Enter email address to test" required style="width:300px">
    <br>
    <button type="submit">Send Test Email</button>
</form>

<p><a href="index.php">← Back to Home</a> | <a href="admin/dashboard.php">Admin Dashboard</a></p>