<?php
session_start();
// ✅ Fix paths: go up to root, then into includes/
require_once '../includes/db_connect.php';
require_once '../includes/mail.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?page=users');
    exit;
}

$user_id = (int)$_GET['id'];
$err = $success = "";

// Fetch user email
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: dashboard.php?page=users');
    exit;
}

// Generate OTP and send email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_user_id'] = $user_id;
    $_SESSION['reset_expires'] = time() + 600; // 10 minutes

    if (sendOtpEmail($user['email'], $user['username'], $otp)) {
        header("Location: set_new_password.php");
        exit;
    } else {
        $err = "Failed to send OTP. Check mail configuration.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Hospital System</title>
    <!-- ✅ Fixed: removed extra spaces in URL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4 text-primary">Reset Password for <?php echo htmlspecialchars($user['username']); ?></h3>
        <?php if ($err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
        <form method="POST">
            <p>Click below to send an OTP to the user’s email for password reset.</p>
            <button type="submit" class="btn btn-warning">Send OTP</button>
            <a href="dashboard.php?page=users" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>