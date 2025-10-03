<?php
$servername = "localhost";
$username = "root";
$password = "1234"; // your DB root password
$dbname = "hospital_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT id, password FROM users");
while ($row = $result->fetch_assoc()) {
    $hashed = password_hash($row['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hashed, $row['id']);
    $stmt->execute();
}

echo "✅ Passwords updated with hashing!";
$conn->close();
?>
