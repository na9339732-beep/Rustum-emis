<?php
session_start();
include '../config/db.php';

// Check admin login
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../login.php");
    exit;
}

// Validate teacher ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid Teacher ID");
}

$teacher_id = $_GET['id'];

// Fetch teacher data
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Teacher not found.");
}

$teacher = $result->fetch_assoc();
$stmt->close();

// Fetch departments for dropdown
$deptQuery = $conn->query("SELECT id, department_name FROM departments ORDER BY department_name ASC");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $teacher_name = $_POST['teacher_name'];
    $cnic = $_POST['cnic'];
    $highest_qualification = $_POST['highest_qualification'];
    $department_id = $_POST['department_id'];
    $subject = $_POST['subject'];
    $job_nature = $_POST['job_nature'];
    $joining = $_POST['joining'];
    $job_status = $_POST['job_status'];

    // Update teacher data
    $stmtUpdate = $conn->prepare("UPDATE teachers SET teacher_name=?, cnic=?, highest_qualification=?, department_id=?, subject=?, job_nature=?, joining=?, job_status=? WHERE teacher_id=?");
    $stmtUpdate->bind_param("sssissssi", $teacher_name, $cnic, $highest_qualification, $department_id, $subject, $job_nature, $joining, $job_status, $teacher_id);
    
    if($stmtUpdate->execute()){
        header("Location: index.php?message=Teacher updated successfully");
        exit;
    } else {
        die("Error updating teacher: " . $stmtUpdate->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <h3>Edit Teacher</h3>
        <hr>

        <form method="POST">

            <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">

            <div class="mb-3">
                <label class="form-label">Teacher Name</label>
                <input type="text" name="teacher_name" class="form-control" required 
                value="<?= htmlspecialchars($teacher['teacher_name']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">CNIC</label>
                <input type="text" name="cnic" class="form-control" required 
                value="<?= htmlspecialchars($teacher['cnic']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Highest Qualification</label>
                <input type="text" name="highest_qualification" class="form-control" required
                value="<?= htmlspecialchars($teacher['highest_qualification']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    <?php while($dept = $deptQuery->fetch_assoc()): ?>
                        <option value="<?= $dept['id']; ?>" 
                            <?= $dept['id'] == $teacher['department_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control"
                value="<?= htmlspecialchars($teacher['subject']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Job Nature</label>
                <select name="job_nature" class="form-select">
                    <option <?= $teacher['job_nature']=='Permanent' ? 'selected':'' ?>>Permanent</option>
                    <option <?= $teacher['job_nature']=='Contract' ? 'selected':'' ?>>Contract</option>
                    <option <?= $teacher['job_nature']=='Visiting' ? 'selected':'' ?>>Visiting</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Joining Date</label>
                <input type="date" name="joining" class="form-control" 
                value="<?= $teacher['joining']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Job Status</label>
                <select name="job_status" class="form-select">
                    <option <?= $teacher['job_status']=='Active' ? 'selected':'' ?>>Active</option>
                    <option <?= $teacher['job_status']=='Resigned' ? 'selected':'' ?>>Resigned</option>
                    <option <?= $teacher['job_status']=='Retired' ? 'selected':'' ?>>Retired</option>
                    <option <?= $teacher['job_status']=='Suspended' ? 'selected':'' ?>>Suspended</option>
                </select>
            </div>

            <button class="btn btn-primary">Update Teacher</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>

        </form>
                    </main>
    </div>

</body>
</html>
