<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit;
}


// Get CNIC of logged-in user
$user_cnic = $_SESSION['cnic'];

// Fetch teacher_id from teachers table using CNIC
$stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE cnic = ? LIMIT 1");
$stmt->bind_param("s", $user_cnic);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
    $teacher_id = $teacher['teacher_id'];
} else {
    echo "No teacher found with CNIC: " . htmlspecialchars($user_cnic);
}
/* ============================
    FETCH CLASSES ASSIGNED TO TEACHER
============================= */
$classes = [];
$stmt = $conn->prepare("
    SELECT DISTINCT c.class_id, c.class_name, c.class_short, s.session_name 
    FROM classes c 
    JOIN teacher_classes tc ON c.class_id = tc.class_id 
    Join sessions s on c.session_id= s.session_id
    WHERE tc.teacher_id = ?
    ORDER BY c.class_name
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $classes[] = $row;
$stmt->close();

/* ============================
    SELECTED CLASS
============================= */
$class_id = intval($_GET['class_id'] ?? 0);
$students = [];
$selected_class_name = '';

if ($class_id) {
    foreach ($classes as $c) {
        if ($c['class_id'] == $class_id) {
            $selected_class_name = $c['class_name'] . " (" . $c['class_short'] . ")";
            break;
        }
    }

    /* ============================
        FETCH STUDENTS OF CLASS
    ============================= */
    $stmt = $conn->prepare("
        SELECT *
        FROM students
        WHERE class_id = ? AND status = 'admitted'
        ORDER BY student_name
    ");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $students[] = $row;
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Assigned Students — EMIS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>
<body>
<div class="container">
  <?php include '../partials/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main">

    <div class="header">
      <div style="display:flex;gap:12px;align-items:center">
        <div style="font-size:20px;font-weight:700">Assigned Students</div>
      </div>

      <div style="display:flex;gap:12px;align-items:center">
        <div class="search"><input placeholder="Search Students..." /></div>
        <div style="display:flex;gap:10px;align-items:center">
          <div style="display:flex;flex-direction:column;align-items:flex-end">
            <div style="font-size:13px;font-weight:700"><?= $_SESSION['username'] ?></div>
            <div style="font-size:12px;color:var(--muted)">Teacher</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Class Selector -->
    <div class="card" style="margin-bottom:20px">
      <form method="GET">
        <label style="font-weight:600">Select Class</label>
        <select name="class_id" class="form-select" onchange="this.form.submit()" required>
          <option value="">-- Choose Class --</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['class_id'] ?>" <?= $class_id == $c['class_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['class_name']) ?> (<?= htmlspecialchars($c['class_short']) ?>) - <?= htmlspecialchars($c['session_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <!-- Students Table -->
    <?php if ($class_id): ?>
    <div class="card">
      <div style="font-weight:700">Students — <?= htmlspecialchars($selected_class_name) ?></div>

      <table class="table" style="margin-top:12px">
        <thead>
          <tr>
            <th>Name</th>
            <th>Roll No / CNIC</th>
            <th>Progress</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>

        <?php if (!empty($students)): ?>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['student_name']) ?></td>

              <!-- Roll no replaced with CNIC or student unique ID -->
              <td><?= htmlspecialchars($s['student_cnic']) ?></td>

              <td>Good</td>

              <td><a class="btn ghost" href="view_student.php?id=<?= $s['student_id'] ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" style="text-align:center;color:gray;padding:20px">
              No students found in this class.
            </td>
          </tr>
        <?php endif; ?>

        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </main>

</div>
</body>
</html>
