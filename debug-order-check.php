<?php
include 'includes/db.php';
$stmt = $pdo->query('SELECT id,status,payment_status,stripe_payment_intent_id,stripe_charge_id,total FROM orders ORDER BY id DESC LIMIT 10');
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    echo 'ID=' . $row['id'] . ' status=' . $row['status'] . ' payment_status=' . $row['payment_status'] . ' intent=' . ($row['stripe_payment_intent_id'] ?? 'NULL') . ' charge=' . ($row['stripe_charge_id'] ?? 'NULL') . ' total=' . $row['total'] . "\n";
}
?>