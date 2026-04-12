<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

$routine_id = intval($_GET['id'] ?? 0);
if ($routine_id <= 0) {
    header('Location: admin-routines.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM teacher_classes WHERE id = ?');
$stmt->bind_param('i', $routine_id);
$stmt->execute();
$stmt->close();

header('Location: admin-routines.php?deleted=1');
exit;
