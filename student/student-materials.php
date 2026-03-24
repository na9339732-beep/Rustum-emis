<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Database Connection Failed");
}

// Fetch study materials + subject names
$query = "
    SELECT m.* 
    FROM study_materials m
    ORDER BY m.material_id DESC
";

$result = mysqli_query($conn, $query);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Study Materials — EMIS Portal</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
</head>
<body>
  <div class="container">
        <div class="col-lg-2 col-md-2 d-none d-lg-block bg-white glass shadow-sm position-sticky top-0">
            <div class="pt-4 px-lg-2 pt-5">
                <?php include '../partials/sidebar.php'; ?>
            </div>
        </div>

    <main class="main">
      <div class="header">
        <div style="display:flex;gap:12px;align-items:center">
          <div style="font-size:20px;font-weight:700">Study Materials</div>
        </div>

        <div style="display:flex;gap:12px;align-items:center">

          <div style="display:flex;gap:10px;align-items:center text-center">
            <div style="display:flex;flex-direction:column;align-items:flex-end">
              <div style="font-size:13px;font-weight:700">
                  <?= $_SESSION['username']; ?>
              </div>
              <div style="font-size:12px;color:var(--muted)">Student</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between">
            <div style="font-weight:700">Materials</div>
            <a class="btn" href="">Refresh</a>
        </div>

        <div style="margin-top:12px">
          <table class="table">
            <thead>
              <tr>
                <th>Title</th>
                <th>File Type</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>

              <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= htmlspecialchars($row['file_type']); ?></td>
                        <td>
                          <a class="btn ghost" href="../uploads/materials/<?= htmlspecialchars($row['file_path']); ?>" download>
                            Download
                          </a>
                        </td>
                      </tr>
                  <?php endwhile; ?>

              <?php else: ?>
                  <tr>
                    <td colspan="3" style="text-align:center;color:gray;">
                        No study materials uploaded yet.
                    </td>
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
