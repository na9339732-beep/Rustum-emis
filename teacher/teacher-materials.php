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
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
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
          <div style="font-size:20px;font-weight:700">Study Materials</div>
          <div style="color:var(--muted)"> / Dynamic</div>
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
                <th>Download</th>
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
                  <a href="<?= $row['file_path'] ?>" class="btn ghost" download>
                    <i class="bi bi-download"></i>
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
