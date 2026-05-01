<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config/db.php';

/* ======================
   AUTH CHECK
====================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) die("Database connection failed");

/* ======================
   SEARCH
====================== */
$search = $_GET['search'] ?? '';
$search_sql = "";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_sql = "WHERE s.student_name LIKE '%$search%'";
}

/* ======================
   PAGINATION
====================== */
$limit = 3;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

/* ======================
   TOTAL COUNT
====================== */
$total_query = $conn->query("
    SELECT COUNT(*) as total
    FROM results r
    LEFT JOIN students s ON r.student_id = s.student_id
    $search_sql
");
$total = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

/* ======================
   FETCH RESULTS
====================== */
$results = $conn->query("
    SELECT 
        r.student_id,
        s.student_name,
        r.subject,
        r.marks,
        r.grade,
        r.exam_term,
        se.session_name,
        r.created_at
    FROM results r
    LEFT JOIN students s ON r.student_id = s.student_id
    LEFT JOIN sessions se ON r.session_id = se.session_id
    $search_sql
    ORDER BY s.student_name, se.session_name, r.exam_term, r.subject
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);

/* ======================
   GROUPING
====================== */
$grouped = [];

foreach ($results as $r) {
    $key = $r['student_name'] . '|' . $r['session_name'] . '|' . $r['exam_term'];

    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'student_name' => $r['student_name'],
            'session_name' => $r['session_name'],
            'exam_term' => $r['exam_term'],
            'subjects' => []
        ];
    }

    $grouped[$key]['subjects'][] = [
        'subject' => $r['subject'],
        'marks' => $r['marks'],
        'grade' => $r['grade']
    ];
}

/* ======================
   CSV EXPORT (FULL DATA)
====================== */
if (isset($_POST['export_csv'])) {

    $export = $conn->query("
        SELECT 
            s.student_name,
            r.subject,
            r.marks,
            r.grade,
            r.exam_term,
            se.session_name,
            r.created_at
        FROM results r
        LEFT JOIN students s ON r.student_id = s.student_id
        LEFT JOIN sessions se ON r.session_id = se.session_id
        $search_sql
        ORDER BY s.student_name
    ");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report_card.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student', 'Subject', 'Marks', 'Grade', 'Exam Term', 'Session', 'Date']);

    while ($r = $export->fetch_assoc()) {
        fputcsv($output, $r);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="../assets/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/admin-dashboard.css">
<title>Results</title>
</head>

<body class="">
<div class="container">
    
    <?php include '../partials/sidebar.php'; ?>

    <main class="main">
<h2> Student Results</h2>

<!--  SEARCH -->
<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" 
           placeholder="Search by student name" value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-primary">Search</button>
</form>

<!--  EXPORT -->
<form method="post" class="mb-3">
    <button type="submit" name="export_csv" class="btn btn-success">
        Export CSV
    </button>
</form>

<!--  RESULTS -->
<?php foreach ($grouped as $g): ?>
<div class="card mb-3">
    <div class="card-header">
        <b><?= htmlspecialchars($g['student_name']) ?></b> |
        <?= htmlspecialchars($g['session_name']) ?> |
        <?= htmlspecialchars($g['exam_term']) ?>
    </div>

    <table class="table mb-0">
        <tr>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
        </tr>

        <?php foreach ($g['subjects'] as $sub): ?>
        <tr>
            <td><?= htmlspecialchars($sub['subject']) ?></td>
            <td><?= htmlspecialchars($sub['marks']) ?></td>
            <td><?= htmlspecialchars($sub['grade']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endforeach; ?>

<!--  PAGINATION -->
<nav>
<ul class="pagination">

<?php for ($i = 1; $i <= $total_pages; $i++): ?>
<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
        <?= $i ?>
    </a>
</li>
<?php endfor; ?>

</ul>
</nav>

    </main>
</div>
</body>
</html>