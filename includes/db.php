<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Auto-detect environment (local or remote hosting)
$is_local = (
    in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:80']) ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
    strpos(__DIR__, 'laragon') !== false ||
    strpos(__DIR__, 'xampp') !== false ||
    strpos(__DIR__, 'wamp') !== false ||
    (strpos(__DIR__, 'htdocs') !== false && strpos(__DIR__, 'C:') !== false) // Only Windows local paths
);

if ($is_local) {
    // Local development (Laragon)
    $host = 'localhost';
    $db   = 'diffindo';
    $user = 'root';
    $pass = '';
    $port = 3306;
} else {
    // Remote hosting (cPanel)
    $host = 'sql301.hstn.me';
    $db   = 'mseet_40452725_diffindo';
    $user = 'mseet_40452725';
    $pass = 'muskeer@2002#';
    $port = 3306;
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  die('DB Connection failed: ' . $e->getMessage());
}
?>
