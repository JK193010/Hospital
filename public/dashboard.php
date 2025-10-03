<?php
// ✅ 1. Start session FIRST
session_start();

// ✅ 2. Load DB connection
require_once __DIR__ . '/../includes/db_connect.php';

// ✅ 3. Protect page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ✅ 4. Get requested page
$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Hospital System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background: 
            linear-gradient(rgba(13, 110, 253, 0.85), rgba(40, 167, 69, 0.85)),
            url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2050&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-blend-mode: overlay;
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 40px;
        margin-top: 40px;
        margin-bottom: 40px;
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .table {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        color: white;
    }

    .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 12px 25px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    h2 {
        color: #2c3e50;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        color: #34495e;
        font-weight: 600;
    }

    .stat-card {
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: scale(1.05);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(13, 110, 253, 0.6);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(13, 110, 253, 0.8);
    }
  </style>
</head>
<body>
<div class="container">
  <?php
  // ✅ 5. Include module INSIDE the HTML body
  if ($page === 'users') {
      include __DIR__ . '/../modules/users/manage_users.php';
  } elseif ($page === 'reservations') {
      include __DIR__ . '/../modules/reservations/reservations.php';
  } elseif ($page === 'appointments') {
      include __DIR__ . '/../modules/appointments/appointments.php';
  } else {
      // 👇 SHOW DASHBOARD HOME
      $users_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
      $patients_count = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'];
      $orders_count = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
      $reservations_count = $conn->query("SELECT COUNT(*) AS total FROM reservations")->fetch_assoc()['total'];
      $inventory_count = $conn->query("SELECT COUNT(*) AS total FROM inventories")->fetch_assoc()['total'];

      $recent_orders = $conn->query("SELECT o.*, p.full_name AS patient_name 
                                     FROM orders o 
                                     JOIN patients p ON o.patient_id = p.id 
                                     ORDER BY o.created_at DESC LIMIT 5");
      ?>

      <h2 class="mb-4">🏥 Hospital Management Dashboard</h2>

      <!-- Quick Stats -->
      <div class="row mb-4">
        <div class="col-md-2 mb-3">
          <div class="card text-center bg-primary text-white stat-card">
            <div class="card-body py-4">
              <h3 class="mb-2"><?= htmlspecialchars($users_count) ?></h3>
              <p class="mb-0">👥 Users</p>
            </div>
          </div>
        </div>
        <div class="col-md-2 mb-3">
          <div class="card text-center bg-success text-white stat-card">
            <div class="card-body py-4">
              <h3 class="mb-2"><?= htmlspecialchars($patients_count) ?></h3>
              <p class="mb-0">👨‍⚕️ Patients</p>
            </div>
          </div>
        </div>
        <div class="col-md-2 mb-3">
          <div class="card text-center bg-warning text-white stat-card">
            <div class="card-body py-4">
              <h3 class="mb-2"><?= htmlspecialchars($orders_count) ?></h3>
              <p class="mb-0">📅 Appointments</p>
            </div>
          </div>
        </div>
        <div class="col-md-2 mb-3">
          <div class="card text-center bg-info text-white stat-card">
            <div class="card-body py-4">
              <h3 class="mb-2"><?= htmlspecialchars($reservations_count) ?></h3>
              <p class="mb-0">📋 Reservations</p>
            </div>
          </div>
        </div>
        <div class="col-md-2 mb-3">
          <div class="card text-center bg-danger text-white stat-card">
            <div class="card-body py-4">
              <h3 class="mb-2"><?= htmlspecialchars($inventory_count) ?></h3>
              <p class="mb-0">📦 Inventory</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Appointments -->
      <div class="card">
        <div class="card-body">
          <h4 class="card-title mb-4">📅 Recent Appointments</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Item</th>
                  <th>Quantity</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $recent_orders->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= htmlspecialchars($row['id']) ?></strong></td>
                  <td><?= htmlspecialchars($row['patient_name']) ?></td>
                  <td><?= htmlspecialchars($row['item']) ?></td>
                  <td><span class="badge bg-secondary"><?= htmlspecialchars($row['quantity']) ?></span></td>
                  <td><span class="badge bg-success"><?= htmlspecialchars($row['status']) ?></span></td>
                  <td><small><?= htmlspecialchars($row['created_at']) ?></small></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="mt-4 text-center">
        <a href="?page=users" class="btn btn-primary me-2 mb-2">👥 Manage Users</a>
        <a href="patients.php" class="btn btn-success me-2 mb-2">👨‍⚕️ Manage Patients</a>
        <a href="?page=reservations" class="btn btn-warning me-2 mb-2">📋 Manage Reservations</a>
        <a href="?page=appointments" class="btn btn-info me-2 mb-2">📅 Manage Appointments</a>
        <a href="inventories.php" class="btn btn-danger me-2 mb-2">📦 Manage Inventory</a>
      </div>
  <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>