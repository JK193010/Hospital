<?php
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Please log in to access this page.</div>';
    return;
}

// Handle Create
if (isset($_POST['add_reservation'])) {
    $patient_id = (int)$_POST['patient_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $reservation_date = $_POST['reservation_date'];

    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $reservation_date);
    if (!$dt || $dt->format('Y-m-d\TH:i') !== $reservation_date) {
        $error = "Invalid date format.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reservations (patient_id, doctor_id, reservation_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $patient_id, $doctor_id, $reservation_date);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=reservations");
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=reservations");
        exit;
    }
}

$reservations = $conn->query("
    SELECT 
        r.id, 
        r.reservation_date, 
        r.status, 
        r.created_at,
        p.full_name AS patient_name, 
        u.username AS doctor_name 
    FROM reservations r 
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON r.doctor_id = u.id
");
?>

<div class="card shadow-sm p-4 mb-4">
    <h2 class="text-center text-primary mb-4">Reservations Management</h2>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="patient_id" class="form-select" required>
                <option value="">Select Patient</option>
                <?php
                $patients = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name");
                while ($p = $patients->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($p['id']) . "'>" . htmlspecialchars($p['full_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php
                $doctors = $conn->query("SELECT id, username FROM users WHERE role='doctor' ORDER BY username");
                while ($d = $doctors->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($d['id']) . "'>" . htmlspecialchars($d['username']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="datetime-local" name="reservation_date" class="form-control" required placeholder="dd/mm/yyyy --:--">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" name="add_reservation" class="btn btn-primary w-100">Add Reservation</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead style="background-color: #000; color: white; text-align: center;">
                <tr>
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Patient</th>
                    <th style="padding: 10px;">Doctor</th>
                    <th style="padding: 10px;">Reservation Date</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Created At</th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reservations && $reservations->num_rows > 0): ?>
                    <?php while ($row = $reservations->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($row['status'] ?: 'Pending') ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <a href="reservations_edit.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                                <a href="?delete=<?= (int)$row['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this reservation?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4" style="font-style: italic; color: #666;">
                            No reservations found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>