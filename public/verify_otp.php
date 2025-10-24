<?php
session_start();

// Check if OTP session exists
if (!isset($_SESSION['otp'], $_SESSION['otp_user_id'], $_SESSION['otp_expiry'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);

    // Check if OTP expired
    if (time() > $_SESSION['otp_expiry']) {
        $error = "OTP expired. Please login again.";
        session_unset();
        session_destroy();
    } elseif ($entered_otp == $_SESSION['otp']) {
        // OTP correct → log in user
        $_SESSION['user_id'] = $_SESSION['otp_user_id'];
        unset($_SESSION['otp'], $_SESSION['otp_user_id'], $_SESSION['otp_expiry']);

        // Redirect to dashboard or home page
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid OTP. Try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP - Hospital System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 1rem; }
        .btn-primary { background-color: #0d6efd; border: none; }
        .btn-primary:hover { background-color: #0b5ed7; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="text-center mb-3 text-primary">Enter OTP</h3>
                <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="otp" placeholder="6-digit OTP" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                </form>
                <p class="text-center mt-3">
                    Didn't receive OTP? <a href="login.php">Login again</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
