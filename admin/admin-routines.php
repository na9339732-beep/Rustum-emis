<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

// Check admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch all classes for dropdown
$classQuery = "SELECT c.*, s.session_name FROM classes c
Join sessions s on c.session_id= s.session_id
";
$classResult = mysqli_query($conn, $classQuery);

$selected_class = $_GET['class_id'] ?? '';
$message = '';
if (isset($_GET['deleted'])) {
    $message = 'Routine entry deleted successfully.';
} elseif (isset($_GET['updated'])) {
    $message = 'Routine entry updated successfully.';
}

// Fetch routine
$sql = "
    SELECT
        tc.id AS routine_id,
        tc.day,
        tc.start_time,
        tc.end_time,
        tc.subject,
        c.class_name,
        t.teacher_name
    FROM teacher_classes tc
    JOIN classes c ON tc.class_id = c.class_id
    JOIN teachers t ON tc.teacher_id = t.teacher_id
    WHERE tc.status = 'Active'
";

if (!empty($selected_class)) {
    $sql .= " AND tc.class_id = '" . mysqli_real_escape_string($conn, $selected_class) . "'";
}

$sql .= " ORDER BY FIELD(tc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), tc.start_time";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Database query failed: ' . mysqli_error($conn));
}

// Build routine array
$routine = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = [];

while ($row = mysqli_fetch_assoc($result)) {
    $time_slot = date('H:i', strtotime($row['start_time'])) . " - " . date('H:i', strtotime($row['end_time']));
    if (!in_array($time_slot, $time_slots)) {
        $time_slots[] = $time_slot;
    }
    $routine[$time_slot][$row['day']] = [
        'content' => htmlspecialchars($row['subject']) . "<br><small>" . htmlspecialchars($row['teacher_name']) . "</small>",
        'id' => $row['routine_id']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Class Routine — EMIS Portal</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
    <style>
       
    </style>
</head>
<body>

<div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
        <h2>Class Timings & Routines</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="GET" class="mb-3">
            <div style="max-width: 300px;">
                <label class="form-label fw-bold">Select Class</label>
                <select name="class_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Classes</option>

                    <?php while ($class = mysqli_fetch_assoc($classResult)): ?>
                        <option value="<?= $class['class_id'] ?>"
                            <?= ($selected_class == $class['class_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['class_name']) ?> -  <?= htmlspecialchars($class['session_name']) ?>
                        </option>
                    <?php endwhile; ?>

                </select>
            </div>
        </form>
        <div class="card">
            <div class="header-actions">
                <h5 class="mb-0 fw-bold">Weekly Class Routine</h5>
                <a href="create_routine.php" class="btn">
                    <i class="bi bi-plus-lg"></i> Create Routine
                </a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
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
                                <td colspan="6" class="text-center py-4 empty">
                                    No routines found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($time_slots as $time): ?>
                                <tr>
                                    <td class="time-col"><?= htmlspecialchars($time) ?></td>
                                    <?php foreach ($days as $day): ?>
                                        <td>
                                        <?php if (!empty($routine[$time][$day])): ?>
                                            <div class="routine-entry">
                                                <?= $routine[$time][$day]['content'] ?>
                                                <div class="routine-actions mt-1">
                                                  <a class="edit-button"  href="edit_routine.php?id=<?= $routine[$time][$day]['id'] ?>">Edit</a>
                                                    <span class="text-muted">|</span>
                                                   <a class="delete-button" href="delete_routine.php?id=<?= $routine[$time][$day]['id'] ?>" onclick="return confirm('Delete this routine entry?')">Delete</a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="empty">—</span>
                                        <?php endif; ?>
                                    </td>
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

</body>
</html>