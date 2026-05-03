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
$error = "";

// Get student ID
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($student_id <= 0) {
    $error = "Invalid student ID.";
}

// Fetch student
if (empty($error)) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    if (!$student) {
        $error = "Student not found.";
    }
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_name = trim($_POST['student_name'] ?? '');
    $father_name  = trim($_POST['father_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $class_id     = (int)($_POST['class_id'] ?? 0);
    $group_id     = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
    $status       = $_POST['status'] ?? 'registered';

   
    $city  = $student['city'];
    $state = $student['state'];

    // VALIDATIONS
    if (empty($student_name) || empty($father_name) || empty($email) || empty($phone) || $class_id <= 0) {
        $error = "Please fill in all required fields.";
    }
    elseif (!preg_match("/^[a-zA-Z\s\.\-]{2,50}$/", $student_name)) {
        $error = "Invalid student name.";
    }
    elseif (!preg_match("/^[a-zA-Z\s\.\-]{2,50}$/", $father_name)) {
        $error = "Invalid father name.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    elseif (!preg_match("/^03[0-9]{9}$/", $phone)) {
        $error = "Invalid phone number.";
    }

    $allowed_status = ['registered', 'admitted', 'banned', 'suspended'];
    if (empty($error) && !in_array($status, $allowed_status)) {
        $error = "Invalid status selected.";
    }

    // CHECK EMAIL UNIQUE
    if (empty($error)) {
        $stmtCheck = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
        $stmtCheck->bind_param("si", $email, $student_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $error = "Email already exists for another student.";
        }
        $stmtCheck->close();
    }

    // UPDATE
    if (empty($error)) {
        $stmt = $conn->prepare("
            UPDATE students SET 
                student_name = ?, 
                father_name = ?, 
                email = ?, 
                phone = ?, 
                class_id = ?, 
                group_id = ?, 
                status = ?, 
                city = ?, 
                state = ?
            WHERE student_id = ?
        ");

        $stmt->bind_param("ssssiisssi", 
            $student_name, 
            $father_name, 
            $email, 
            $phone, 
            $class_id, 
            $group_id, 
            $status, 
            $city, 
            $state, 
            $student_id
        );

        if ($stmt->execute()) {
            header("Location: admin-students.php?message=Student updated successfully");
            exit;
        } else {
            $error = "Update failed.";
        }
    }
}
// Fetch classes for dropdown
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch groups for dropdown
$groups = $conn->query("SELECT group_id, group_name FROM student_groups ORDER BY group_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student — Admin</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <h2>Edit Student</h2>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div>
                <label>Student Name</label>
                <input type="text" class="form-control" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>" required>
            </div>
            <div>
                <label>Father Name</label>
                <input type="text" class="form-control"  name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
            </div>
            <div>
                <label>Phone</label>
                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>
            </div>
            <div>
                <label>Class</label>
                <select class="form-control" name="class_id" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['class_id'] ?>" <?= $class['class_id'] == $student['class_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Group</label>
                <select class="form-control" name="group_id">
                    <option value="">-- Select Group --</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['group_id'] ?>" <?= $g['group_id'] == $student['group_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['group_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Status</label>
                <select class="form-control"  name="status" required>
                    <option value="registered" <?= $student['status']=='registered'?'selected':'' ?>>Registered</option>
                    <option value="admitted" <?= $student['status']=='admitted'?'selected':'' ?>>Admitted</option>
                    <option value="banned" <?= $student['status']=='banned'?'selected':'' ?>>Banned</option>
                    <option value="suspended" <?= $student['status']=='suspended'?'selected':'' ?>>Suspended</option>
                </select>
            </div>
            <div>
                <label>City</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($student['city']) ?>" disabled>
            </div>
            <div>
                <label>State</label>
                <input type="text" class="form-control" name="state" value="<?php if(!empty($student['state'])) { echo $student['state']; } ?>" disabled>
            </div>
            <div style="margin-top:10px;">
                <button type="submit" class="btn">Update Student</button>
                <a href="admin-students.php" class="btn">Cancel</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>