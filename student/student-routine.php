<?php
session_start();
include '../config/db.php';

// ----------------------
// SELECT CLASS (dynamic)
// ----------------------
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student's profile dynamically with class name
$stmtStudent = $conn->prepare("
    SELECT s.*, c.class_name, c.class_id
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE s.user_id = ?
");
$stmtStudent->bind_param("i", $user_id);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

if ($resultStudent->num_rows === 0) {
    die("Student profile not found.");
}

$student = $resultStudent->fetch_assoc();
$student_id = $student['student_id'];
$class_id = $student['class_id'];

// Get class name
$classSql = "SELECT class_name FROM classes WHERE class_id = ?";
$stmtClass = $conn->prepare($classSql);
$stmtClass->bind_param("i", $class_id);
$stmtClass->execute();
$classResult = $stmtClass->get_result()->fetch_assoc();
$className = $classResult['class_name'] ?? "Unknown Class";

// Get routine from teacher_classes table
$sql = "
    SELECT day, subject, start_time, end_time 
    FROM teacher_classes 
    WHERE class_id = ? AND status = 'Active'
    ORDER BY start_time
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

// Build array by time slot & day
$routine = [];
$days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

// Format time slot and store subjects
while($row = $result->fetch_assoc()){
    $timeSlot = date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time']));
    $routine[$timeSlot][$row['day']] = $row['subject'];
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Class Routine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>
<body>

<div class="container">
  <?php include '../partials/sidebar.php'; ?>

  <main class="main">
    <div class="header">
      <div style="display:flex;gap:12px;align-items:center">
        <div style="font-size:20px;font-weight:700">Class Routine</div>
        <div style="color:var(--muted)"> / <?= $className ?></div>
      </div>
    </div>

    <div class="card">
      <div style="font-weight:700">Weekly Routine — <?= $className ?></div>

      <div style="margin-top:12px;overflow:auto">
        <table class="table" style="min-width:700px">
          <thead>
            <tr>
              <th>Time</th>
              <?php foreach ($days as $d) echo "<th>$d</th>"; ?>
            </tr>
          </thead>

          <tbody>
          <?php foreach ($routine as $time => $daySubjects): ?>
            <tr>
              <td><?= $time ?></td>
              <?php foreach ($days as $d): ?>
                <td><?= $daySubjects[$d] ?? "-" ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

</body>
</html>
