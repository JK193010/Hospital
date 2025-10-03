<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$user_id = (int)$_GET['id'];

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    header('Location: manage_users.php');
    exit;
}

// Delete user
$delete = $conn->prepare("DELETE FROM users WHERE id=?");
$delete->bind_param("i", $user_id);
$delete->execute();
$delete->close();
$conn->close();

header('Location: manage_users.php');
exit;
