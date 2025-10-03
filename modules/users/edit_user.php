<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';


// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from query
if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$user_id = intval($_GET['id']);
$err = "";

// Fetch existing user data
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: manage_users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if (!$username || !$email || !$role) {
        $err = "All fields are required.";
    } else {
        $update = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
        $update->bind_param("sssi", $username, $email, $role, $user_id);
        if ($update->execute()) {
            header("Location: manage_users.php");
            exit;
        } else {
            $err = "Update failed: " . $conn->error;
        }
    }
    $update->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Hospital System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4 text-primary">Edit User</h3>
        <?php if(!empty($err)) echo "<p class='text-danger'>$err</p>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                    <option value="doctor" <?php if($user['role']=='doctor') echo 'selected'; ?>>Doctor</option>
                    <option value="nurse" <?php if($user['role']=='nurse') echo 'selected'; ?>>Nurse</option>
                    <option value="patient" <?php if($user['role']=='patient') echo 'selected'; ?>>Patient</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
