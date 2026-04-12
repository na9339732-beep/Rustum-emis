<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit;
}

$user_cnic = $_SESSION['cnic'] ?? '';
if (!$user_cnic) {
    die('Session error: CNIC missing');
}

$stmt = $conn->prepare('SELECT teacher_id, teacher_name FROM teachers WHERE cnic = ? LIMIT 1');
$stmt->bind_param('s', $user_cnic);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    die('Teacher record not found for CNIC: ' . htmlspecialchars($user_cnic));
}

$teacher_id = (int) $teacher['teacher_id'];
$teacher_name = $teacher['teacher_name'];
$selected_session = $_GET['session_id'] ?? '';

// Fetch sessions for this teacher
$stmt = $conn->prepare(
    'SELECT DISTINCT s.session_id, s.session_name 
     FROM sessions s 
     JOIN teacher_classes tc ON s.session_id = tc.session_id 
     WHERE tc.teacher_id = ? 
     ORDER BY s.starting_date DESC'
);
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$session_result = $stmt->get_result();
$sessions = [];
while ($row = $session_result->fetch_assoc()) {
    $sessions[] = $row;
}
$stmt->close();

// Fetch teacher routine
$sql = "
    SELECT tc.day, tc.start_time, tc.end_time, tc.subject, c.class_name, c.class_short, s.session_name
    FROM teacher_classes tc
    JOIN classes c ON tc.class_id = c.class_id
    LEFT JOIN sessions s ON tc.session_id = s.session_id
    WHERE tc.teacher_id = ?
      AND tc.status = 'Active' AND s.status = 'active' AND c.class_status = 'active'
";
$params = [];
$types = 'i';

if (!empty($selected_session)) {
    $sql .= " AND tc.session_id = ?";
    $types .= 'i';
}
$sql .= " ORDER BY FIELD(tc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), tc.start_time";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Database prepare failed: ' . $conn->error);
}

if (!empty($selected_session)) {
    $stmt->bind_param('ii', $teacher_id, $selected_session);
} else {
    $stmt->bind_param('i', $teacher_id);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$routine = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = [];

while ($row = $result->fetch_assoc()) {
    $time_slot = date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time']));
    if (!in_array($time_slot, $time_slots, true)) {
        $time_slots[] = $time_slot;
    }

    $routine[$time_slot][$row['day']] = htmlspecialchars($row['subject']) . '<br><small>' . htmlspecialchars($row['class_name'] . ' (' . $row['class_short'] . ')') . '</small>';
}

$selected_session_name = '';
foreach ($sessions as $session) {
    if ((string) $session['session_id'] === (string) $selected_session) {
        $selected_session_name = $session['session_name'];
        break;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Teacher Routine — EMIS Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/styles.css">
<link rel="stylesheet" href="../assets/sidebar.css">
<link rel="stylesheet" href="../assets/admin-routine.css">
<style>
    .routine-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .time-col { width: 140px; }
    .empty { color: #6c757d; }
</style>
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
        <div class="d-flex justify-content-between align-items-start mb-4 routine-header">
            <div>
                <h2 class="mb-1">My Weekly Routine</h2>
                <p class="mb-0 text-muted">Teacher: <?= htmlspecialchars($teacher_name) ?></p>
                <?php if ($selected_session_name): ?>
                    <p class="mb-0 text-muted">Batch: <?= htmlspecialchars($selected_session_name) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($sessions)): ?>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-bold">Batch</label>
                    <select name="session_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Batches</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['session_id'] ?>" <?= ($selected_session == $session['session_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($session['session_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="time-col">Time Slot</th>
                            <?php foreach ($days as $day): ?>
                                <th><?= htmlspecialchars($day) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($time_slots)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 empty">No routines found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($time_slots as $time): ?>
                                <tr>
                                    <td class="time-col fw-bold"><?= htmlspecialchars($time) ?></td>
                                    <?php foreach ($days as $day): ?>
                                        <td><?= $routine[$time][$day] ?? '<span class="empty">—</span>' ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
