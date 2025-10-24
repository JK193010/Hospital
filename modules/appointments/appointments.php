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

// Create new appointment
if (isset($_POST['add_appointment'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'];
    $description = $_POST['description'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, appointment_type, description, status) VALUES (?, ?, ?, ?, ?, ?, 'scheduled')");
        $stmt->bind_param("iissss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $appointment_type, $description);
        
        if ($stmt->execute()) {
            $message = "Appointment scheduled successfully!";
            $message_type = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $message = "Error scheduling appointment: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Check if tables exist and handle gracefully
$tables_exist = true;
$patients_count = 0;
$doctors_count = 0;

// Check if patients table exists and has data
$patients_result = $conn->query("SHOW TABLES LIKE 'patients'");
if ($patients_result->num_rows > 0) {
    $patients_count = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
} else {
    $tables_exist = false;
}

// Check if appointments table exists
$appointments_result = $conn->query("SHOW TABLES LIKE 'appointments'");
if ($appointments_result->num_rows == 0) {
    $tables_exist = false;
}

// Check if we have doctors
$doctors_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'doctor'")->fetch_assoc()['count'];

// Fetch appointments if tables exist
if ($tables_exist) {
    $filter_date = $_GET['date'] ?? '';
    $filter_status = $_GET['status'] ?? '';
    $filter_doctor = $_GET['doctor'] ?? '';

    $query = "SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone, 
                     u.username AS doctor_name
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              JOIN users u ON a.doctor_id = u.id 
              WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($filter_date)) {
        $query .= " AND a.appointment_date = ?";
        $params[] = $filter_date;
        $types .= "s";
    }

    if (!empty($filter_status)) {
        $query .= " AND a.status = ?";
        $params[] = $filter_status;
        $types .= "s";
    }

    if (!empty($filter_doctor)) {
        $query .= " AND a.doctor_id = ?";
        $params[] = $filter_doctor;
        $types .= "i";
    }

    $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $appointments = $stmt->get_result();
} else {
    $appointments = false;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-calendar-check me-2"></i>Appointment Management
        </h2>
        <?php if ($tables_exist && $patients_count > 0 && $doctors_count > 0): ?>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="fas fa-plus me-1"></i>Schedule New Appointment
        </button>
        <?php endif; ?>
    </div>

    <!-- Database Setup Warning -->
    <?php if (!$tables_exist): ?>
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Setup Required</h5>
        <p>The appointments system requires database tables to be created. Please run the SQL setup script to create the necessary tables.</p>
        <a href="setup_database.php" class="btn btn-warning">Run Database Setup</a>
    </div>
    <?php elseif ($patients_count == 0): ?>
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle me-2"></i>No Patients Found</h5>
        <p>You need to add patients before scheduling appointments.</p>
        <a href="?page=patients" class="btn btn-info">Manage Patients</a>
    </div>
    <?php elseif ($doctors_count == 0): ?>
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle me-2"></i>No Doctors Found</h5>
        <p>You need to add doctors before scheduling appointments.</p>
        <a href="?page=users" class="btn btn-info">Manage Users</a>
    </div>
    <?php endif; ?>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($tables_exist && $patients_count > 0 && $doctors_count > 0): ?>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
        $scheduled_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'scheduled'")->fetch_assoc()['count'];
        $completed_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'completed'")->fetch_assoc()['count'];
        $today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['count'];
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_appointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                Scheduled Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_appointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Upcoming</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $scheduled_appointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $completed_appointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Appointments</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="appointments">
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($filter_date ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="status">
                        <option value="">All Statuses</option>
                        <option value="scheduled" <?= ($filter_status ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="confirmed" <?= ($filter_status ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="in-progress" <?= ($filter_status ?? '') === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= ($filter_status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($filter_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="no-show" <?= ($filter_status ?? '') === 'no-show' ? 'selected' : '' ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="doctor">
                        <option value="">All Doctors</option>
                        <?php
                        $doctors = $conn->query("SELECT id, username FROM users WHERE role = 'doctor'");
                        while ($doctor = $doctors->fetch_assoc()) {
                            $selected = ($filter_doctor ?? '') == $doctor['id'] ? 'selected' : '';
                            echo "<option value='{$doctor['id']}' $selected>{$doctor['username']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Appointments List</h6>
            <span class="badge bg-primary"><?= $appointments->num_rows ?> appointments</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="appointmentsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments && $appointments->num_rows > 0): ?>
                            <?php while ($appointment = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $appointment['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($appointment['patient_name']) ?></strong>
                                    <?php if (!empty($appointment['patient_phone'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($appointment['patient_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></strong>
                                    <br><small class="text-muted"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst($appointment['appointment_type']) ?></span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?= $appointment['status'] === 'scheduled' ? 'bg-warning' : 
                                           ($appointment['status'] === 'confirmed' ? 'bg-primary' : 
                                           ($appointment['status'] === 'in-progress' ? 'bg-info' : 
                                           ($appointment['status'] === 'completed' ? 'bg-success' : 
                                           ($appointment['status'] === 'cancelled' ? 'bg-danger' : 'bg-secondary')))) ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($appointment['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#viewAppointmentModal<?= $appointment['id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal<?= $appointment['id'] ?>">
                                            <i class="fas fa-edit"></i> Status
                                        </button>
                                        <a href="?delete=<?= $appointment['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this appointment?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>

                                    <!-- View Appointment Modal -->
                                    <div class="modal fade" id="viewAppointmentModal<?= $appointment['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Appointment Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Patient Information</h6>
                                                            <p><strong>Name:</strong> <?= htmlspecialchars($appointment['patient_name']) ?></p>
                                                            <?php if (!empty($appointment['patient_phone'])): ?>
                                                            <p><strong>Phone:</strong> <?= htmlspecialchars($appointment['patient_phone']) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Appointment Details</h6>
                                                            <p><strong>Doctor:</strong> <?= htmlspecialchars($appointment['doctor_name']) ?></p>
                                                            <p><strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_date'])) ?></p>
                                                            <p><strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_time'])) ?></p>
                                                            <p><strong>Type:</strong> <?= ucfirst($appointment['appointment_type']) ?></p>
                                                            <p><strong>Status:</strong> 
                                                                <span class="badge <?= $appointment['status'] === 'scheduled' ? 'bg-warning' : 'bg-success' ?>">
                                                                    <?= ucfirst($appointment['status']) ?>
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($appointment['description'])): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Description</h6>
                                                            <p><?= htmlspecialchars($appointment['description']) ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateStatusModal<?= $appointment['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Appointment Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-control" required>
                                                                <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                                <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                                <option value="in-progress" <?= $appointment['status'] === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                                                                <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                                <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                                <option value="no-show" <?= $appointment['status'] === 'no-show' ? 'selected' : '' ?>>No Show</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes</label>
                                                            <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this appointment..."><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
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
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No appointments found</h5>
                                    <p class="text-muted">Schedule a new appointment to get started</p>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                                        <i class="fas fa-plus me-1"></i>Schedule First Appointment
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

<!-- Add Appointment Modal -->
<?php if ($tables_exist && $patients_count > 0 && $doctors_count > 0): ?>
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient *</label>
                            <select name="patient_id" class="form-control" required>
                                <option value="">Select Patient</option>
                                <?php
                                $patients = $conn->query("SELECT * FROM patients");
                                while ($patient = $patients->fetch_assoc()) {
                                    echo "<option value='{$patient['id']}'>{$patient['full_name']}" . 
                                         (isset($patient['phone']) ? " - {$patient['phone']}" : "") . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Doctor *</label>
                            <select name="doctor_id" class="form-control" required>
                                <option value="">Select Doctor</option>
                                <?php
                                $doctors = $conn->query("SELECT id, username FROM users WHERE role = 'doctor'");
                                while ($doctor = $doctors->fetch_assoc()) {
                                    echo "<option value='{$doctor['id']}'>{$doctor['username']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Appointment Date *</label>
                            <input type="date" name="appointment_date" class="form-control" required 
                                   min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Appointment Time *</label>
                            <input type="time" name="appointment_time" class="form-control" required value="09:00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Appointment Type *</label>
                            <select name="appointment_type" class="form-control" required>
                                <option value="checkup">Checkup</option>
                                <option value="consultation">Consultation</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="emergency">Emergency</option>
                                <option value="surgery">Surgery</option>
                                <option value="therapy">Therapy</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the appointment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_appointment" class="btn btn-primary">Schedule Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const fa = document.createElement('link');
    fa.rel = 'stylesheet';
    fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    document.head.appendChild(fa);
}
</script>