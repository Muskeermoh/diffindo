<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Environment Debug</h2>";
echo "<strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>SERVER_NAME:</strong> " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'NOT SET') . "<br>";
echo "<strong>__DIR__:</strong> " . __DIR__ . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test detection logic
$is_local = (
    in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:80']) ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
    strpos(__DIR__, 'laragon') !== false ||
    strpos(__DIR__, 'xampp') !== false ||
    strpos(__DIR__, 'wamp') !== false ||
    (strpos(__DIR__, 'htdocs') !== false && strpos(__DIR__, 'C:') !== false)
);

echo "<br><strong>Detected as:</strong> " . ($is_local ? 'LOCAL' : 'REMOTE') . "<br><br>";

if ($is_local) {
    $host = 'localhost';
    $db   = 'diffindo';
    $user = 'root';
    $pass = '';
    $port = 3306;
} else {
    $host = 'sql301.hstn.me';
    $db   = 'mseet_40452725_diffindo';
    $user = 'mseet_40452725';
    $pass = 'muskeer@2002#';
    $port = 3306;
}

echo "<h2>Database Config</h2>";
echo "<strong>Host:</strong> $host<br>";
echo "<strong>Database:</strong> $db<br>";
echo "<strong>User:</strong> $user<br>";
echo "<strong>Port:</strong> $port<br><br>";

echo "<h2>Testing Connection...</h2>";
$charset = 'utf8mb4';

// Try without port first
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
echo "Trying: $dsn<br><br>";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<strong style='color: green;'>✓ Connection successful!</strong><br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    echo "Products in database: " . $result['count'];
    
} catch (PDOException $e) {
    echo "<strong style='color: red;'>✗ Connection failed:</strong> " . $e->getMessage() . "<br><br>";
    
    // Try with port
    echo "Trying with port: mysql:host=$host;port=$port;dbname=$db<br><br>";
    try {
        $dsn2 = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn2, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<strong style='color: green;'>✓ Connection with port successful!</strong><br>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch();
        echo "Products in database: " . $result['count'];
    } catch (PDOException $e2) {
        echo "<strong style='color: red;'>✗ Also failed:</strong> " . $e2->getMessage();
    }
}
?>
