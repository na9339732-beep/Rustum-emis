<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

// Fetch Study Materials with class + teacher name
$sql = "
    SELECT sm.*, c.class_name, t.teacher_name
    FROM study_materials sm
    JOIN classes c ON sm.class_id = c.class_id
    JOIN teachers t ON sm.teacher_id = t.teacher_id
    ORDER BY sm.uploaded_at DESC
";
$result = $conn->query($sql);
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

            <?php while ($row = $result->fetch_assoc()): ?>
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

            <?php if ($result->num_rows == 0): ?>
              <tr>
                <td colspan="6" style="text-align:center;color:gray">No study materials uploaded yet.</td>
              </tr>
            <?php endif; ?>

            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
