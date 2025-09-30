<?php
require_once __DIR__ . '/../../includes/db_connect.php';

// Handle Create
if(isset($_POST['add_order'])){
    $patient_id = $_POST['patient_id'];
    $item = $_POST['item'];
    $quantity = $_POST['quantity'];
    
    $stmt = $conn->prepare("INSERT INTO orders (patient_id, item, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $patient_id, $item, $quantity);
    $stmt->execute();
}

// Handle Update
if(isset($_POST['update_appointment'])){
    $id = $_POST['id'];
    $status = $_POST['status'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE appointments SET status=?, quantity=? WHERE id=?");
    $stmt->bind_param("sii", $status, $quantity, $id);
    $stmt->execute();
}

// Handle Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM appointments WHERE id=$id");
}

// Fetch Orders
$orders = $conn->query("SELECT o.*, p.full_name AS patient_name FROM orders o JOIN patients p ON o.patient_id = p.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Management</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Appointment Management</h2>

    <!-- Add Order Form -->
    <form method="POST" class="card p-3 mb-4 shadow-sm">
        <div class="mb-3">
            <select name="patient_id" class="form-control" required>
                <option value="">Select Patient</option>
                <?php
                $patients = $conn->query("SELECT * FROM patients");
                while($p = $patients->fetch_assoc()){
                    echo "<option value='{$p['id']}'>{$p['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <input type="text" name="item" class="form-control" placeholder="Item" required>
        </div>
        <div class="mb-3">
            <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
        </div>
        <button type="submit" name="add_order" class="btn btn-primary">Add Order</button>
    </form>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['patient_name'] ?></td>
                    <td><?= $row['item'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="appointments_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS (for components like modals, dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
