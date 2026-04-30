<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php'; 

/* ======================
   AUTH CHECK
====================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit;
}

$user_cnic = $_SESSION['cnic'] ?? null;
if (!$user_cnic) {
    die("Session error: CNIC missing");
}


/* ======================
   FETCH TEACHER
====================== */
$stmt = $conn->prepare("
    SELECT * 
    FROM teachers 
    WHERE cnic = ? 
    LIMIT 1
");
$stmt->bind_param("s", $user_cnic);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    die("Teacher record not found for CNIC: " . htmlspecialchars($user_cnic));
}

$teacher_id = (int)$teacher['teacher_id'];
$teacherName = $teacher['teacher_name'];

// Store for later use (PTM actions etc.)
$_SESSION['teacher_id'] = $teacher_id;

/* ======================
   DATE INFO
====================== */
$today_day  = date('l');           // Monday, Tuesday...
$today_date = date('Y-m-d');

/* ======================
   TODAY'S CLASSES
====================== */
$stmt = $conn->prepare("
    SELECT c.class_name, tc.subject, tc.start_time, tc.end_time
    FROM teacher_classes tc
    JOIN classes c ON tc.class_id = c.class_id
    WHERE tc.teacher_id = ?
      AND tc.day = ?
      AND tc.status = 'Active'
    ORDER BY tc.start_time
");
$stmt->bind_param("is", $teacher_id, $today_day);
$stmt->execute();
$today_classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ======================
   PENDING ATTENDANCE
====================== */
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.class_id) AS pending
    FROM teacher_classes tc
    JOIN attendance a ON tc.class_id = a.class_id
    WHERE tc.teacher_id = ?
      AND a.attendance_date = ?
      AND a.status IS NULL
");
$stmt->bind_param("is", $teacher_id, $today_date);
$stmt->execute();
$pending_attendance = $stmt->get_result()->fetch_assoc()['pending'] ?? 0;
$stmt->close();

