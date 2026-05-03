<?php
session_start();
include "../config/db.php";

// ======================
// AUTH CHECK
// ======================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}
//======================
// UPDATE STATUS
// ======================
if (isset($_POST['update_status']) && isset($_POST['status'])) {

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'Parents'");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    foreach ($_POST['status'] as $user_id => $status) {
        $user_id = (int)$user_id;
        $status = trim($status);

        $stmt->bind_param("si", $status, $user_id);
        $stmt->execute();
    }

    $stmt->close();

    header("Location: admin-parents.php?msg=success");
    exit;
}

// ======================
// FETCH PARENTS
// ======================
$query = "SELECT user_id, username, email, cnic, status FROM users WHERE role='Parents'";
$result = mysqli_query($conn, $query);

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$total_query = "SELECT COUNT(*) as total FROM users WHERE role='Parents'";
$total_result = mysqli_query($conn, $total_query);
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated results
$query_paginated = "SELECT user_id, username, email, cnic, status FROM users WHERE role='Parents' LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query_paginated);

if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Parents</title>
   <meta name="viewport" content="width=device-width,initial-scale=1" />
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Parents</h2>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="alert alert-success">Statuses updated successfully!</div>
        <?php endif; ?>

        <form method="POST">

            <div class="card shadow-sm">

                <div class="card-body p-0">

                    <table class="table table-hover mb-0">

                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>CNIC</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>

                            <?php 
                                // Fix: case-insensitive match
                                $db_status = strtolower(trim($row['status']));
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['cnic']); ?></td>

                                <td>
                                    <select name="status[<?= $row['user_id'] ?>]" class="form-select form-select-sm">

                                        <option value="Active" <?= ($db_status == 'active') ? 'selected' : '' ?>>
                                            Active
                                        </option>

                                        <option value="Inactive" <?= ($db_status == 'inactive') ? 'selected' : '' ?>>
                                            Inactive
                                        </option>

                                    </select>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

                <div class="card-footer bg-white">
                    <button type="submit" name="update_status" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save All Changes
                    </button>
                </div>

            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Parent pagination" class="mt-4">
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

        </form>

    </main>
</div>

</body>
</html>