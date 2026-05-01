<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

// Ensure teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'] ?? null;

// Get POST data
$class_id = intval($_POST['class_id'] ?? 0);
$statuses = $_POST['status'] ?? []; // array of student_id => status
$today_date = date('Y-m-d');

if(!$class_id || empty($statuses)){
    $_SESSION['error'] = "Invalid data. Please select a class and mark at least one student.";
    header("Location: teacher-attendance.php");
    exit;
}

// Prepare statements
$select_stmt = $conn->prepare("SELECT attendance_id FROM attendance WHERE student_id=? AND class_id=? AND teacher_id=? AND attendance_date=?");
$insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, class_id, teacher_id, attendance_date, status) VALUES (?, ?, ?, ?, ?)");

$update_stmt = $conn->prepare("UPDATE attendance SET status=? WHERE attendance_id=?");
if (!$select_stmt || !$insert_stmt || !$update_stmt) {
    die("Prepare failed: " . $conn->error);
}

foreach($statuses as $student_id => $status){

    $student_id = intval($student_id);

    $select_stmt->bind_param("iiis", $student_id, $class_id, $teacher_id, $today_date);
    $select_stmt->execute();

    if ($select_stmt->error) {
        die("Select Error: " . $select_stmt->error);
    }

    $res = $select_stmt->get_result();

    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        $attendance_id = $row['attendance_id'];

        $update_stmt->bind_param("si", $status, $attendance_id);
        $update_stmt->execute();

        if ($update_stmt->error) {
            die("Update Error: " . $update_stmt->error);
        }

    } else {

        $insert_stmt->bind_param("iiiss", $student_id, $class_id, $teacher_id, $today_date, $status);
        $insert_stmt->execute();

        if ($insert_stmt->error) {
            die("Insert Error: " . $insert_stmt->error);
        }
    }
}

// Close statements
$select_stmt->close();
$insert_stmt->close();
$update_stmt->close();

// Set success message and redirect
$_SESSION['success'] = "Attendance has been saved successfully!";
header("Location: teacher-attendance.php?class_id=$class_id");
exit;
