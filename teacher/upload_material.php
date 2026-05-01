<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

// Ensure teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch active classes for dropdown, including batch/session names
$classes = $conn->query(
    "SELECT c.class_id, c.class_name, s.session_name 
     FROM classes c 
     LEFT JOIN sessions s ON c.session_id = s.session_id 
     WHERE c.class_status = 'active' 
     ORDER BY c.class_name ASC"
);

// Handle form submission
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $class_id = intval($_POST['class_id']);
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));

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
                    (class_id, teacher_id, title, description, file_path, file_type, uploaded_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("iissss", $class_id, $teacher_id, $title, $description, $target_path, $ext);

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
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>
<body>

<div class="container ">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <div class="header">
            <div style="font-size:20px;font-weight:700">Upload Study Material</div>
        </div>

        <div class="card" style="max-width:600px">

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Select Class</label>
                    <select name="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php while($row = $classes->fetch_assoc()): ?>
                            <option value="<?= $row['class_id'] ?>">
                                <?= htmlspecialchars($row['class_name'] . (!empty($row['session_name']) ? ' -- ' . $row['session_name'] : '')) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:12px">
                    <label>Material Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                </div>

                <div class="form-group" style="margin-top:12px">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                </div>

                <div class="form-group" style="margin-top:12px">
                    <label>Upload File</label>
                    <input type="file" name="file" class="form-control" required>
                </div>

                <button class="btn" style="margin-top:15px;width:100%">Upload</button>

            </form>
        </div>

    </main>

</div>

</body>
</html>
