 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

/* ======================
   HANDLE CSV EXPORT
====================== */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ptm_bookings.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student', 'Teacher', 'Parent', 'Meeting Date', 'Status', 'Booked At']);

    $ptmBookingsCsv = $conn->query("
        SELECT 
            s.student_name,
            t.teacher_name,
            u.username AS parent_name,
            pb.meeting_date,
            pb.status,
            pb.created_at
        FROM ptm_bookings pb
        LEFT JOIN students s ON pb.child_id = s.student_id
        LEFT JOIN teachers t ON pb.teacher_id = t.teacher_id
        LEFT JOIN users u ON pb.booked_by = u.user_id
        ORDER BY pb.meeting_date DESC
    ")->fetch_all(MYSQLI_ASSOC);

    foreach ($ptmBookingsCsv as $row) {
        fputcsv($output, [
            $row['student_name'] ?? 'Unknown',
            $row['teacher_name'] ?? 'Unknown',
            $row['parent_name'] ?? '-',
            $row['meeting_date'],
            $row['status'],
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

/* ======================
   FETCH PTM BOOKINGS
====================== */
$ptmBookings = $conn->query("
    SELECT 
        pb.booking_id,
        pb.meeting_date,
        pb.status,
        s.student_name,
        t.teacher_name,
        u.username AS parent_name,
        pb.created_at
    FROM ptm_bookings pb
    LEFT JOIN students s ON pb.child_id = s.student_id
    LEFT JOIN teachers t ON pb.teacher_id = t.teacher_id
    LEFT JOIN users u ON pb.booked_by = u.user_id
    ORDER BY pb.meeting_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$total_query = "SELECT COUNT(*) as total FROM ptm_bookings pb
                LEFT JOIN students s ON pb.child_id = s.student_id
                LEFT JOIN teachers t ON pb.teacher_id = t.teacher_id
                LEFT JOIN users u ON pb.booked_by = u.user_id";
$total_result = $conn->query($total_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated results
$ptmBookings_paginated = $conn->query("
    SELECT 
        pb.booking_id,
        pb.meeting_date,
        pb.status,
        s.student_name,
        t.teacher_name,
        u.username AS parent_name,
        pb.created_at
    FROM ptm_bookings pb
    LEFT JOIN students s ON pb.child_id = s.student_id
    LEFT JOIN teachers t ON pb.teacher_id = t.teacher_id
    LEFT JOIN users u ON pb.booked_by = u.user_id
    ORDER BY pb.meeting_date DESC
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);

/* ======================
   HANDLE STATUS UPDATE (Optional via GET)
====================== */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    if (in_array($action, ['Confirmed','Cancelled'])) {
        $stmt = $conn->prepare("UPDATE ptm_bookings SET status=? WHERE booking_id=?");
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: view-ptm.php"); // refresh page
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>View PTM Bookings</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/styles.css">
<link rel="stylesheet" href="../assets/sidebar.css">
<link rel="stylesheet" href="../assets/admin-routine.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/admin-dashboard.css">
<link rel="stylesheet" href="../assets/view-ptm.css">
</head>
<body>

<div class="container">

  <?php include '../partials/sidebar.php'; ?>

<!-- Main Content -->
<main class="main">
<h2> PTM Bookings</h2>

<!-- CSV Export Button -->
<form method="get">
    <button type="submit" name="export" value="csv" class="btn">Download CSV</button>
</form>

<table>
<tr>
<th>Student</th>
<th>Teacher</th>
<th>Parent</th>
<th>Meeting Date</th>
<th>Status</th>

<th>Booked At</th>
</tr>

<?php if($ptmBookings_paginated): foreach($ptmBookings_paginated as $ptm): ?>
<tr>
<td><?= htmlspecialchars($ptm['student_name'] ?? 'Unknown') ?></td>
<td><?= htmlspecialchars($ptm['teacher_name'] ?? 'Unknown') ?></td>
<td><?= htmlspecialchars($ptm['parent_name'] ?? '-') ?></td>
<td><?= htmlspecialchars($ptm['meeting_date']) ?></td>
<td class="status-<?= $ptm['status'] ?>"><?= htmlspecialchars($ptm['status'] ?? 'Pending') ?></td>

<td><?= htmlspecialchars($ptm['created_at']) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7">No PTM bookings found.</td></tr>
<?php endif; ?>

</table>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="PTM pagination" class="mt-4">
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

</table>
</main>
</div>
</body>
</html>

