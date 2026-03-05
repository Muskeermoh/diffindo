<?php
// PHPMailer one-shot test script
// Usage (CLI): php admin\email-send-test.php recipient@example.com
// Usage (browser): http://.../admin/email-send-test.php?to=recipient@example.com

require_once __DIR__ . '/../vendor/autoload.php';
$config = include __DIR__ . '/../includes/email-config.php';

$to = $argv[1] ?? ($_GET['to'] ?? $config['from_email']);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer test — ' . date('Y-m-d H:i:s');
    $mail->Body = '<p>This is a test email from Diffindo Cakes & Bakes (PHPMailer).</p>';
    $mail->AltBody = 'This is a test email from Diffindo Cakes & Bakes (PHPMailer).';

    $mail->send();
    $msg = "SUCCESS: Email sent to $to";
    echo $msg . PHP_EOL;
    error_log($msg);
    // also append to logs/emails.log
    $log_file = __DIR__ . '/../logs/emails.log';
    if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0777, true);
    file_put_contents($log_file, "[".date('Y-m-d H:i:s')."] SUCCESS: Sent test to $to" . PHP_EOL, FILE_APPEND | LOCK_EX);
} catch (Exception $e) {
    $err = 'PHPMailer Error: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage();
    echo $err . PHP_EOL;
    error_log($err);
    $log_file = __DIR__ . '/../logs/emails.log';
    if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0777, true);
    file_put_contents($log_file, "[".date('Y-m-d H:i:s')."] ERROR: " . $err . PHP_EOL, FILE_APPEND | LOCK_EX);
}

?>