<?php
// Only check if user is logged in — no role check
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Please log in to access this page.</div>';
    return;
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!-- Content only (no full HTML) -->
<h3 class="mb-4 text-primary">Manage Users</h3>
<div class="text-end mb-3">
    <a href="signup.php" class="btn btn-success">Add New User</a>
</div>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_user.php?id=<?= (int)$user['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <a href="reset_password.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-secondary">Reset Password</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-3">No users found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>