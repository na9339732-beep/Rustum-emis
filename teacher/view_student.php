<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid student ID.");
}
$student_id = intval($_GET['id']);


// --------------------------------------------------------
// 1. Fetch Student Info
// --------------------------------------------------------
$stmt = $conn->prepare("
    SELECT s.student_id, s.student_name, s.student_cnic, s.status,
           c.class_name, c.class_short
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found.");
}


// --------------------------------------------------------
// 2. Update Password
// --------------------------------------------------------
$successMsg = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPass = trim($_POST['new_password']);

    if (strlen($newPass) < 8) {
        $errorMsg = "Password must be at least 8 characters.";
    } else {
        $hashed = password_hash($newPass, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE students SET password=? WHERE student_id=?");
        $stmt->bind_param("si", $hashed, $student_id);

        if ($stmt->execute()) {
            $successMsg = "Password updated successfully!";
        } else {
            $errorMsg = "Failed to update password.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>View Student — EMIS Portal</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<div class="container">

    <!-- Sidebar -->
    <div class="col-lg-2 d-none d-lg-block bg-white glass shadow-sm position-sticky top-0">
      <div class="pt-4 px-lg-2 pt-5">
        <?php include '../partials/sidebar.php'; ?>
      </div>
    </div>

    <!-- Main -->
    <main class="main">

      <div class="header">
        <div style="font-size:20px;font-weight:700">Student Details</div>
      </div>

      <div class="card">

        <h3 style="margin-bottom:10px"><?= htmlspecialchars($student['student_name']) ?></h3>

        <p><strong>CNIC:</strong> <?= htmlspecialchars($student['student_cnic']) ?></p>
        <p><strong>Class:</strong> <?= $student['class_name'] ?> (<?= $student['class_short'] ?>)</p>
        <p><strong>Status:</strong> <?= ucfirst($student['status']) ?></p>

        <hr style="margin:20px 0">

        <!-- Status messages -->
        <?php if ($successMsg): ?>
            <div style="margin:10px 0; color:green; font-weight:bold;"><?= $successMsg ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div style="margin:10px 0; color:red; font-weight:bold;"><?= $errorMsg ?></div>
        <?php endif; ?>

        <!-- Password Reset Form -->
      <!--  <h4>Reset Student Password</h4>
        <form method="POST" style="margin-top:10px">
            <label>New Password</label>
            <input type="password" class ="search" name="new_password" class="form-control" required minlength="8"
                   placeholder="Enter new password">

            <button class="btn" style="margin-top:12px;">Update Password</button>
        </form>
-->
        <br>
        <a href="teacher-students.php?class_id=<?= $_GET['class_id'] ?? '' ?>" class="btn ghost">
            Back
        </a>

      </div>

    </main>

</div>
</body>
</html>
