<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

include '../config/db.php';

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

/*  Security: Teacher must be logged in */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/*  Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

$booking_id = $data['ptm_id'] ?? null;
$teacher_id = $_SESSION['teacher_id'] ?? null;

if (!$booking_id || !$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

/*  Update PTM status */
$stmt = $conn->prepare("
    UPDATE ptm_bookings
    SET status = 'Confirmed'
    WHERE booking_id = ?
      AND teacher_id = ?
");

$stmt->bind_param("ii", $booking_id, $teacher_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'PTM not found or already confirmed'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}

$stmt->close();
$conn->close();

