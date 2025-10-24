<?php
session_start();
require_once __DIR__ . "/../includes/db_connect.php";   // $conn
require_once __DIR__ . "/../includes/mail.php";         // sendOtpEmail()

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $identifier = trim($_POST['username']); 
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, email, password, role 
                            FROM users 
                            WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_user_id'] = $user['id'];
        $_SESSION['otp_expiry'] = time() + 600;

        if (sendOtpEmail($user['email'], $user['username'], $otp)) {
            header('Location: verify_otp.php');
            exit;
        } else {
            $error = "❌ Failed to send OTP email. Check SMTP configuration.";
        }
    } else {
        $error = "❌ Invalid username or password";
    }

    $stmt->close();
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Hospital System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 1rem; }
        .btn-primary { background-color: #0d6efd; border: none; }
        .btn-primary:hover { background-color: #0b5ed7; }
    </style>
</head>
<body>

<?php include __DIR__ . "/../includes/header.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="text-center mb-3 text-primary">Login</h3>
                <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="post" action="login.php">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username or Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-center mt-3">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>

</body>
</html>
