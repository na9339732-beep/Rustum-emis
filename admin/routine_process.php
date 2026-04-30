<?php
session_start();
include '../config/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $session_id = $_POST['session_id'];
    $class_id = $_POST['class_id'];
    $subject = $_POST['subject'];
    $teacher_id = $_POST['teacher_id'];
    $days = $_POST['days']; // array
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    if(empty($session_id) || empty($class_id) || empty($teacher_id) || empty($subject) || empty($days) || empty($start_time) || empty($end_time)){
        header("Location: create_routine.php?error=Please fill all fields");
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO teacher_classes (session_id, class_id, teacher_id, subject, day, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    foreach($days as $day){
        $stmt->bind_param("iiissss", $session_id, $class_id, $teacher_id, $subject, $day, $start_time, $end_time);
        $stmt->execute();
    }

    if($stmt){
        header("Location: create_routine.php?success=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
