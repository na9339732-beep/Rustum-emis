<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    header("Location: ../login.php");
    exit;
}
// Fetch Study Materials with class + teacher name
$sql = "
    SELECT sm.*, c.class_name, t.teacher_name
    FROM study_materials sm
    JOIN classes c ON sm.class_id = c.class_id
    JOIN teachers t ON sm.teacher_id = t.teacher_id
    ORDER BY sm.uploaded_at DESC
";
$result = $conn->query($sql);

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$total_query = "SELECT COUNT(*) as total FROM study_materials sm
                JOIN classes c ON sm.class_id = c.class_id
                JOIN teachers t ON sm.teacher_id = t.teacher_id";
$total_result = $conn->query($total_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated results
$sql_paginated = "
    SELECT sm.*, c.class_name, t.teacher_name
    FROM study_materials sm
    JOIN classes c ON sm.class_id = c.class_id
    JOIN teachers t ON sm.teacher_id = t.teacher_id
    ORDER BY sm.uploaded_at DESC
    LIMIT $limit OFFSET $offset
";
$result_paginated = $conn->query($sql_paginated);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Study Materials — EMIS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <style>
      .btn-action {
        font-size: 0.9rem;
        padding: 8px 8px;
        border-radius: 4px;
        color: #ccc;      
        text-decoration: none;
        background: linear-gradient(90deg, #3a6ff8, #6a2ff8);
        margin-right: 4px;           
      }
    </style>
</head>
<body>
  <div class="container">
        <?php include '../partials/sidebar.php'; ?>
    <main class="main">
      <div class="header">
        <div style="display:flex;gap:12px;align-items:center">
          <div style="font-size:20px;font-weight:700">Study Materials</div>
        </div>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between">
          <div style="font-weight:700">Uploaded Materials</div>
          <a class="btn" href="upload_material.php">+ Upload</a>
        </div>

        <div style="margin-top:12px">
          <table class="table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Class</th>
                <th>Teacher</th>
                <th>Type</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>

            <?php while ($row = $result_paginated->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                <td><?= strtoupper($row['file_type']) ?></td>
                <td><?= date("Y-m-d", strtotime($row['uploaded_at'])) ?></td>
                <td>
                  <a href="<?= $row['file_path'] ?>" class="btn-action " download>
                    <i class="bi bi-download"></i>
                  </a>
                   <a href="delete_file.php?id=<?= $row['material_id'] ?>" 
                 class="btn-action mt-1 " 
               onclick="return confirm('Do you want to delete this file?')">
               <i class="bi bi-trash"></i> 
                </a>
                
                </td>
              </tr>
            <?php endwhile; ?>

            <?php if ($total == 0): ?>
              <tr>
                <td colspan="6" style="text-align:center;color:gray">No study materials uploaded yet.</td>
              </tr>
            <?php endif; ?>

            </tbody>
          </table>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
          <nav aria-label="Materials pagination" class="mt-4">
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

            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
