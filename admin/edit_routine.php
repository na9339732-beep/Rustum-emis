<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

$routine_id = intval($_GET['id'] ?? $_POST['routine_id'] ?? 0);
if ($routine_id <= 0) {
    header('Location: admin-routines.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = intval($_POST['session_id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $teacher_id = intval($_POST['teacher_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $day = trim($_POST['day'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');

    if ($session_id && $class_id && $teacher_id && $subject && $day && $start_time && $end_time) {
        $stmt = $conn->prepare("UPDATE teacher_classes SET session_id = ?, class_id = ?, teacher_id = ?, subject = ?, day = ?, start_time = ?, end_time = ? WHERE id = ?");
        $stmt->bind_param('iiissssi', $session_id, $class_id, $teacher_id, $subject, $day, $start_time, $end_time, $routine_id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: admin-routines.php?updated=1');
            exit;
        }
        $error = 'Unable to update routine entry.';
        $stmt->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}

$routine = $conn->prepare('SELECT * FROM teacher_classes WHERE id = ? LIMIT 1');
$routine->bind_param('i', $routine_id);
$routine->execute();
$routine_result = $routine->get_result();
$routine_data = $routine_result->fetch_assoc();
$routine->close();

if (!$routine_data) {
    header('Location: admin-routines.php');
    exit;
}

$sessions = mysqli_query($conn, "SELECT session_id, session_name FROM sessions ORDER BY starting_date DESC");
$classes = mysqli_query($conn, "SELECT class_id, class_name FROM classes WHERE class_status = 'active' ORDER BY class_name ASC");
$teachers = mysqli_query($conn, "SELECT teacher_id, teacher_name, subject FROM teachers ORDER BY teacher_name ASC");
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Routine — EMIS Portal</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <div class="card-header -white mt-5">
            <h4>Edit Routine Entry</h4>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm" style="max-width: 700px;">
            <form method="POST">
                <input type="hidden" name="routine_id" value="<?= htmlspecialchars($routine_id) ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Batch</label>
                    <select name="session_id" class="form-select" required>
                        <option value="">Select Batch</option>
                        <?php while ($session = mysqli_fetch_assoc($sessions)): ?>
                            <option value="<?= $session['session_id'] ?>" <?= $session['session_id'] == $routine_data['session_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($session['session_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php while ($class = mysqli_fetch_assoc($classes)): ?>
                            <option value="<?= $class['class_id'] ?>" <?= $class['class_id'] == $routine_data['class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Teacher</label>
                    <select name="teacher_id" class="form-select" required>
                        <option value="">Select Teacher</option>
                        <?php while ($teacher = mysqli_fetch_assoc($teachers)): ?>
                            <option value="<?= $teacher['teacher_id'] ?>" <?= $teacher['teacher_id'] == $routine_data['teacher_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['teacher_name']) ?> <?= !empty($teacher['subject']) ? '— (' . htmlspecialchars($teacher['subject']) . ')' : '' ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject</label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($routine_data['subject']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Day</label>
                    <select name="day" class="form-select" required>
                        <option value="">Select Day</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= $day ?>" <?= $day === $routine_data['day'] ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($routine_data['start_time']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($routine_data['end_time']) ?>" required>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="admin-routines.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
