<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

// Check if admin is logged in
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
        die("Invalid status selected.");
    }

    $stmt = $conn->prepare("UPDATE students SET status = ? WHERE student_id = ?");
    $stmt->bind_param("si", $status, $student_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin-students.php"); // reload page
    exit;
}

// Fetch all students
$students = [];
$res = $conn->query("SELECT s.student_id, s.student_name, s.father_name, s.class_id, s.status, c.class_name FROM students s JOIN classes c ON s.class_id = c.class_id ORDER BY s.student_id ASC");
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $students = [];
    $res = $conn->query("SELECT student_id, student_name, father_name, class_id, status FROM students WHERE student_name LIKE '%$search%' OR father_name LIKE '%$search%' ORDER BY student_id ASC");
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students — Admin</title>
<link rel="stylesheet" href="../assets/styles.css">
<link rel="stylesheet" href="../assets/sidebar.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/admin-dashboard.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
table { width:100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
select { padding:5px; }
</style>
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main mt-4">
        <div class="d-flex flex-row align-items-center justify-content-between">
            <div class="d-flex flex-row col-4 align-items-center gap-2">
                <h2>Manage Students</h2>
            </div>
            <div class="d-flex flex-row align-items-center gap-2 justify-content-end">
                <form action="" method="get">
                    <input type="search" name="search" placeholder="Search students..." class="form-control search">
                    <button type="submit" class="btn btn-primary mt-2">Search</button>
                </form>
            </div>
            <div class="d-flex">
                <form action="" method="get">
                    <div class="form-group">
                        <label for="class_id">Filter by Classes</label>
                        <select name="class_id" class="form-control search" onchange="this.form.submit()">
                            <?php
                            $classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");
                            while ($class = $classes->fetch_assoc()) {
                                $selected = (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : '';
                                echo "<option value=\"{$class['class_id']}\" $selected>{$class['class_name']}</option>";
                            }
                            $fbc = $conn->query("SELECT student_id, student_name, father_name, class_id, status FROM students  where class_id = '{$_GET['class_id']}' ORDER BY student_id ASC");
                            if ($fbc->num_rows > 0) {
                                $students = [];
                                while ($row = $fbc->fetch_assoc()) {
                                    $students[] = $row;
                                    $student['class_name'] = $class['class_name'];
                                }
                            } else {
                                $students = [];
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Father Name</th>
                    <!-- <th>Class</th> -->
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_name']) ?></td>
                    <td><?= htmlspecialchars($student['father_name']) ?></td>
                    <!-- <td><?=// htmlspecialchars($student['class_name'] ?? $class['class_name'] ?? '') ?></td> -->

                    <td>
                        <form method="POST" style="display:flex;gap:5px;">
                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="registered" <?= $student['status']=='registered'?'selected':'' ?>>Registered</option>
                                <option value="admitted" <?= $student['status']=='admitted'?'selected':'' ?>>Admitted</option>
                                <option value="banned" <?= $student['status']=='banned'?'selected':'' ?>>Banned</option>
                                <option value="suspended" <?= $student['status']=='suspended'?'selected':'' ?>>Suspended</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="edit-student.php?id=<?= $student['student_id'] ?>" class="btn">Edit</a>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($students)): ?>
                <tr><td colspan="6">No students found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>

