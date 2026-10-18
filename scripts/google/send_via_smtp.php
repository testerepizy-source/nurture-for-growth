<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\\PHPMailer\\PHPMailer;
use PHPMailer\\PHPMailer\\SMTP;
use Dotenv\\Dotenv;

// Load .env if present
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Configuration: read from environment variables or fallbacks
$smtpHost = getenv('SMTP_HOST') ?: (getenv('MAILERSEND_SMTP_HOST') ?: 'smtp.mailersend.net');
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpUser = getenv('SMTP_USERNAME') ?: getenv('MAILERSEND_SMTP_USERNAME');
$smtpPass = getenv('SMTP_APP_PASSWORD') ?: getenv('MAILERSEND_SMTP_PASSWORD');

$from = getenv('SMTP_FROM') ?: 'admin@nurtureforgrowth.com';
$to = getenv('SMTP_TO') ?: 'client@example.com';
$subject = getenv('SMTP_SUBJECT') ?: 'Undangan Konsultasi — NFG';
$meetLink = getenv('MEET_LINK') ?: '#';
$body = "<p>Halo,</p><p>Silakan bergabung pada sesi konsultasi: <a href=\"$meetLink\">Join Google Meet</a></p>";

$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtpPort;

    //Recipients
    $mail->setFrom($from, 'NFG');
    $mail->addAddress($to);

    //Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
