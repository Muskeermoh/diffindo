<?php
session_start();
$order_id = $_GET['order_id'] ?? $_SESSION['last_order_id'] ?? 'N/A';
unset($_SESSION['last_order_id']);
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast text-bg-success" role="alert">
        <div class="toast-header">
            <strong class="me-auto">Success</strong>
        </div>
        <div class="toast-body">
            🎉 Payment successful! <?= htmlspecialchars($order_id) ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();

    setTimeout(() => {
        window.location.href = "../index.php";
    }, 3000);
</script>

</body>
</html>