<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/mail.php';

if (sendOtpEmail('njue.wambui@strathmore.edu', 'Test User', rand(100000, 999999))) {
    echo "✅ OTP email sent successfully!";
} else {
    echo "❌ Failed to send OTP email. Check error logs.";
}
