<?php
require_once __DIR__ . '/../../includes/db_connect.php';

// Check if user is logged in
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
        <a href="#" onclick="runPatientSetup()" class="btn btn-warning">Create Patients Table</a>
    </div>
    <?php endif; ?>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
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
            <div class="card border-left-primary shadow h-100 py-2">
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
            <div class="card border-left-info shadow h-100 py-2">
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
            <div class="card border-left-warning shadow h-100 py-2">
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
            <div class="card border-left-success shadow h-100 py-2">
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
                    <select class="form-control" name="gender">
                        <option value="">All Genders</option>
                        <option value="Male" <?= $gender_filter === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender_filter === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $gender_filter === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
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
                                <td><?= $patient['id'] ?></td>
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
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editPatientModal<?= $patient['id'] ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?= $patient['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this patient? This will also delete their appointments.')">
                                            <i class="fas fa-trash"></i> Delete
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
                                                            <p><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
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
                                                                <select name="gender" class="form-control">
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
                            <select name="gender" class="form-control">
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

<script>
function runPatientSetup() {
    if (confirm('This will create the patients table. Continue?')) {
        // You can implement AJAX call here to run the SQL
        alert('Patients table creation would be implemented here.');
    }
}

// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const fa = document.createElement('link');
    fa.rel = 'stylesheet';
    fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    document.head.appendChild(fa);
}
</script>