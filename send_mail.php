<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . "/mail.php";

// Get email from form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $toEmail = $_POST['email'];
    $toName = "User"; // you can replace with actual name from DB
    $otpCode = rand(100000, 999999);

    if (sendOtpEmail($toEmail, $toName, $otpCode)) {
        echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
                <h2 style='color:green;'>✅ Email sent successfully to $toEmail!</h2>
                <p>Your OTP: <strong>$otpCode</strong></p>
              </div>";
    } else {
        echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
                <h2 style='color:red;'>❌ Email sending failed!</h2>
                <p>Check logs for more details.</p>
              </div>";
    }
} else {
    echo "Invalid request.";
}
