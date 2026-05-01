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

// Get student's profile dynamically with class name
$stmtStudent = $conn->prepare("
    SELECT s.*, c.class_name
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
if($student["status"]=="banned"){
   session_destroy();
    die("<script>alert('You are banned form accessing your account.');</script>");
    header("Location: ../login.php");
    exit();
}
if($student["status"]=="suspended"){
   session_destroy();
    die("<script>alert('You are suspended form accessing your account.');</script>");
    header("Location: ../login.php");
    exit();
}
$student_id = $student['student_id'];

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$stmtCount = $conn->prepare("
    SELECT COUNT(*) as total
    FROM results 
    WHERE student_id = ?
");
if (!$stmtCount) {
    die("Prepare failed: " . $conn->error);
}
$stmtCount->bind_param("i", $student_id);
$stmtCount->execute();
$total = $stmtCount->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);
$stmtCount->close();

// Fetch student results dynamically
$stmtResults = $conn->prepare("
    SELECT subject, marks, grade, exam_term
    FROM results 
    WHERE student_id = ? 
    ORDER BY id DESC
    LIMIT ? OFFSET ?
");
if (!$stmtResults) {
    die("Prepare failed: " . $conn->error);
}
$stmtResults->bind_param("iii", $student_id, $limit, $offset);
$stmtResults->execute();
$results = $stmtResults->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Results — EMIS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>
<body>
  <div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
      <!-- Header -->
      <div class="header">
        <div style="display:flex;gap:12px;align-items:center">
          <div style="font-size:20px;font-weight:700">Results</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center">
          
          <div style="display:flex;gap:10px;align-items:center">
            <div style="display:flex;flex-direction:column;align-items:flex-end">
              <div style="font-size:13px;font-weight:700"><?= htmlspecialchars($_SESSION['username']); ?></div>
              <div style="font-size:12px;color:var(--muted)">Student</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Student Info -->
      <div class="card" style="margin-bottom:12px">
        <div style="font-weight:700">Student Profile</div>
        <table class="table" style="margin-top:8px">
          <tbody>
            <tr>
              <th>Full Name</th>
              <td><?= htmlspecialchars($student['student_name']); ?></td>
            </tr>
            <tr>
              <th>Class</th>
              <td><?= htmlspecialchars($student['class_name']); ?></td>
            </tr>
            <tr>
              <th>Email</th>
              <td><?= htmlspecialchars($student['email']); ?></td>
            </tr>
            <tr>
              <th>Phone</th>
              <td><?= htmlspecialchars($student['phone']); ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Results -->
      <div class="card">
        <div style="font-weight:700">Latest Results</div>

        <table class="table" style="margin-top:12px">
          <thead>
            <tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Exam Term</th></tr>
          </thead>
          <tbody>
            <?php if ($results->num_rows > 0): ?>
                <?php while($row = $results->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['subject']); ?></td>
                    <td><?= htmlspecialchars($row['marks']); ?></td>
                    <td><?= htmlspecialchars($row['grade']); ?></td>
                    <td><?= htmlspecialchars($row['exam_term']); ?></td>
                  </tr>
                <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" style="text-align:center;color:gray">No results found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Results pagination" class="mt-4">
          <ul class="pagination justify-content-center">
              <!-- Previous button -->
              <?php if ($page > 1): ?>
              <li class="page-item">
                  <a class="page-link" href="?page=<?= $page - 1 ?>">
                      Previous
                  </a>
              </li>
              <?php endif; ?>

              <!-- Page numbers -->
              <?php
              $start_page = max(1, $page - 2);
              $end_page = min($total_pages, $page + 2);
              
              if ($start_page > 1): ?>
              <li class="page-item">
                  <a class="page-link" href="?page=1">1</a>
              </li>
              <?php if ($start_page > 2): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
              <?php endif; ?>
              <?php endif; ?>

              <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
              <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                  <a class="page-link" href="?page=<?= $i ?>">
                      <?= $i ?>
                  </a>
              </li>
              <?php endfor; ?>

              <?php if ($end_page < $total_pages): ?>
              <?php if ($end_page < $total_pages - 1): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
              <?php endif; ?>
              <li class="page-item">
                  <a class="page-link" href="?page=<?= $total_pages ?>">
                      <?= $total_pages ?>
                  </a>
              </li>
              <?php endif; ?>

              <!-- Next button -->
              <?php if ($page < $total_pages): ?>
              <li class="page-item">
                  <a class="page-link" href="?page=<?= $page + 1 ?>">
                      Next
                  </a>
              </li>
              <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>

        <div style="margin-top:12px">
          <a class="btn" href="download_report.php?student_id=<?= urlencode($student_id); ?>">Download Report Card</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
