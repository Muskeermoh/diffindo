<?php
// SMTP Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>SMTP Connection Test</h2>";

// Test both TLS and SSL
$smtp_host = 'smtp.gmail.com';
$smtp_port_tls = 587;
$smtp_port_ssl = 465;
$username = 'Diffindocakes@gmail.com';
$password = 'jqbpzwighkzvgldd';

echo "<h3>Testing SMTP Connection...</h3>";

// Test 1: Basic socket connections
echo "<p><strong>Test 1a:</strong> TLS Connection (Port 587)</p>";
$socket = fsockopen($smtp_host, $smtp_port_tls, $errno, $errstr, 10);
if ($socket) {
    echo "✅ Connected to $smtp_host:$smtp_port_tls<br>";
    $response = fgets($socket, 515);
    echo "Server response: " . htmlspecialchars($response) . "<br>";
    fclose($socket);
} else {
    echo "❌ Failed to connect: $errstr ($errno)<br>";
}

echo "<p><strong>Test 1b:</strong> SSL Connection (Port 465)</p>";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);
$socket = stream_socket_client("ssl://$smtp_host:$smtp_port_ssl", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
if ($socket) {
    echo "✅ SSL Connected to $smtp_host:$smtp_port_ssl<br>";
    $response = fgets($socket, 515);
    echo "Server response: " . htmlspecialchars($response) . "<br>";
    fclose($socket);
} else {
    echo "❌ SSL connection failed: $errstr ($errno)<br>";
}

echo "<hr>";

// Test 2: Try simple PHP mail function
echo "<p><strong>Test 2:</strong> PHP mail() function</p>";
if (function_exists('mail')) {
    echo "✅ PHP mail() function is available<br>";
    
    if ($_POST && isset($_POST['test_simple'])) {
        $to = $_POST['test_email'];
        $subject = "Test Email from Diffindo Cakes";
        $message = "This is a test email from your cake ordering system.";
        $headers = "From: diffindocakes@gmail.com\r\n";
        $headers .= "Content-type: text/html\r\n";
        
        $result = mail($to, $subject, $message, $headers);
        echo "<p>Simple mail result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
    }
} else {
    echo "❌ PHP mail() function not available<br>";
}

echo "<hr>";

// Test 3: Manual SMTP test
echo "<p><strong>Test 3:</strong> Manual SMTP Authentication</p>";

if ($_POST && isset($_POST['test_smtp'])) {
    $test_email = $_POST['test_email'];
    
    echo "Attempting manual SMTP connection...<br>";
    
    // Connect to the TLS port for manual STARTTLS flow
    $socket = fsockopen($smtp_host, $smtp_port_tls, $errno, $errstr, 30);
    if (!$socket) {
        echo "❌ Connection failed: $errstr ($errno)<br>";
    } else {
        echo "✅ Socket created<br>";
        
        // Read greeting
        $response = fgets($socket, 515);
        echo "Greeting: " . htmlspecialchars($response) . "<br>";
        
        // EHLO
        fputs($socket, "EHLO $smtp_host\r\n");
        // Read full multi-line EHLO response
        $ehlo_all = '';
        do {
            $response = fgets($socket, 515);
            $ehlo_all .= $response;
            echo "EHLO response: " . htmlspecialchars($response) . "<br>";
        } while (substr($response, 3, 1) == '-');
        
        // STARTTLS
        fputs($socket, "STARTTLS\r\n");
        
        // Read all STARTTLS response lines
        $all_responses = [];
        do {
            $response = fgets($socket, 515);
            $all_responses[] = $response;
            echo "STARTTLS response: " . htmlspecialchars($response) . "<br>";
        } while (substr($response, 3, 1) == '-');
        
        if (substr($response, 0, 3) == '220') {
            echo "✅ TLS started successfully<br>";
            
            // Enable TLS
            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                echo "✅ TLS encryption enabled<br>";
                
                // EHLO again (read multi-line response)
                fputs($socket, "EHLO $smtp_host\r\n");
                do {
                    $response = fgets($socket, 515);
                    echo "EHLO after TLS: " . htmlspecialchars($response) . "<br>";
                } while ($response !== false && preg_match('/^\d{3}-/', $response));
                
                // AUTH LOGIN
                fputs($socket, "AUTH LOGIN\r\n");
                $response = fgets($socket, 515);
                echo "AUTH LOGIN: " . htmlspecialchars($response) . "<br>";
                
                // Send username
                fputs($socket, base64_encode($username) . "\r\n");
                $response = fgets($socket, 515);
                echo "Username response: " . htmlspecialchars($response) . "<br>";
                
                // Send password
                fputs($socket, base64_encode($password) . "\r\n");
                $response = fgets($socket, 515);
                echo "Password response: " . htmlspecialchars($response) . "<br>";
                
                if (substr($response, 0, 3) == '235') {
                    echo "✅ Authentication successful!<br>";
                } else {
                    echo "❌ Authentication failed<br>";
                }
            } else {
                echo "❌ TLS encryption failed<br>";
            }
        } else {
            echo "❌ STARTTLS failed<br>";
        }
        
        fclose($socket);
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
input, button { padding: 10px; margin: 5px; }
button { background: #007cba; color: white; border: none; cursor: pointer; }
.success { color: green; }
.error { color: red; }
</style>

<form method="POST">
    <h3>Email Tests:</h3>
    <input type="email" name="test_email" placeholder="Enter your email" required style="width:300px">
    <br><br>
    <button type="submit" name="test_simple">Test PHP mail() Function</button>
    <button type="submit" name="test_smtp">Test SMTP Connection</button>
</form>

<p><a href="email-test-simple.php">← Back to Simple Test</a></p>