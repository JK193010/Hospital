<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ✅ Fix path to vendor + config
require_once __DIR__ . '/../vendor/autoload.php';
$config = include __DIR__ . '/../conf.php';

function sendOtpEmail($toEmail, $toName, $otpCode) {
    global $config;
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_user'];
        $mail->Password   = $config['smtp_pass'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom($config['smtp_user'], 'Hospital Admin');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "
            <p>Hello {$toName},</p>
            <p>Your one-time code is: <strong>{$otpCode}</strong></p>
            <p>This code expires in 10 minutes.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}<br>";
        echo "Exception: {$e->getMessage()}";
        return false;
    }
}
