<?php
require 'db_connect.php';

// Handle Create
if(isset($_POST['add_inventory'])){
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    
    $stmt = $conn->prepare("INSERT INTO inventories (item_name, quantity, unit_price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $item_name, $quantity, $unit_price);
    $stmt->execute();
}

// Handle Update
if(isset($_POST['update_inventory'])){
    $id = $_POST['id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];

    $stmt = $conn->prepare("UPDATE inventories SET quantity=?, unit_price=? WHERE id=?");
    $stmt->bind_param("idi", $quantity, $unit_price, $id);
    $stmt->execute();
}

// Handle Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM inventories WHERE id=$id");
}

// Fetch Inventories
$inventories = $conn->query("SELECT * FROM inventories");
?>

<div class="container mt-5">
    <h2>Inventories Management</h2>

    <!-- Add Inventory Form -->
    <form method="POST" class="mb-4">
        <input type="text" name="item_name" class="form-control mb-2" placeholder="Item Name" required>
        <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" required>
        <input type="number" step="0.01" name="unit_price" class="form-control mb-2" placeholder="Unit Price">
        <button type="submit" name="add_inventory" class="btn btn-primary">Add Inventory</button>
    </form>

    <!-- Inventories Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $inventories->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['item_name'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $row['unit_price'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="inventories_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
