<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

// Ensure teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch classes for dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");

// Handle form submission
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $class_id = intval($_POST['class_id']);
    $title = $conn->real_escape_string($_POST['title']);

    if ($class_id && $title && isset($_FILES['file'])) {

        // Create upload directory if not exists
        $upload_dir = "../uploads/materials/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file = $_FILES['file'];
        $filename = time() . "_" . basename($file['name']); 
        $target_path = $upload_dir . $filename;

        // Allowed file types
        $allowed = [
            "pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx",
            "jpg", "jpeg", "png"
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type!";
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_path)) {

                // Insert into DB
                $stmt = $conn->prepare("
                    INSERT INTO study_materials 
                    (class_id, teacher_id, title, file_path, file_type, uploaded_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("iisss", $class_id, $teacher_id, $title, $target_path, $ext);

                if ($stmt->execute()) {
                    $success = "Study material uploaded successfully!";
                } else {
                    $error = "Database error!";
                }
                $stmt->close();
            } else {
                $error = "File upload failed!";
            }
        }
    } else {
        $error = "All fields are required!";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Study Material — EMIS Portal</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<div class="container">

    <!-- Sidebar -->
    <div class="col-lg-2 d-none d-lg-block bg-white glass shadow-sm position-sticky top-0">
        <div class="pt-4 px-lg-2 pt-5">
            <?php include '../partials/sidebar.php'; ?>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main">
        <div class="header">
            <div style="font-size:20px;font-weight:700">Upload Study Material</div>
        </div>

        <div class="card" style="max-width:600px">

            <?php if ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Select Class</label>
                    <select name="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php while($row = $classes->fetch_assoc()): ?>
                            <option value="<?= $row['class_id'] ?>">
                                <?= htmlspecialchars($row['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:12px">
                    <label>Material Title</label>
                    <input type="text" name="title" placeholder="Enter title" required>
                </div>

                <div class="form-group" style="margin-top:12px">
                    <label>Upload File</label>
                    <input type="file" name="file" required>
                </div>

                <button class="btn" style="margin-top:15px;width:100%">Upload</button>

            </form>
        </div>

    </main>

</div>

</body>
</html>
