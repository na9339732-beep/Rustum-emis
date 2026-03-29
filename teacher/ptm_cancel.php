<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ptm_id = (int)($data['ptm_id'] ?? 0);
$remarks = $data['remarks'] ?? 'Unspecified';
$teacher_id = $_SESSION['teacher_id'];

if ($ptm_id <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid PTM ID']);
    exit;
}

$stmt = $conn->prepare("
    UPDATE ptm_bookings
    SET status = 'Cancelled', remarks = ?
    WHERE booking_id = ? AND teacher_id = ?
");
$stmt->bind_param("sii", $remarks, $ptm_id, $teacher_id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode([
    'success'  => $ok,
    'redirect' => 'set_meeting_availability.php'
]);

