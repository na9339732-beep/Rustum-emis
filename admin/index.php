<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../config/db.php';

// ====== FETCH COUNTS ======
$studentsCount = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$teachersCount = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'];
$classesCount  = $conn->query("SELECT COUNT(*) AS total FROM classes")->fetch_assoc()['total'];

// ====== RECENT ACTIVITY (Last 5 registered users) ======
$recentActivity = $conn->query("
    SELECT username, creation_date
    FROM users
    ORDER BY creation_date DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard — EMIS Portal</title>
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
        <h2>Admin Dashboard</h2>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <small>Total Students</small>
                <h2><?= $studentsCount ?></h2>
            </div>
            <div class="stat-card">
                <small>Total Teachers</small>
                <h2><?= $teachersCount ?></h2>
            </div>
            <div class="stat-card">
                <small>Total Classes</small>
                <h2><?= $classesCount ?></h2>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="header-actions">
                <h5 class="mb-0 fw-bold">Recent User Registrations</h5>
                <a href="admin-students.php" class="btn btn-sm">Manage Students</a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentActivity->num_rows > 0): ?>
                            <?php while ($row = $recentActivity->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['creation_date']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="empty">No recent registrations</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h5 class="fw-bold mb-3">Quick Actions</h5>
            <div class="quick-actions">
                <a href="add_teacher.php" class="btn">
                    <i class="bi bi-person-plus"></i> Add Teacher
                </a>
                <a href="create_session.php" class="btn btn-outline">
                    <i class="bi bi-calendar-plus"></i> Create Session
                </a>
                <a href="create_class.php" class="btn btn-outline">
                    <i class="bi bi-building"></i> Create Class
                </a>
                <a href="manage_groups.php" class="btn btn-outline">
                    <i class="bi bi-book"></i> Manage Groups
                </a>
                  <a href="result.php" class="btn btn-outline">
                    <i class="bi bi-trophy"></i> View Results
                </a>
                <a href="view-ptm.php" class="btn btn-outline">
                    <i class="bi bi-calendar"></i> View PTMS
                </a>
                <a href="add_notification.php" class="btn btn-outline">
                    <i class="bi bi-bell"></i> Create Notification
                </a>
            </div>
        </div>
    </main>
</div>

</body>
</html>
