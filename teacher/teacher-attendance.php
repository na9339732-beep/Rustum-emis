<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit;
}


// Get CNIC of logged-in user
$user_cnic = $_SESSION['cnic'];

// Fetch teacher_id from teachers table using CNIC
$stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE cnic = ? LIMIT 1");
$stmt->bind_param("s", $user_cnic);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
    $teacher_id = $teacher['teacher_id'];
} else {
    echo "No teacher found with CNIC: " . htmlspecialchars($user_cnic);
}

// Fetch classes assigned to this teacher
$classes = [];
$stmt = $conn->prepare("
    SELECT DISTINCT c.class_id, c.class_name, c.class_short 
    FROM classes c 
    JOIN teacher_classes tc ON c.class_id = tc.class_id 
    WHERE tc.teacher_id = ?
    ORDER BY c.class_name
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();

// Selected class
$class_id = intval($_GET['class_id'] ?? 0);
$students = [];
$selected_class_name = '';

if ($class_id) {
    foreach ($classes as $c) {
        if ($c['class_id'] == $class_id) {
            $selected_class_name = $c['class_name'] . " (" . $c['class_short'] . ")";
            break;
        }
    }

    // Fetch students
    $stmt = $conn->prepare("
        SELECT student_id, student_name 
        FROM students 
        WHERE class_id = ? AND status = 'registered' 
        ORDER BY student_name
    ");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();

    // Today's attendance
    $today = date('Y-m-d');
    $attendance = [];
    $stmt = $conn->prepare("
        SELECT student_id, status 
        FROM attendance 
        WHERE class_id = ? AND teacher_id = ? AND attendance_date = ?
    ");
    $stmt->bind_param("iis", $class_id, $teacher_id, $today);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $attendance[$row['student_id']] = $row['status'];
    }
    $stmt->close();
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Take Attendance — EMIS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
<style>
    .attendance-table th { background: #f8f9fa; font-weight: 600; text-align: center; }
    .attendance-table td { vertical-align: middle; }
    .form-select { font-size: 0.95rem; }
    .btn-mark-all { font-size: 0.9rem; }
</style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main" >
        <div class="header mb-4">
            <div style="display:flex;gap:12px;align-items:center">
                <div style="font-size:20px;font-weight:700">Track Attendance</div>
                <div style="color:var(--muted)"> / Teacher Portal</div>
            </div>
        </div>

        <!-- Class Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <label class="fw-bold mb-0">Select Class:</label>
                    <select name="class_id" class="form-select w-auto" onchange="this.form.submit()" required>
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>" <?= $class_id == $c['class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['class_name']) ?> (<?= $c['class_short'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <?php if ($class_id && !empty($students)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Attendance — <?= htmlspecialchars($selected_class_name) ?></strong>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="markAllPresent()">Mark All Present</button>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="save_attendance.php">
                        <input type="hidden" name="class_id" value="<?= $class_id ?>">
                        <table class="table table-hover attendance-table mb-0">
                            <thead>
                                <tr>
                                    <th>Roll</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $s): ?>
                                    <tr>
                                        <td>S-<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= htmlspecialchars($s['student_name']) ?></td>
                                        <td>
                                            <select name="status[<?= $s['student_id'] ?>]" class="form-select form-select-sm">
                                                <option value="Present" <?= (!isset($attendance[$s['student_id']]) || $attendance[$s['student_id']] === 'Present') ? 'selected' : '' ?>>Present</option>
                                                <option value="Absent" <?= (isset($attendance[$s['student_id']]) && $attendance[$s['student_id']] === 'Absent') ? 'selected' : '' ?>>Absent</option>
                                                <option value="Leave" <?= (isset($attendance[$s['student_id']]) && $attendance[$s['student_id']] === 'Leave') ? 'selected' : '' ?>>Leave</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="p-3 d-flex justify-content-between align-items-center">
                            <span>Total Students: <strong><?= count($students) ?></strong></span>
                            <button type="submit" class="btn btn-success">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($class_id): ?>
            <div class="card text-center py-5">
                <i class="bi bi-emoji-frown display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No students found in this class</h4>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function markAllPresent() {
    document.querySelectorAll('select[name^="status"]').forEach(select => select.value = 'Present');
}
</script>
</body>
</html>
