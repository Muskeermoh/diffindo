<?php
/**
 * Mailer using PHPMailer with fallback logging.
 * Replaces fragile custom SMTP code.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send using PHPMailer configured from email-config.php
 */
function phpmailer_send($to_email, $subject, $html_body, $config) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = (!empty($config['smtp']['encryption']) && $config['smtp']['encryption'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['smtp']['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        if (!empty($config['reply_to'])) $mail->addReplyTo($config['reply_to']);
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = strip_tags($html_body);

        $mail->send();
        error_log("PHPMailer: Email sent to $to_email");
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Build and send order notification. Uses PHPMailer when enabled, otherwise logs to file.
 */
function send_order_notification($to_email, $customer_name, $order_id, $status, $order_details = []) {
    $subject = '';
    $message = '';

    switch ($status) {
        case 'placed':
            $subject = "Order Confirmation - Diffindo Cakes & Bakes (Order #$order_id)";
            $message = "<h2>Order Placed Successfully!</h2>\n<p>Dear " . htmlspecialchars($customer_name) . ",</p>\n<p>Thank you for your order. Your order #$order_id has been received and is being processed.</p>\n<p><strong>Order Details:</strong></p><ul>";
            if (!empty($order_details['items'])) {
                foreach ($order_details['items'] as $item) {
                    $name = htmlspecialchars($item['product_name'] ?? $item['name']);
                    $qty = intval($item['quantity']);
                    $line_total = number_format(($item['price'] ?? 0) * $qty);
                    $message .= "<li>{$name} x {$qty} - Rs {$line_total}</li>";
                }
            }
            $message .= "</ul>\n<p><strong>Total: Rs " . number_format($order_details['total'] ?? 0) . "</strong></p>\n<p><strong>Delivery Date:</strong> " . (!empty($order_details['delivery_datetime']) ? date('M j, Y g:i A', strtotime($order_details['delivery_datetime'])) : 'TBD') . "</p>\n<p>We'll notify you once your order is confirmed.</p>\n<p>Best regards,<br>Diffindo Cakes & Bakes Team</p>";
            break;
        case 'accepted':
            $subject = "Order Confirmed - Diffindo Cakes & Bakes (Order #$order_id)";
            $message = "<h2>Order Confirmed!</h2>\n<p>Dear " . htmlspecialchars($customer_name) . ",</p>\n<p>Your order #$order_id has been confirmed.</p>\n<p>Best regards,<br>Diffindo Cakes & Bakes Team</p>";
            break;
        case 'delivered':
            $subject = "Order Delivered - Diffindo Cakes & Bakes (Order #$order_id)";
            $message = "<h2>Order Delivered</h2>\n<p>Dear " . htmlspecialchars($customer_name) . ",</p>\n<p>Good news — your order #$order_id has been delivered. We hope you enjoy your purchase!</p>\n";
            // Build absolute feedback URL (best-effort)
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $feedback_link = $scheme . '://' . $host . '/feedback.php?order_id=' . urlencode($order_id);
            $message .= "<p>We'd love to hear from you — please <a href=\"" . htmlspecialchars($feedback_link) . "\">leave feedback for your order</a>.";
            $message .= "</p>\n<p>If you have any urgent issues, you may also reply to this email.</p>\n<p>Best regards,<br>Diffindo Cakes & Bakes Team</p>";
            break;
        case 'rejected':
            $subject = "Order Rejected & Refund Processed - Diffindo Cakes & Bakes (Order #$order_id)";
            $message = "<h2>Order Update & Refund Details</h2>\n<p>Dear " . htmlspecialchars($customer_name) . ",</p>\n<p>We regret to inform you that we cannot fulfill your order #$order_id at this time.</p>\n";
            
            if (!empty($order_details['refund_amount'])) {
                $refund_amount = number_format($order_details['refund_amount']);
                $message .= "<p><strong>Refund Amount:</strong> Rs " . $refund_amount . "</p>\n";
            }
            if (!empty($order_details['refund_id'])) {
                $message .= "<p><strong>Refund Transaction ID:</strong> " . htmlspecialchars($order_details['refund_id']) . "</p>\n";
            }
            if (!empty($order_details['refund_amount'])) {
                $message .= "<p style='color: #27ae60; font-weight: bold;'>Your refund has been issued to your original payment method.</p>\n";
                $message .= "<p>Please allow 3-5 business days for the refund to appear on your statement.</p>\n";
            }
            
            $message .= "<p>We are sorry for the inconvenience. If you have any questions, please contact us and we will assist you promptly.</p>\n<p>Best regards,<br>Diffindo Cakes & Bakes Team</p>";
            break;
        default:
            $subject = "Order Update - Diffindo Cakes & Bakes (Order #$order_id)";
            $message = "<p>Dear " . htmlspecialchars($customer_name) . ",</p><p>Your order #$order_id has an update.</p>";
            break;
    }

    $config_path = __DIR__ . '/email-config.php';
    if (!file_exists($config_path)) {
        error_log("EMAIL ERROR: Config file not found: $config_path");
        return false;
    }
    $email_config = include $config_path;

    if (empty($email_config['enable_sending'])) {
        $log_file = __DIR__ . '/../logs/emails.log';
        if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0777, true);
        file_put_contents($log_file, "=== EMAIL LOGGED (DEV) ===\nTime: " . date('Y-m-d H:i:s') . "\nTo: $to_email\nSubject: $subject\nContent:\n" . $message . "\n\n", FILE_APPEND | LOCK_EX);
        error_log("EMAIL LOGGED (DEV): To: $to_email, Subject: $subject");
        return true;
    }

    $sent = phpmailer_send($to_email, $subject, $message, $email_config);
    if (!$sent) {
        $log_file = __DIR__ . '/../logs/emails.log';
        if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0777, true);
        file_put_contents($log_file, "=== EMAIL FAILED (SMTP) ===\nTime: " . date('Y-m-d H:i:s') . "\nTo: $to_email\nSubject: $subject\nContent:\n" . $message . "\n\n", FILE_APPEND | LOCK_EX);
        error_log("EMAIL FALLBACK LOGGED: To: $to_email, Subject: $subject");
    }

    return $sent;
}

function notify_order_placed($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        $order_details = [
            'items' => $items,
            'total' => $order['total'],
            'delivery_datetime' => $order['delivery_datetime']
        ];
        return send_order_notification($order['customer_email'], $order['customer_name'], $order_id, 'placed', $order_details);
    }
    return false;
}

function notify_order_status_change($order_id, $new_status, $refund_amount = null, $refund_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        // Guard against missing email addresses (don't attempt to send empty To)
        if (empty($order['customer_email'])) {
            error_log("EMAIL WARNING: Order #$order_id has no customer email; skipping notification for status '$new_status'.");
            return false;
        }
        $order_details = [];
        if ($refund_amount !== null) {
            $order_details['refund_amount'] = $refund_amount;
        }
        if ($refund_id !== null) {
            $order_details['refund_id'] = $refund_id;
        }
        return send_order_notification($order['customer_email'], $order['customer_name'], $order_id, $new_status, $order_details);
    }
    return false;
}

?>
