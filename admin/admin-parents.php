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

// ======================
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

    header("Location: manage_parents.php?msg=success");
    exit;
}

// ======================
// FETCH PARENTS
// ======================
$query = "SELECT user_id, username, email, cnic, status FROM users WHERE role='Parents'";
$result = mysqli_query($conn, $query);

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

<div class="container mt-4">
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

        </form>

    </main>
</div>

</body>
</html>