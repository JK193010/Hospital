<?php
session_start();
// ✅ FIX: Use correct path to db_connect.php
require_once '../includes/db_connect.php';

$err = $success = "";

if (!isset($_SESSION['reset_otp'], $_SESSION['reset_user_id'], $_SESSION['reset_expires'])) {
    header('Location: dashboard.php?page=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];

    if (time() > $_SESSION['reset_expires']) {
        $err = "OTP expired.";
        session_unset();
        session_destroy();
    } elseif ($entered_otp != $_SESSION['reset_otp']) {
        $err = "Invalid OTP.";
    } elseif (!$new_password) {
        $err = "Enter a new password.";
    } else {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $_SESSION['reset_user_id']);
        if ($stmt->execute()) {
            $success = "Password reset successfully!";
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
        } else {
            $err = "Failed to reset password.";
        }
        $stmt->close();
    }
}

// Optional: Don't close $conn if other scripts might use it, but okay here
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set New Password - Hospital System</title>
    <!-- ✅ Fixed: removed extra spaces in URL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4 text-primary">Set New Password</h3>
        <?php if ($err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>OTP</label>
                <input type="text" name="otp" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
            <a href="dashboard.php?page=users" class="btn btn-secondary mt-2">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>