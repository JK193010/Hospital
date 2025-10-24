<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Please log in to access this page.</div>';
    return;
}

// Handle form submissions
$message = '';
$message_type = '';

// Create new patient
if (isset($_POST['add_patient'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $emergency_contact = $_POST['emergency_contact'];
    $emergency_phone = $_POST['emergency_phone'];
    $medical_history = $_POST['medical_history'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO patients (full_name, email, phone, date_of_birth, gender, address, emergency_contact, emergency_phone, medical_history) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $full_name, $email, $phone, $date_of_birth, $gender, $address, $emergency_contact, $emergency_phone, $medical_history);
        
        if ($stmt->execute()) {
            $message = "Patient added successfully!";
            $message_type = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $message = "Error adding patient: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Update patient
if (isset($_POST['update_patient'])) {
    $id = $_POST['patient_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $emergency_contact = $_POST['emergency_contact'];
    $emergency_phone = $_POST['emergency_phone'];
    $medical_history = $_POST['medical_history'];

    $stmt = $conn->prepare("UPDATE patients SET full_name=?, email=?, phone=?, date_of_birth=?, gender=?, address=?, emergency_contact=?, emergency_phone=?, medical_history=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $full_name, $email, $phone, $date_of_birth, $gender, $address, $emergency_contact, $emergency_phone, $medical_history, $id);
    
    if ($stmt->execute()) {
        $message = "Patient updated successfully!";
        $message_type = "success";
    }
}

// Delete patient
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM patients WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Patient deleted successfully!";
        $message_type = "success";
    }
}

// Check if patients table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'patients'")->num_rows > 0;

// Fetch patients with search and filters
$search = $_GET['search'] ?? '';
$gender_filter = $_GET['gender'] ?? '';

if ($table_exists) {
    $query = "SELECT * FROM patients WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sss";
    }

    if (!empty($gender_filter)) {
        $query .= " AND gender = ?";
        $params[] = $gender_filter;
        $types .= "s";
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $patients = $stmt->get_result();
} else {
    $patients = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e8f4f8 0%, #f0f8ff 25%, #f5f5f5 50%, #f0fff0 75%, #e6f3ff 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container-fluid {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1.25rem;
            border-radius: 15px 15px 0 0 !important;
        }

        .table {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .table th {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .table td {
            padding: 1rem;
            border-color: rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-success { background: linear-gradient(135deg, #198754, #20c997); }
        .btn-primary { background: linear-gradient(135deg, #0d6efd, #0dcaf0); }
        .btn-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        .btn-danger { background: linear-gradient(135deg, #dc3545, #e83e8c); }
        .btn-info { background: linear-gradient(135deg, #17a2b8, #6f42c1); }

        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
            border-radius: 6px;
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .border-left-primary { border-left: 4px solid #0d6efd !important; }
        .border-left-success { border-left: 4px solid #198754 !important; }
        .border-left-warning { border-left: 4px solid #ffc107 !important; }
        .border-left-info { border-left: 4px solid #17a2b8 !important; }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .btn-group .btn {
            margin: 2px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateX(5px);
            transition: all 0.3s ease;
        }

        h2, h6 {
            color: #2c3e50;
            font-weight: 700;
        }

        .text-primary { color: #0d6efd !important; }
        .text-muted { color: #6c757d !important; }

        /* Statistics cards */
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-user-injured me-2"></i>Patient Management
        </h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">
            <i class="fas fa-plus me-1"></i>Add New Patient
        </button>
    </div>

    <!-- Database Setup Warning -->
    <?php if (!$table_exists): ?>
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Setup Required</h5>
        <p>The patients table needs to be created. Please run the SQL setup script.</p>
        <button class="btn btn-warning" onclick="runPatientSetup()">Create Patients Table</button>
    </div>
    <?php endif; ?>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($table_exists): ?>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $total_patients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
        $male_patients = $conn->query("SELECT COUNT(*) as count FROM patients WHERE gender = 'Male'")->fetch_assoc()['count'];
        $female_patients = $conn->query("SELECT COUNT(*) as count FROM patients WHERE gender = 'Female'")->fetch_assoc()['count'];
        $today_patients = $conn->query("SELECT COUNT(*) as count FROM patients WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_patients ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Male Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $male_patients ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-male fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Female Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $female_patients ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-female fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                New Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_patients ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter Patients</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="patients">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="gender">
                        <option value="">All Genders</option>
                        <option value="Male" <?= $gender_filter === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender_filter === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $gender_filter === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Patients List</h6>
            <span class="badge bg-primary"><?= $patients->num_rows ?> patients</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="patientsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Contact Info</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Emergency Contact</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($patients && $patients->num_rows > 0): ?>
                            <?php while ($patient = $patients->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $patient['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($patient['full_name']) ?></strong>
                                    <?php if (!empty($patient['medical_history'])): ?>
                                    <br><small class="text-muted"><?= substr(htmlspecialchars($patient['medical_history']), 0, 50) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($patient['email'])): ?>
                                    <div><i class="fas fa-envelope text-muted me-1"></i> <?= htmlspecialchars($patient['email']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['phone'])): ?>
                                    <div><i class="fas fa-phone text-muted me-1"></i> <?= htmlspecialchars($patient['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $patient['date_of_birth'] ? date('M j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?= $patient['gender'] === 'Male' ? 'bg-primary' : 
                                           ($patient['gender'] === 'Female' ? 'bg-warning' : 'bg-secondary') ?>">
                                        <i class="fas fa-<?= $patient['gender'] === 'Male' ? 'male' : 'female' ?> me-1"></i>
                                        <?= $patient['gender'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($patient['emergency_contact'])): ?>
                                    <div><strong><?= htmlspecialchars($patient['emergency_contact']) ?></strong></div>
                                    <div><small class="text-muted"><?= htmlspecialchars($patient['emergency_phone'] ?? '') ?></small></div>
                                    <?php else: ?>
                                    <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($patient['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#viewPatientModal<?= $patient['id'] ?>">
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editPatientModal<?= $patient['id'] ?>">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                        <a href="?delete=<?= $patient['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this patient? This will also delete their appointments.')">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </a>
                                    </div>

                                    <!-- View Patient Modal -->
                                    <div class="modal fade" id="viewPatientModal<?= $patient['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Patient Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Personal Information</h6>
                                                            <p><strong>Full Name:</strong> <?= htmlspecialchars($patient['full_name']) ?></p>
                                                            <p><strong>Date of Birth:</strong> <?= $patient['date_of_birth'] ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></p>
                                                            <p><strong>Gender:</strong> <?= $patient['gender'] ?></p>
                                                            <?php if (!empty($patient['email'])): ?>
                                                            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($patient['phone'])): ?>
                                                            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Address & Emergency</h6>
                                                            <?php if (!empty($patient['address'])): ?>
                                                            <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($patient['emergency_contact'])): ?>
                                                            <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($patient['emergency_contact']) ?></p>
                                                            <p><strong>Emergency Phone:</strong> <?= htmlspecialchars($patient['emergency_phone'] ?? '') ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($patient['medical_history'])): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Medical History</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                <?= nl2br(htmlspecialchars($patient['medical_history'])) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Patient Modal -->
                                    <div class="modal fade" id="editPatientModal<?= $patient['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Patient</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Full Name *</label>
                                                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($patient['full_name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($patient['email'] ?? '') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Phone</label>
                                                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($patient['phone'] ?? '') ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Date of Birth</label>
                                                                <input type="date" name="date_of_birth" class="form-control" value="<?= $patient['date_of_birth'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Gender</label>
                                                                <select name="gender" class="form-select">
                                                                    <option value="Male" <?= $patient['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                                                    <option value="Female" <?= $patient['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                                                    <option value="Other" <?= $patient['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Address</label>
                                                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Emergency Contact Name</label>
                                                                <input type="text" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Emergency Contact Phone</label>
                                                                <input type="text" name="emergency_phone" class="form-control" value="<?= htmlspecialchars($patient['emergency_phone'] ?? '') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Medical History</label>
                                                            <textarea name="medical_history" class="form-control" rows="4" placeholder="Any relevant medical history, allergies, conditions..."><?= htmlspecialchars($patient['medical_history'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_patient" class="btn btn-primary">Update Patient</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-user-injured fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No patients found</h5>
                                    <p class="text-muted">Add your first patient to get started</p>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                        <i class="fas fa-plus me-1"></i>Add First Patient
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email address">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact" class="form-control" placeholder="Emergency contact name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact Phone</label>
                            <input type="text" name="emergency_phone" class="form-control" placeholder="Emergency contact phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Medical History</label>
                        <textarea name="medical_history" class="form-control" rows="4" placeholder="Any relevant medical history, allergies, conditions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_patient" class="btn btn-primary">Add Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function runPatientSetup() {
    if (confirm('This will create the patients table. Continue?')) {
        // You can implement AJAX call here to run the SQL
        alert('Patients table creation would be implemented here.');
    }
}
</script>
</body>
</html>