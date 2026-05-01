<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

// Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $exam_id      = intval($_POST['exam_id'] ?? 0);
    $class_id     = intval($_POST['class_id'] ?? 0);
    $subject_name = trim($_POST['subject_name'] ?? '');
    $exam_date    = $_POST['exam_date'] ?? '';
    $start_time   = $_POST['start_time'] ?? '';
    $end_time     = $_POST['end_time'] ?? '';

    $errors = [];

    // Basic validation
    if ($exam_id <= 0) $errors[] = "Invalid exam.";
    if ($class_id <= 0) $errors[] = "Invalid class.";
    if (empty($subject_name)) $errors[] = "Subject name is required.";
    if (empty($exam_date)) $errors[] = "Exam date is required.";
    if (empty($start_time) || empty($end_time)) $errors[] = "Start and end time are required.";

    // Duration check
    $duration = (strtotime($end_time) - strtotime($start_time)) / 3600;
    if ($duration < 1 || $duration > 4) $errors[] = "Duration must be between 1–4 hours.";

    if (empty($errors)) {

        // ❌ Check duplicate subject in same exam
        $check_subject = $conn->prepare("
            SELECT er.id 
            FROM exam_routine er
            JOIN subjects s ON s.subject_id = er.subject_id
            WHERE er.exam_id = ? AND s.subject_name = ?
        ");
        $check_subject->bind_param("is", $exam_id, $subject_name);
        $check_subject->execute();
        if ($check_subject->get_result()->num_rows > 0) {
            $errors[] = "This subject is already added in this exam!";
        }

        // ❌ Check same date already used
        $check_date = $conn->prepare("
            SELECT id FROM exam_routine 
            WHERE exam_id = ? AND exam_date = ?
        ");
        $check_date->bind_param("is", $exam_id, $exam_date);
        $check_date->execute();
        if ($check_date->get_result()->num_rows > 0) {
            $errors[] = "An exam is already scheduled on this date!";
        }

        // Stop if errors
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: view_datesheet.php?exam_id=" . $exam_id);
            exit;
        }

        // Ensure subject exists
        $stmt = $conn->prepare("SELECT subject_id FROM subjects WHERE subject_name=? AND class_id=?");
        $stmt->bind_param("si", $subject_name, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $subject_id = $result->fetch_assoc()['subject_id'];
        } else {
            $status = 'active';
            $insert = $conn->prepare("INSERT INTO subjects (subject_name, class_id, status) VALUES (?, ?, ?)");
            $insert->bind_param("sis", $subject_name, $class_id, $status);
            $insert->execute();
            $subject_id = $insert->insert_id;
        }

        // Insert routine
        $stmt_routine = $conn->prepare("
            INSERT INTO exam_routine (exam_id, subject_id, exam_date, start_time, end_time)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_routine->bind_param("iisss", $exam_id, $subject_id, $exam_date, $start_time, $end_time);

        if ($stmt_routine->execute()) {
            $_SESSION['success'] = "Subject added successfully!";
        } else {
            $_SESSION['errors'] = ["DB Error: " . $stmt_routine->error];
        }

        header("Location: view_datesheet.php?exam_id=" . $exam_id);
        exit;
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: view_datesheet.php?exam_id=" . $exam_id);
        exit;
    }
}

// Get exam
$exam_id = intval($_GET['exam_id'] ?? 0);
if ($exam_id <= 0) die("Invalid Exam ID");

$exam_query = $conn->prepare("
    SELECT e.exam_id, e.exam_title, e.class_id, c.class_name, e.start_date, e.end_date, s.session_name
    FROM exams e
    JOIN classes c ON c.class_id = e.class_id
    JOIN sessions s ON c.session_id = s.session_id
    WHERE e.exam_id = ?
");
$exam_query->bind_param("i", $exam_id);
$exam_query->execute();
$exam = $exam_query->get_result()->fetch_assoc();

if (!$exam) die("Exam not found");

// Fetch routine
$routine = $conn->prepare("
    SELECT s.subject_name, er.exam_date, er.start_time, er.end_time
    FROM exam_routine er
    JOIN subjects s ON s.subject_id = er.subject_id
    WHERE er.exam_id = ?
    ORDER BY er.exam_date ASC
");
$routine->bind_param("i", $exam_id);
$routine->execute();
$routine_result = $routine->get_result();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Date Sheet</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
<h3><?= htmlspecialchars($exam['exam_title']) ?></h3>
<p>
Class: <?= $exam['class_name'] ?> |
Batch: <?= $exam['session_name'] ?><br>
<?= $exam['start_date'] ?> to <?= $exam['end_date'] ?>
</p>

<button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#form">+ Add Subject</button>

<!-- Alerts -->
<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['errors'])): ?>
<?php foreach($_SESSION['errors'] as $e): ?>
<div class="alert alert-danger"><?= $e; ?></div>
<?php endforeach; unset($_SESSION['errors']); ?>
<?php endif; ?>

<!-- FORM -->
<div id="form" class="collapse mb-4">
<div class="card p-3">

<form method="POST">
<input type="hidden" name="exam_id" value="<?= $exam['exam_id'] ?>">
<input type="hidden" name="class_id" value="<?= $exam['class_id'] ?>">

<select name="subject_name" class="form-control mb-2" required>
<option value="">Select Subject</option>

<?php
$subjects = $conn->prepare("SELECT subject_name FROM subjects WHERE class_id=? AND status='active'");
$subjects->bind_param("i", $exam['class_id']);
$subjects->execute();
$res = $subjects->get_result();

while($s = $res->fetch_assoc()):
?>
<option value="<?= htmlspecialchars($s['subject_name']) ?>">
<?= htmlspecialchars($s['subject_name']) ?>
</option>
<?php endwhile; ?>
</select>

<input type="date" name="exam_date" class="form-control mb-2"
min="<?= $exam['start_date'] ?>" max="<?= $exam['end_date'] ?>" required>

<div class="row">
<div class="col">
<input type="time" name="start_time" class="form-control" required>
</div>
<div class="col">
<input type="time" name="end_time" class="form-control" required>
</div>
</div>

<button class="btn btn-success mt-3 w-100">Save</button>
</form>

</div>
</div>

<!-- TABLE -->
<table class="table table-striped bg-white">
<tr>
<th>Subject</th>
<th>Date</th>
<th>Start</th>
<th>End</th>
</tr>

<?php if($routine_result->num_rows > 0): ?>
<?php while($r = $routine_result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($r['subject_name']) ?></td>
<td><?= $r['exam_date'] ?></td>
<td><?= date("h:i A", strtotime($r['start_time'])) ?></td>
<td><?= date("h:i A", strtotime($r['end_time'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4" class="text-center">No data</td></tr>
<?php endif; ?>

</table>
</main> 
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>