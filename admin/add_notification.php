<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $user_type = $_POST['user_type'];

    $stmt = $conn->prepare(
        "INSERT INTO notifications (title, message, user_type) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $title, $message, $user_type);

    if ($stmt->execute()) {
        echo "<script>alert('Notification saved successfully!');</script>";
    } else {
        echo "<script>alert('Error saving notification:S');</script> "  ;
    }

    $stmt->close();
}
?>
 
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../assets/styles.css">
<link rel="stylesheet" href="../assets/sidebar.css">
<link rel="stylesheet" href="../assets/admin-routine.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/admin-dashboard.css">
<title>Add Notification</title>
    <style>
        body { font-family: Arial; }
        input, textarea, select, button {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
        }
        button { background: #2563eb; color: white; border: none; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
        <h2>Add Notification</h2>

        <form method="POST" action="">
            <input type="text" name="title" placeholder="Notification Title" required>

            <textarea name="message" placeholder="Notification Message" rows="5" required></textarea>

            <select name="user_type">
                <option value="Parents">Parents</option>
                <option value="Student">Student</option>
                <option value="Teacher">Teacher</option>
                
            </select>

            <button class="btn" type="submit" name="submit">Save Notification</button>
        </form>
    </main>
</body>
</html>

