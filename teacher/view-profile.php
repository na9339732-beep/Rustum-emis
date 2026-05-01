<?php
session_start();
require_once '../config/db.php';

/* ======================
   AUTH CHECK
====================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('Teacher Id not found in session');</script>";
    header("Location: index.php");
    exit();
}
$cnic = $_SESSION['cnic'];

$stmt = $conn->prepare("
    SELECT t.*, d.department_name 
    FROM teachers t 
    JOIN departments d 
    ON t.department_id = d.id 
    WHERE t.cnic = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $cnic);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    echo "<script>alert('Teacher not found');</script>";
    header("Location: index.php");
    exit();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <title>Teacher Profile</title>
</head>
<body class="container">
    <?php include '../partials/sidebar.php'; ?>

    <div class="main">
        <h1 class="mb-4">Teacher Profile</h1>

        <div class="row">
            
            <!-- Profile Info -->
            <div class="col-md-8">
                <div class="bg-white p-4 rounded shadow">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($teacher['teacher_name']); ?></p>
                    <p><strong>CNIC:</strong> <?php echo htmlspecialchars($teacher['cnic']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($teacher['subject']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($teacher['department_name']); ?></p>
                </div>
            </div>

            <!-- Profile Image -->
            <div class="col-md-4 text-center">
                <div class="bg-white p-3 rounded shadow">
                    <img 
                        src="../admin/uploads/teachers/<?php echo htmlspecialchars($teacher['photo']); ?>" 
                        alt="Teacher Photo"
                        class="img-fluid rounded-circle"
                        style="width: 200px; height: 216px; object-fit: cover;"
                    >
                </div>
            </div>

        </div>
    </div>
</body>
</html>