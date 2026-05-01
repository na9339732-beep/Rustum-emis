<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
include '../config/db.php';

// Ensure teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id']; 
$success = $error = "";

if (isset($_POST['save'])) {
    $date = $_POST['meeting_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    // 1. Convert times to Unix timestamps to calculate difference
    $startTime = strtotime($start);
    $endTime = strtotime($end);

    // 2. Calculate difference in seconds
    $durationSeconds = $endTime - $startTime;

    // 3. Validation Logic
    if ($endTime <= $startTime) {
        $error = "End time must be after the start time.";
    } elseif ($durationSeconds < 1800) { // 30 mins * 60 secs
        $error = "Meeting duration must be at least 30 minutes.";
    } elseif ($durationSeconds > 7200) { // 2 hours * 3600 secs
        $error = "Meeting duration cannot exceed 2 hours.";
    } else {
        // Validation Passed -> Save to DB
        $stmt = $conn->prepare("
            INSERT INTO teacher_availability 
            (teacher_id, meeting_date, start_time, end_time) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $teacher_id, $date, $start, $end);
        
        if ($stmt->execute()) {
            $success = "Availability saved successfully!";
        } else {
            $error = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Set Meeting Availability</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <style>
        
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            box-sizing: border-box;
        }
        button {
            background: #2563eb;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        a{
            text-decoration:none;
            color:white;
        }
    </style>
</head>
<body>
  <div class="container">
        <?php include '../partials/sidebar.php'; ?>
   <main class="main">
<h2>Set PTM Availability</h2>

<?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
    <?php header("refresh:3; url=index.php"); ?>
<?php endif; ?>

<form method="POST">
    <label>Date</label>
    <input type="date" name="meeting_date" min="<?= date('Y-m-d') ?>" required>

    <label>Start Time</label>
    <input type="time" name="start_time" required>

    <label>End Time</label>
    <input type="time" name="end_time" required>

    <button class="btn" name="save">Save Availability</button>    
    </form>
    <a href="./index.php" class="btn mt-1">Back to Dashboard</a>
</main>
</div>
</body>
</html>