/* ======================
   LATEST MATERIALS (last 5)
====================== */
$stmt = $conn->prepare("
    SELECT sm.title, sm.uploaded_at, c.class_name
    FROM study_materials sm
    JOIN classes c ON sm.class_id = c.class_id
    WHERE sm.teacher_id = ?
    ORDER BY sm.uploaded_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ======================
   UPCOMING PTM BOOKINGS
====================== */
$stmt = $conn->prepare("
    SELECT 
        pb.booking_id,
        pb.meeting_date,
        pb.status,
        s.student_name,
        u.username AS parent_name
    FROM ptm_bookings pb
    JOIN students s ON pb.child_id = s.student_id
    JOIN users u ON pb.booked_by = u.user_id
    WHERE pb.teacher_id = ?
      AND pb.meeting_date >= CURDATE()
    ORDER BY pb.meeting_date ASC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$ptm_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ======================
   TEACHER SUBJECTS (distinct)
====================== */
$stmt = $conn->prepare("
    SELECT DISTINCT subject
    FROM teacher_classes
    WHERE teacher_id = ?
      AND status = 'Active'
    ORDER BY subject
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 1.25rem;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .ptm-today {
            background-color: #fffbeb;
        }
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.85rem;
            border-radius: 5px;
            margin-right: 4px;
            min-width:100px;
        }
        .cancel-btn   { background: #ef4444; color: white; }
        .cancel-btn:hover { background: #dc2626; }
        .reschedule-btn { background: #f59e0b; color: black; }
        .reschedule-btn:hover { background: #d97706; }
    </style>
</head>
<body>

<div class="container">
    <div>
        <?php include '../partials/sidebar.php'; ?>
    </div>

    <main class="main">
        <div class="header">
            <h3>Teacher Dashboard</h3>
            <div class="text-end">
                <strong><?= htmlspecialchars($teacherName) ?></strong><br>
                <small class="text-muted">Teacher</small>
            </div>
        </div>

        <div class="grid">
            <!-- Today's Classes -->
            <div class="card">
                <h6>Today's Classes</h6>
                <?php if ($today_classes): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($today_classes as $c): ?>
                            <li class="mb-1">
                                <strong><?= date('H:i', strtotime($c['start_time'])) ?> – <?= date('H:i', strtotime($c['end_time'])) ?></strong><br>
                                <?= htmlspecialchars($c['class_name']) ?> • <?= htmlspecialchars($c['subject']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No classes scheduled today</p>
                <?php endif; ?>
            </div>

            <!-- Pending Attendance -->
            <div class="card text-center">
                <h6>Pending Attendance</h6>
                <h4 class="mb-0"><?= $pending_attendance ?></h4>
                <small class="text-muted">class<?= $pending_attendance !== 1 ? 'es' : '' ?></small>
            </div>

            <!-- Recent Materials Count -->
            <div class="card text-center">
                <h6>Recent Materials</h6>
                <h4 class="mb-0"><?= count($materials) ?></h4>
                <small class="text-muted">last 5 uploads</small>
            </div>

            <!-- Subjects -->
            <div class="card">
                <h6>My Subjects</h6>
                <?php if ($subjects): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($subjects as $s): ?>
                            <li><?= htmlspecialchars($s['subject']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No subjects assigned</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Materials Table -->
        <div class="card mb-4">
            <h6>Recent Study Materials</h6>
            <table class="table text-center">
                <thead class>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($materials): ?>
                        <?php foreach ($materials as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['title']) ?></td>
                                <td><?= htmlspecialchars($m['class_name']) ?></td>
                                <td><?= date('d M Y', strtotime($m['uploaded_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No materials uploaded yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Upcoming PTM Bookings -->
        <div class="card">
            <h6>Upcoming PTM Bookings</h6>
            <?php if ($ptm_bookings): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Parent</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ptm_bookings as $ptm):
                            $isToday = ($ptm['meeting_date'] === $today_date);
                        ?>
                            <tr class="<?= $isToday ? 'ptm-today' : '' ?>">
                                <td><?= htmlspecialchars($ptm['student_name']) ?></td>
                                <td><?= htmlspecialchars($ptm['parent_name']) ?></td>
                                <td><?= date('d M Y', strtotime($ptm['meeting_date'])) ?></td>
                                <td id="status-<?= $ptm['booking_id'] ?>">
                                    <?= htmlspecialchars($ptm['status'] ?: '—') ?>
                                </td>
                                <td class="action-cell">
                                    <?php if ($ptm['status'] === 'Pending' || $ptm['status'] === 'Confirmed'): ?>
                                        <button class="btn-sm confirm-btn"   data-id="<?= $ptm['booking_id'] ?>">Confirm</button>
                                        <button class="btn-sm cancel-btn"     data-id="<?= $ptm['booking_id'] ?>">Cancel</button>
                                        <button class="btn-sm reschedule-btn" data-id="<?= $ptm['booking_id'] ?>">Reschedule</button>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($ptm['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No upcoming parent-teacher meetings scheduled.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Status update helper
function updateStatus(id, newStatus) {
    const el = document.getElementById('status-' + id);
    if (el) el.textContent = newStatus;
}

document.querySelectorAll('.confirm-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        try {
            const res = await fetch('ptm_confirm.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ptm_id: id })
            });
            const data = await res.json();
            
            if (data.success) {
                updateStatus(id, 'Confirmed');
                btn.remove();
            } else {
                alert(data.message || 'Something went wrong');
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    });
});

document.querySelectorAll('.reschedule-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        const newDate = prompt('Enter new meeting date (YYYY-MM-DD):');
        
        if (!newDate || !/^\d{4}-\d{2}-\d{2}$/.test(newDate)) {
            alert('Please enter valid date (YYYY-MM-DD)');
            return;
        }

        try {
            const res = await fetch('ptm_reschedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ptm_id: id, new_date: newDate })
            });

            if (!res.ok) { // Check if the network response is 'ok' (status in the range 200-299)
                throw new Error(`HTTP error! status: ${res.status}`);
            }

            const data = await res.json();
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        } catch (err) { // The error object is named 'err'
            console.error('Fetch error:', err); 

            alert(`Network error while rescheduling, ${err.message}`); 
        }
    });
});
</script>
<script>
function updateStatus(id, newStatus) {
    const el = document.getElementById('status-' + id);
    if (el) el.textContent = newStatus;
}

document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to cancel this PTM?')) return;

        const id = btn.dataset.id;

        try {
            const res = await fetch('ptm_cancel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ptm_id: id })
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = data.redirect || 'set_meeting_availability.php';
            } else {
                alert(data.message || 'Failed to cancel PTM');
            }
        } catch (err) {
            alert('Network error while cancelling PTM');
            console.error(err);
        }
    });
});
</script>

</body>
</html>

<?php
mysqli_close($conn);
?>
