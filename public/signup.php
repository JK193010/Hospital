<?php
// signup.php
session_start();
require_once __DIR__ . "/../includes/db_connect.php";

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (!$username || !$email || !$password || !$confirm_password) {
        $err = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $err = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $err = "Username or email already exists.";
        } else {
            // Hash the password
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user as 'patient' role
            $ins = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'patient')");
            $ins->bind_param('sss', $username, $email, $hash);

            if ($ins->execute()) {
                // Redirect to login with a success flag
                header('Location: login.php?signup=1');
                exit;
            } else {
                $err = "Signup failed: " . $conn->error;
            }
            $ins->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Signup - Hospital System</title>
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
    <div class="col-md-5">
      <div class="card shadow p-4">
        <h3 class="text-center mb-4 text-primary">Create Account</h3>
        <?php if(!empty($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>
        <form method="post">
          <div class="mb-3">
            <input name="username" placeholder="Username" class="form-control" required>
          </div>
          <div class="mb-3">
            <input name="email" type="email" placeholder="Email" class="form-control" required>
          </div>
          <div class="mb-3">
            <input name="password" type="password" placeholder="Password" class="form-control" required>
          </div>
          <div class="mb-3">
            <input name="confirm_password" type="password" placeholder="Confirm Password" class="form-control" required>
          </div>
          <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
        </form>
        <p class="text-center mt-3">
          Already have an account? <a href="login.php">Login here</a>
        </p>
      </div>
    </div>
  </div>
</div>

</body>
</html>
