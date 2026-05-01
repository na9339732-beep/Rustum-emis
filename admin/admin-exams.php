<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

// Check admin login
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../login.php");
    exit;
}

// Fetch exams with class names
$sql = "SELECT e.exam_id, e.exam_title, e.start_date, e.end_date, c.class_name, s.session_name
        FROM exams e 
        JOIN classes c ON e.class_id = c.class_id 
        JOIN sessions s ON c.session_id = s.session_id 
        WHERE s.status = 'Active'
        ORDER BY e.start_date ASC";

$exams = mysqli_query($conn, $sql);

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$total_query = "SELECT COUNT(*) as total FROM exams e 
                JOIN classes c ON e.class_id = c.class_id 
                JOIN sessions s ON c.session_id = s.session_id 
                WHERE s.status = 'Active'";
$total_result = mysqli_query($conn, $total_query);
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated results
$sql_paginated = "SELECT e.exam_id, e.exam_title, e.start_date, e.end_date, c.class_name, s.session_name
                  FROM exams e 
                  JOIN classes c ON e.class_id = c.class_id 
                  JOIN sessions s ON c.session_id = s.session_id 
                  WHERE s.status = 'Active'
                  ORDER BY e.start_date ASC
                  LIMIT $limit OFFSET $offset";
$exams = mysqli_query($conn, $sql_paginated);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Exam Scheduler - School Portal</title>
  <!-- Bootstrap -->
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

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center header-flex mb-4">
      <h3 class="fw-bold">Exam Scheduler</h3>
      <div class="d-flex align-items-center gap-3">
        <input type="text" class="form-control rounded-4 shadow-sm header-search" placeholder="Search exams...">
        <div class="text-end">
          <div class="fw-bold small"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
          <small class="text-muted">Administrator</small>
        </div>
      </div>
    </div>

    <!-- Exam Card -->
    <div class="card p-3 shadow-sm">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Exams & Date Sheets</h5>
        <a class="btn btn-primary" href="./schedule_exam.php">
          <i class="bi bi-plus-lg"></i> Schedule Exam
        </a>
      </div>

      <div class="table-responsive-custom">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>Exam</th>
              <th>Class</th>
              <th>Batch</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($exams) > 0): ?>
              <?php while($row = mysqli_fetch_assoc($exams)): ?>
                <tr>
                  <td><?= htmlspecialchars($row['exam_title']); ?></td>
                  <td><?= htmlspecialchars($row['class_name']); ?></td>
                  <td><?= htmlspecialchars($row['session_name']); ?></td>
                  <td><?= date('d M Y', strtotime($row['start_date'])); ?></td>
                  <td><?= date('d M Y', strtotime($row['end_date'])); ?></td>
                  <td class="text-center">
                    <a class="btn btn-sm btn-outline-primary" href="./view_datesheet.php?exam_id=<?= $row['exam_id']; ?>">View</a>
                    <a class="btn btn-sm btn-outline-warning" href="./edit_exam.php?exam_id=<?= $row['exam_id']; ?>">Edit</a>
                    <a class="btn btn-sm btn-outline-danger" href="./delete_exam.php?exam_id=<?= $row['exam_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No exams scheduled yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <nav aria-label="Exam pagination" class="mt-4">
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

    </div>

  </main>
</div>


</body>
</html>

