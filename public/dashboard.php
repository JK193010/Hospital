<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page = $_GET['page'] ?? 'home';

$users_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$patients_count = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'] ?? 0;
$orders_count = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$reservations_count = $conn->query("SELECT COUNT(*) AS total FROM reservations")->fetch_assoc()['total'] ?? 0;
$inventory_count = $conn->query("SELECT COUNT(*) AS total FROM inventories")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Letik Hospital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #230082ff;
      color: #000;
    }
    .navbar, footer {
      background-color: #f1f1f1;
      border: 1px solid #ccc;
    }
    .card {
      border: 1px solid #ccc;
      border-radius: 10px;
      transition: 0.2s ease;
    }
    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    .btn {
      background-color: transparent;
      border: 1px solid #000;
      color: #000;
      border-radius: 8px;
    }
    .btn:hover {
      background-color: #000;
      color: #fff;
    }
  </style>
</head>
<body>
<div class="container">
  <?php
  if ($page === 'users') {
      include DIR . '/../modules/users/manage_users.php';
  } elseif ($page === 'reservations') {
      include DIR . '/../modules/reservations/reservations.php';
  } elseif ($page === 'appointments') {
      include DIR . '/../modules/appointments/appointments.php';
  } else {
      $recent_orders = $conn->query("SELECT o.*, p.full_name AS patient_name 
                                     FROM orders o 
                                     JOIN patients p ON o.patient_id = p.id 
                                     ORDER BY o.created_at DESC LIMIT 5");
  }
  ?>

  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="home.php">Letik Hospital</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active fw-semibold" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="text-center mb-5">
      <h1 class="fw-bold">Hospital Management Dashboard</h1>
      <p>Overview of hospital performance</p>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h4><?= htmlspecialchars($users_count) ?></h4>
            <p>Users</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h4><?= htmlspecialchars($patients_count) ?></h4>
            <p>Patients</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h4><?= htmlspecialchars($orders_count) ?></h4>
            <p>Appointments</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h4><?= htmlspecialchars($reservations_count) ?></h4>
            <p>Reservations</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 text-center">
      <a href="?page=users" class="btn me-2 mb-2">Manage Users</a>
      <a href="patients.php" class="btn me-2 mb-2">Manage Patients</a>
      <a href="?page=reservations" class="btn me-2 mb-2">Manage Reservations</a>
      <a href="?page=appointments" class="btn me-2 mb-2">Manage Appointments</a>
      <a href="inventories.php" class="btn me-2 mb-2">Manage Inventory</a>
    </div>
  </main>

  <footer class="text-center py-3 mt-5">
    <p>© 2025 Letik Hospital. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>