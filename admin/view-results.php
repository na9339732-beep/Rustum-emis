<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}


if (!isset($_GET['student_id'])) {
    die("Student ID is required");
}
$student_id = (int)$_GET['student_id'];



$stmt1 = $conn->prepare("
    SELECT s.student_name, c.class_name 
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    WHERE s.student_id = ?
");
$stmt1->bind_param("i", $student_id);
$stmt1->execute();
$student = $stmt1->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found");
}



$stmt2 = $conn->prepare("
    SELECT r.subject, r.marks, r.grade, r.exam_term, se.session_name
    FROM results r
    JOIN sessions se ON r.session_id = se.session_id
    WHERE r.student_id = ?
");
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>View Results — EMIS Portal</title>

   <link rel="stylesheet" href="../assets/styles.css">
   <link rel="stylesheet" href="../assets/sidebar.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
   <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>

<body>
<div class="container">
    
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
        <div class="card">
        <h2>
            Results for 
            <?= htmlspecialchars($student['student_name']) ?> 
            (<?= htmlspecialchars($student['class_name']) ?>)
        </h2>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Grade</th>
                    <th>Exam Term</th>
                    <th>Session</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject']); ?></td>
                            <td><?= htmlspecialchars($row['marks']); ?></td>
                            <td><?= htmlspecialchars($row['grade']); ?></td>
                            <td><?= htmlspecialchars($row['exam_term']); ?></td>
                            <td><?= htmlspecialchars($row['session_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No results found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        
        </table>
        <a href="./index.php" class="btn mt-3">Back to Dashboard</a>
        </div>
    </main>

</div>
</body>
</html>