<?php
session_start();
require_once DIR . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute();

        $_SESSION['success_msg'] = "✅ Your message has been sent successfully.";
        header("Location: home.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "❌ Please fill in all fields.";
        header("Location: home.php");
        exit;
    }
}
?>
