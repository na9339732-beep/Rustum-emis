<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['status'])) {
    $student_id = (int)$_POST['student_id'];
    $status = $_POST['status'];

    $allowed_status = ['registered', 'admitted', 'banned', 'suspended'];
    if (!in_array($status, $allowed_status)) {
        die("Invalid status");
    }

    $stmt = $conn->prepare("UPDATE students SET status=? WHERE student_id=?");
    $stmt->bind_param("si", $status, $student_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin-students.php");
    exit;
}

// Filters
$search   = $_GET['search'] ?? '';
$class_id = $_GET['class_id'] ?? '';
$status   = $_GET['status'] ?? '';

// Base Query
$query = "SELECT s.student_id, s.student_name, s.father_name, s.status, c.class_name 
          FROM students s 
          JOIN classes c ON s.class_id = c.class_id 
          WHERE 1=1";

$params = [];
$types  = "";

// Apply filters dynamically
if (!empty($search)) {
    $query .= " AND (s.student_name LIKE ? OR s.father_name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($class_id)) {
    $query .= " AND s.class_id = ?";
    $params[] = $class_id;
    $types .= "i";
}

if (!empty($status)) {
    $query .= " AND s.status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY s.student_id ASC";

// Prepare + Execute
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch classes for filter
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">

<style>
table { width:100%; margin-top:20px; }
th, td { padding:10px; border:1px solid #ddd; }
</style>
</head>

<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
<main class="main">
<h2>Manage Students</h2>

<!-- FILTER BAR -->
<form method="GET" class="row g-2 align-items-end">

    <!-- Search -->
    <div class="col-md-3">
        <label>Search</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
               class="form-control" placeholder="Name or Father Name">
    </div>

    <!-- Class Filter -->
    <div class="col-md-3">
        <label>Class</label>
        <select name="class_id" class="form-control">
            <option value="">All Classes</option>
            <?php while ($c = $classes->fetch_assoc()): ?>
                <option value="<?= $c['class_id'] ?>" 
                    <?= ($class_id == $c['class_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Status Filter -->
    <div class="col-md-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="registered" <?= $status=='registered'?'selected':'' ?>>Registered</option>
            <option value="admitted" <?= $status=='admitted'?'selected':'' ?>>Admitted</option>
            <option value="banned" <?= $status=='banned'?'selected':'' ?>>Banned</option>
            <option value="suspended" <?= $status=='suspended'?'selected':'' ?>>Suspended</option>
        </select>
    </div>

    <!-- Button -->
    <div class="col-md-3">
        <button class="btn btn-primary w-100">Apply Filters</button>
    </div>

</form>

<!-- TABLE -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Father Name</th>
            <th>Class</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['student_name']) ?></td>
                <td><?= htmlspecialchars($student['father_name']) ?></td>
                <td><?= htmlspecialchars($student['class_name']) ?></td>

                <td>
                    <form method="POST">
                        <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="registered" <?= $student['status']=='registered'?'selected':'' ?>>Registered</option>
                            <option value="admitted" <?= $student['status']=='admitted'?'selected':'' ?>>Admitted</option>
                            <option value="banned" <?= $student['status']=='banned'?'selected':'' ?>>Banned</option>
                            <option value="suspended" <?= $student['status']=='suspended'?'selected':'' ?>>Suspended</option>
                        </select>
                    </form>
                </td>

                <td>
                    <a href="edit-student.php?id=<?= $student['student_id'] ?>" class="btn btn-sm btn-warning">
                        Edit
                    </a>
                    <a href="view-results.php?student_id=<?= $student['student_id'] ?>" class="btn btn-sm btn-info">
                        View Results
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No students found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</main>
</div>
</body>
</html>