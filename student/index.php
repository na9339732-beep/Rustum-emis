<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
// Get student info
$stmtStudent = $conn->prepare("SELECT s.*, c.class_name
                               FROM students s
                               LEFT JOIN classes c ON s.class_id = c.class_id
                               WHERE s.user_id = ?");
$stmtStudent->bind_param("i", $user_id);
$stmtStudent->execute();
$studentResult = $stmtStudent->get_result();

if ($studentResult->num_rows === 0) die("Student not found.");
$student = $studentResult->fetch_assoc();

// Today's classes
$today = date('l'); // e.g., Monday, Tuesday
$stmtClasses = $conn->prepare("
    SELECT tc.subject, tc.start_time, tc.end_time, t.teacher_name
    FROM teacher_classes tc
    LEFT JOIN teachers t ON tc.teacher_id = t.teacher_id
    WHERE tc.class_id = ? AND tc.day = ? AND tc.status = 'Active'
    ORDER BY tc.start_time ASC
");
$stmtClasses->bind_param("is", $student['class_id'], $today);
$stmtClasses->execute();
$classes = $stmtClasses->get_result();

// Upcoming exams
$stmtExam = $conn->prepare("
    SELECT e.exam_title, er.exam_date
    FROM exams e
    LEFT JOIN exam_routine er ON e.exam_id = er.exam_id
    WHERE e.class_id = ? AND er.exam_date >= CURDATE()
    ORDER BY er.exam_date ASC
    LIMIT 1
");
$stmtExam->bind_param("i", $student['class_id']);
$stmtExam->execute();
$upcomingExam = $stmtExam->get_result()->fetch_assoc();

// Recent study materials (last 5)
$stmtMaterials = $conn->prepare("
    SELECT title, file_path, uploaded_at
    FROM study_materials
    WHERE class_id = ?
    ORDER BY uploaded_at DESC
    LIMIT 5
");
$stmtMaterials->bind_param("i", $student['class_id']);
$stmtMaterials->execute();
$materials = $stmtMaterials->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Student Dashboard — School Portal</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
  <div class="container">
    <div class="col-lg-2 d-none d-lg-block bg-white glass shadow-sm position-sticky top-0">
      <div class="pt-4 px-lg-2 pt-5">
        <?php include '../partials/sidebar.php'; ?>
      </div>
    </div>

    <main class="main">
      <div class="header">
        <div style="display:flex;gap:12px;align-items:center">
          <div style="font-size:20px;font-weight:700">Student Dashboard</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center">
          <div class="search"><input placeholder="Search..." /></div>
          <div style="display:flex;flex-direction:column;align-items:flex-end">
            <div style="font-size:13px;font-weight:700"><?= htmlspecialchars($student['student_name']); ?></div>
            <div style="font-size:12px;color:var(--muted)">Student</div>
          </div>
        </div>
      </div>

      <div class="grid">
        <div class="card">
          <div style="font-weight:700">Today's Classes</div>
          <?php if($classes->num_rows > 0): ?>
            <?php while($cls = $classes->fetch_assoc()): ?>
              <div style="margin-top:8px;color:var(--muted)">
                <?= htmlspecialchars($cls['subject']); ?> (<?= date("H:i", strtotime($cls['start_time'])); ?> - <?= date("H:i", strtotime($cls['end_time'])); ?>)
                — <?= htmlspecialchars($cls['teacher_name']); ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div style="margin-top:8px;color:gray">No classes today</div>
          <?php endif; ?>
        </div>

        <div class="card">
          <div style="font-weight:700">Upcoming Exam</div>
          <?php if($upcomingExam): ?>
            <div style="margin-top:8px;color:var(--muted)">
              <?= htmlspecialchars($upcomingExam['exam_title']); ?> — <?= date("M d, Y", strtotime($upcomingExam['exam_date'])); ?>
            </div>
          <?php else: ?>
            <div style="margin-top:8px;color:gray">No upcoming exams</div>
          <?php endif; ?>
        </div>

        <div class="card">
          <div style="font-weight:700">New Materials</div>
          <div style="margin-top:8px;color:var(--muted)"><?= $materials->num_rows; ?> file<?= $materials->num_rows != 1 ? 's' : ''; ?></div>
        </div>
      </div>

      <div class="card">
        <div style="font-weight:700">Recent Materials</div>
        <ul style="margin-top:8px;color:var(--muted)">
          <?php if($materials->num_rows > 0): ?>
            <?php while($mat = $materials->fetch_assoc()): ?>
              <li>
                <a href="<?= htmlspecialchars($mat['file_path']); ?>" target="_blank">
                  <?= htmlspecialchars($mat['title']); ?> — Download
                </a>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li style="color:gray">No materials available</li>
          <?php endif; ?>
        </ul>
      </div>
    </main>
  </div>
</body>
</html>
