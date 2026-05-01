<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

// Check admin login
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../login.php");
    exit;
}

// Fetch classes
$classes = mysqli_query($conn, "SELECT class_id, class_name FROM classes WHERE class_status='active' ORDER BY class_name ASC");

//fetch students
$subject = mysqli_query($conn, "SELECT * FROM `subjects` ORDER BY `subjects`.`subject_name` ASC");
// Fetch teachers
$teachers = mysqli_query($conn, "SELECT teacher_id, teacher_name, subject FROM teachers where job_status='Active' ORDER BY teacher_name ASC");

// Fetch sessions
$sessions = mysqli_query($conn, "SELECT session_id, session_name FROM sessions WHERE status='active' ORDER BY starting_date DESC");

// Success/Error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Class Routine</title>

<!-- Bootstrap -->
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">

<style>
.btn-day {
    margin: 2px;
}
.btn-day.active {
    background-color: #3867d6;
    color: white;
}
a{
    text-decoration: none;
    color: inherit;
}
</style>

<script>
function toggleDay(btn){
    btn.classList.toggle('active');
    // toggle checkbox
    const checkbox = btn.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
}

function validateForm() {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;

    if(!startTime || !endTime){
        alert("Start Time and End Time are required!");
        return false;
    }

    const start = new Date("1970-01-01T" + startTime + "Z");
    const end = new Date("1970-01-01T" + endTime + "Z");
    const diff = (end - start) / (1000*60); // difference in minutes

    if(diff < 30){
        alert("End Time must be at least 30 minutes after Start Time.");
        return false;
    }
    if(diff > 60){
        alert("End Time cannot be more than 1 hour after Start Time.");
        return false;
    }

    // check at least one day is selected
    const checkedDays = document.querySelectorAll('input[name="days[]"]:checked');
    if(checkedDays.length === 0){
        alert("Please select at least one day.");
        return false;
    }

    return true;
}
</script>
</head>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <div class="card-header -white mt-5">
            <h4>Create Routine</h4>
        </div>
    <form action="routine_process.php" method="POST" onsubmit="return validateForm();">
        <?php if($success == 1): ?>
            <div class="alert alert-success">Routine created successfully!</div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger">Error creating routine. <?php echo $error;?></div>
        <?php endif; ?>
    <!-- Session -->
        <label class="fw-bold">Session</label>
        <select name="session_id" class="form-select mb-3" required>
            <option value="">Select Session</option>
            <?php while($s = mysqli_fetch_assoc($sessions)): ?>
                <option value="<?= $s['session_id']; ?>"><?= $s['session_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Class -->
        <label class="fw-bold">Class</label>
        <select name="class_id" class="form-select mb-3" required>
            <option value="">Select Class</option>
            <?php while($c = mysqli_fetch_assoc($classes)): ?>
                <option value="<?= $c['class_id']; ?>"><?= $c['class_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Subject -->
        <label class="fw-bold">Subject</label>
        <select name="subject" class="form-select mb-3" required>
            <option value="">Select Subject</option>
            <?php while($t = mysqli_fetch_assoc($subject)): ?>
                <option value="<?= $t['subject_name']; ?>"><?=$t['subject_name']; ?> </option>
            <?php endwhile; ?>
        </select>

        <!-- Teacher -->
        <label class="fw-bold">Teacher</label>
        <select name="teacher_id" class="form-select mb-3" required>
            <option value="">Select Teacher</option>
            <?php while($t = mysqli_fetch_assoc($teachers)): ?>
                <option value="<?= $t['teacher_id']; ?>"><?= $t['teacher_name']; ?> <?= !empty($t['subject']) ? "— ({$t['subject']})" : "" ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Days as buttons -->
        <label class="fw-bold">Days</label><br>
        <?php
        $days = ["Monday","Tuesday","Wednesday","Thursday","Friday"];
        foreach($days as $day):
        ?>
            <button type="button" class="btn ghost btn-day" onclick="toggleDay(this)">
                <?= $day ?>
                <input type="checkbox" name="days[]" value="<?= $day ?>" style="display:none;">
            </button>
        <?php endforeach; ?>
        <br><br>

        <!-- Start & End Time -->
        <label class="fw-bold">Start Time</label>
        <input type="time" name="start_time" id="start_time" class="form-control mb-3" required>

        <label class="fw-bold">End Time</label>
        <input type="time" name="end_time" id="end_time" class="form-control mb-4" required>

        <button type="submit" class="btn btn-primary w-100">Create Routine</button>
        <div class="text-center mt-3 btn">
            <a href="index.php">Back to Dashboard</a>
        </div>
    </form>
</main>
</div>

</body>
</body>
</html>
