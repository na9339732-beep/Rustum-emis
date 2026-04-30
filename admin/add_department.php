<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

/* ===== ADD GROUP ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name']);

    if ($department_name !== '' ) {
        $stmt = $conn->prepare("
            INSERT INTO departments (department_name)
            VALUE (?)
        ");
        $stmt->bind_param("s", $department_name, $group_short);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_groups.php");
    exit;
}

/* ===== DELETE department ===== */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM departments
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: add_department.php");
    exit;
}

/* ===== FETCH Department ===== */
$department = $conn->query("
    SELECT * FROM departments
    ORDER BY department_name
")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Student Groups</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="../assets/styles.css">
<link rel="stylesheet" href="../assets/sidebar.css">
<link rel="stylesheet" href="../assets/admin-routine.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/admin-dashboard.css">
<style>
.card{padding:20px;background:#fff;border-radius:10px}
.table th,.table td{padding:10px;border:1px solid #ddd}
</style>
</head>

<body>
<div class="container">

  <?php include '../partials/sidebar.php'; ?>

<main class="main ">

<h4 class="mb-3">Manage Departments</h4>

<!-- ADD Department -->
<div class="card mb-4">
<h6>Add New Department</h6>
<form method="post" class="d-flex gap-2">
<input type="text" name="department_name" class="form-control search" placeholder="Full Name (e.g. Computer Science, Mathematics)" required>
<button class="btn btn-primary">Add</button>
</form>
</div>

<!-- Departmenrt LIST -->
<div class="card">
<h6>Existing </h6>

<table class="table">
<tr>
<th>#</th>
<th>Department Name</th>
<th>Action</th>
</tr>

<?php if($department): $i=1; foreach($department as $d): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($d['department_name']) ?></td>
<td>
<a href="?delete=<?= $d['id'] ?>"
   onclick="return confirm('Delete this Department?')"
   class="text-danger">
<i class="bi bi-trash"></i>
</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="3">No Department added</td></tr>
<?php endif; ?>

</table>
</div>

</main>
</div>
</body>
</html>

