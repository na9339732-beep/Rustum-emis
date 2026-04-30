<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

// Check Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../login.php");
    exit;
}

$where = "";

if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $from = mysqli_real_escape_string($conn, $_GET['from_date']);
    $to   = mysqli_real_escape_string($conn, $_GET['to_date']);

    $where = "WHERE a.attendance_date BETWEEN '$from' AND '$to'";
}

$sql = "
    SELECT 
        a.attendance_date,
        c.class_name,
        SUM(a.status = 'Present') AS present_count,
        SUM(a.status = 'Absent') AS absent_count,
        SUM(a.status = 'Leave') AS leave_count
    FROM attendance a
    JOIN classes c ON c.class_id = a.class_id
    $where
    GROUP BY a.attendance_date, c.class_name
    ORDER BY a.attendance_date DESC
";


$result = mysqli_query($conn, $sql);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manage Attendance — EMIS Portal</title>

    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<body>

<div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main p-4">
        <h2>Admin Dashboard</h2>

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold">Manage Attendance</h3>
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control"
                      value="<?= $_GET['from_date'] ?? '' ?>">
            </div>

            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control"
                      value="<?= $_GET['to_date'] ?? '' ?>">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-md-3">
                <a href="admin-attendance.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
      </div>

      <!-- Attendance Card -->
      <div class="card p-4 shadow-sm col-lg-20">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold">Attendance Calendar</h5>
         <?php
          $from = $_GET['from_date'] ?? '';
          $to   = $_GET['to_date'] ?? '';
          ?>

          <a class="btn btn-outline-primary"
            href="export_attendance.php?from_date=<?= urlencode($from) ?>&to_date=<?= urlencode($to) ?>">
            <i class="bi bi-file-earmark-arrow-down"></i> Export CSV
          </a>
        </div>

        <table class="table table-hover align-middle table-responsive">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Class</th>
              <th>Present</th>
              <th>Absent</th>
              <th>On Leave</th>
            </tr>
          </thead>
          <tbody>

            <?php if(mysqli_num_rows($result) > 0): ?>
              <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $row['attendance_date'] ?></td>
                  <td><?= htmlspecialchars($row['class_name']) ?></td>
                  <td><span class="badge bg-success"><?= $row['present_count'] ?></span></td>
                  <td><span class="badge bg-danger"><?= $row['absent_count'] ?></span></td>
                  <td><span class="badge bg-warning text-dark"><?= $row['leave_count'] ?></span></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No attendance records found.</td>
              </tr>
            <?php endif; ?>

          </tbody>
        </table>
      </div>

    </main>
  </div>
</div>

<!-- Mobile Sidebar -->
<div class="d-block d-md-none">
  <?php include '../partials/sidebar.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
