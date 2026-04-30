<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

/* ======================
   AUTH
====================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) die("DB connection failed");

/* ======================
   FETCH DROPDOWNS
====================== */

// Students
$students = $conn->query("
    SELECT s.student_id, s.student_name, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    ORDER BY s.student_name
")->fetch_all(MYSQLI_ASSOC);

// Subjects
$subjects = $conn->query("
    SELECT subject_name
    FROM subjects
    WHERE status='active'
    ORDER BY subject_name
")->fetch_all(MYSQLI_ASSOC);

// Sessions
$sessions = $conn->query("
    SELECT session_id, session_name
    FROM sessions
    WHERE status='active'
")->fetch_all(MYSQLI_ASSOC);

/* ======================
   ADD / UPDATE
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = $_POST['student_id'];
    $subject    = $_POST['subject']
    $grade      = $_POST['grade'];
    $exam_term  = $_POST['exam_term'];
    $session_id = $_POST['session_id'];
    $edit_id    = $_POST['edit_id'] ?? null;
    $marks = $_POST['marks'] ?? '';

        // Check format: 1 to 3 digits / 1 to 3 digits
        if (!preg_match('/^\d{1,3}\s*\/\s*\d{1,3}$/', $marks)) {
            echo "Invalid format! Use format like 45/50 or 100/100 (max 3 digits)";
            exit;
        }

        // Split values
        list($obtained, $total) = explode('/', $marks);

        $obtained = (int) trim($obtained);
        $total = (int) trim($total);

        // Logical validation
        if ($total == 0) {
            echo "Total marks cannot be zero!";
            exit;
        }

        if ($obtained > $total) {
            echo "Obtained marks cannot be greater than total marks!";
            exit;
        }



    if ($edit_id) {
        $stmt = $conn->prepare("
            UPDATE results
            SET student_id=?, subject=?, marks=?, grade=?, exam_term=?, session_id=?
            WHERE id=?
        ");
        $stmt->bind_param("isissii",
            $student_id, $subject, $marks, $grade, $exam_term, $session_id, $edit_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO results
            (student_id, subject, marks, grade, exam_term, session_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isissi",
            $student_id, $subject, $marks, $grade, $exam_term, $session_id
        );
    }

    $stmt->execute();
    header("Location: manage_results.php");
    exit;
}

/* ======================
   DELETE
====================== */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM results WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_results.php");
    exit;
}

/* ======================
   EDIT FETCH
====================== */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM results WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

/* ======================
   FETCH RESULTS LIST
====================== */
$results = $conn->query("
    SELECT 
        r.id,
        r.marks,
        r.grade,
        r.exam_term,
        r.subject,
        r.created_at,
        s.student_name,
        c.class_name,
        se.session_name
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    LEFT JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN sessions se ON r.session_id = se.session_id
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manage Results — EMIS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
<style>
    .table{width:100%;border-collapse:collapse}
    .table th,.table td{border:1px solid #ccc;padding:8px}
    .form-control{width:100%;padding:8px;margin-bottom:8px}
    .btn-save{background:#4a63e7;color:#fff}
    .btn-del{background:#e74c3c;color:#fff}
    .btn-edit{background:#f39c12;color:#fff}
</style>
</head>

<body>
  <div class="container">
        <?php include '../partials/sidebar.php'; ?>
   <main class="main">
      <div class="header">
        <h3> Manage Student Results</h3>

        <form method="POST">
        <input type="hidden" name="edit_id" value="<?= $edit['id'] ?? '' ?>">

        <select name="student_id" class="form-control" required>
        <option value="">Select Student</option>
        <?php foreach($students as $s): ?>
        <option value="<?= $s['student_id'] ?>"
        <?= ($edit && $edit['student_id']==$s['student_id'])?'selected':'' ?>>
        <?= htmlspecialchars($s['student_name']) ?> (<?= $s['class_name'] ?>)
        </option>
        <?php endforeach; ?>
        </select>

        <select name="subject" class="form-control" required>
        <option value="">Select Subject</option>
        <?php foreach($subjects as $sub): ?>
        <option value="<?= $sub['subject_name'] ?>"
        <?= ($edit && $edit['subject']==$sub['subject_name'])?'selected':'' ?>>
        <?= htmlspecialchars($sub['subject_name']) ?>
        </option>
        <?php endforeach; ?>
        </select>

        <input type="number" name="marks" class="form-control"
        value="<?= $edit['marks'] ?? '' ?>" placeholder="Obtained Marks/ Total Marks" required>

        <input type="text" name="grade" class="form-control"
        value="<?= $edit['grade'] ?? '' ?>" placeholder="Grade (A/B/C)" required>

        <input type="text" name="exam_term" class="form-control"
        value="<?= $edit['exam_term'] ?? 'Final' ?>" placeholder="Exam Term">

        <select name="session_id" class="form-control">
        <option value="">Select Batch</option>
        <?php foreach($sessions as $ses): ?>
        <option value="<?= $ses['session_id'] ?>"
        <?= ($edit && $edit['session_id']==$ses['session_id'])?'selected':'' ?>>
        <?= $ses['session_name'] ?>
        </option>
        <?php endforeach; ?>
        </select>

        <button class="btn btn-save">
        <?= $edit ? 'Update Result' : 'Add Result' ?>
        </button>
        </form>

        <hr>

        <table class="table">
        <tr>
        <th>Student</th>
        <th>Class</th>
        <th>Subject</th>
        <th>Marks</th>
        <th>Grade</th>
        <th>Term</th>
        <th>Batch</th>
        <th>Action</th>
        </tr>

        <?php foreach($results as $r): ?>
        <tr>
        <td><?= htmlspecialchars($r['student_name']) ?></td>
        <td><?= $r['class_name'] ?></td>
        <td><?= htmlspecialchars($r['subject']) ?></td>
        <td><?= $r['marks'] ?></td>
        <td><?= $r['grade'] ?></td>
        <td><?= $r['exam_term'] ?></td>
        <td><?= $r['session_name'] ?></td>
        <td>
        <a class="btn mb-1" href="?edit=<?= $r['id'] ?>">Edit</a>
        <a class="btn" href="?delete=<?= $r['id'] ?>"
        onclick="return confirm('Delete result?')">Delete</a>
        </td>
        </tr>
        <?php endforeach; ?>
        </table>
    </main>
  </div>
</body>
</html>

