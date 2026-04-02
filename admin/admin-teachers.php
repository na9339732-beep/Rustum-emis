<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch teachers with department name
$sql = "
    SELECT t.*, d.department_name
    FROM teachers t
    LEFT JOIN departments d ON t.department_id = d.id
    ORDER BY t.teacher_name ASC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Teachers — EMIS Portal</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-teacher.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<body>

<div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
        <h2>Manage Teachers</h2>

        <div class="card">
            <div class="header-actions">
                <h5 class="mb-0 fw-bold">Teachers Directory</h5>
                <a href="add_teacher.php" class="btn">
                    <i class="bi bi-plus-lg"></i> Add Teacher
                </a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th style="width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($teacher = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($teacher['teacher_name']) ?></td>
                                    <td><?= htmlspecialchars($teacher['subject'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge">
                                            <?= htmlspecialchars($teacher['department_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                                    <td>
                                        <a href="edit_teacher.php?id=<?= $teacher['teacher_id'] ?>" class="btn btn-sm">
                                            Edit
                                        </a>
                                        <a href="delete_teacher.php?id=<?= $teacher['teacher_id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this teacher?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty">
                                    No teachers found.
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
