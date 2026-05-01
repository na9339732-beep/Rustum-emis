<?php

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';
// Security: Must be logged in as Teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher' || $_SESSION['job_status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}


if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['ptm_id']) || !isset($input['new_date'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$ptm_id    = (int)$input['ptm_id'];
$new_date  = trim($input['new_date']);
$teacher_id = (int)$_SESSION['teacher_id'];

// Basic date validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date) || !strtotime($new_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

// Optional: Prevent scheduling in the past
if ($new_date < date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Cannot schedule PTM in the past']);
    exit;
}

// Verify this booking belongs to the logged-in teacher
$stmt = $conn->prepare("
    SELECT booking_id, status 
    FROM ptm_bookings 
    WHERE booking_id = ? 
      AND teacher_id = ?
");
$stmt->bind_param("ii", $ptm_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or not yours']);
    exit;
}

// Optional: Only allow rescheduling if status is Pending or Confirmed
if (!in_array($booking['status'], ['Pending', 'Confirmed'])) {
    echo json_encode(['success' => false, 'message' => 'Cannot reschedule a ' . $booking['status'] . ' meeting']);
    exit;
}

// Update the meeting date
$stmt = $conn->prepare("
    UPDATE ptm_bookings 
    SET meeting_date = ?,
        status = 'Pending'  -- Rescheduling resets to Pending (or keep 'Confirmed' if you prefer)
    WHERE booking_id = ?
");
$stmt->bind_param("si", $new_date, $ptm_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => "PTM rescheduled to $new_date successfully!"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
}

$stmt->close();
mysqli_close($conn);
?>
