<?php
session_start();
include '../config/db.php';

/* --------------------
   AUTH CHECK
---------------------*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}


/* --------------------
   CSRF TOKEN
---------------------*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$msg = "";

/* --------------------
   CREATE CLASS
---------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'create'
    && $_POST['csrf'] === $csrf
) {
   $class_name  = trim($_POST['class_name']);
    $class_short = trim($_POST['class_short']);
    
    $session_id  = (int)$_POST['session_id'];

    //  CLASS NAME VALIDATION
    // 2-3 alphabets + 1-2 numbers (order flexible: e.g. BS1, 1BS, BS12)
    if (!preg_match('/^(?=(?:.*[A-Za-z]){2,3}$)(?=(?:.*\d){1,2}$)[A-Za-z\d]+$/', $class_name)) {
        $msg = "<div class='alert alert-danger'>
        Class Name must contain 2-3 letters and 1-2 numbers only (e.g. BS1, CS12, 2IT).
        </div>";
    }

    //  CLASS SHORT VALIDATION
    // Only alphabets (Roman), no spaces, no numbers
    elseif (!preg_match('/^[A-Za-z]+$/', $class_short)) {
        $msg = "<div class='alert alert-danger'>
        Class Short must contain only letters (no numbers or spaces).
        </div>";
    }
    // 🔍 Check duplicate
    $check = $conn->prepare("SELECT class_id FROM classes WHERE class_name=? AND session_id=? AND class_status != 'deleted'");
    $check->bind_param("si", $class_name, $session_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>This class already exists in this session.</div>";
        $conn->query("DELETE FROM classes WHERE session_id IS NULL;
        ");
        
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO classes (class_name, class_short, session_id) VALUES (?,?,?)"
        );
        $stmt->bind_param("ssi", $class_name, $class_short, $session_id);
        $stmt->execute();
        $stmt->close();
        $conn->query("DELETE FROM classes WHERE session_id IS NULL;
        "); // Cleanup any orphaned classes without session
        $msg = "<div class='alert alert-success'>Class created successfully.</div>";
    }

    $check->close();
}

/* --------------------
   UPDATE CLASS
---------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'update'
    && $_POST['csrf'] === $csrf
) {
    $class_id    = (int)$_POST['class_id'];
    $class_name  = trim($_POST['class_name']);
    $class_short = trim($_POST['class_short']);
    $session_id  = (int)$_POST['session_id'];
    $status      = $_POST['class_status'];

    // 🔍 Check duplicate (exclude current id)
    $check = $conn->prepare("SELECT class_id FROM classes 
        WHERE class_name=? AND session_id=? AND class_id != ? AND class_status != 'deleted'");
    
    $check->bind_param("sii", $class_name, $session_id, $class_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>This class already exists in this session.</div>";

    } else {
        $stmt = $conn->prepare(
            "UPDATE classes 
             SET class_name=?, class_short=?, session_id=?, class_status=? 
             WHERE class_id=?"
        );

        $stmt->bind_param(
            "ssisi",
            $class_name,
            $class_short,
            $session_id,
            $status,
            $class_id
        );

        $stmt->execute();
        $stmt->close();

        $msg = "<div class='alert alert-success'>Class updated successfully.</div>";
    }

    $check->close();
}
/* --------------------
   DELETE CLASS (SOFT)
---------------------*/
if (isset($_GET['delete'], $_GET['csrf']) && $_GET['csrf'] === $csrf) {
    $id = (int)$_GET['delete'];
    $conn->query("UPDATE classes SET class_status='deleted' WHERE class_id=$id");
    $msg = "<div class='alert alert-info'>Class deleted.</div>";
}
if (!empty($msg)) {
    $_SESSION['msg'] = $msg;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
/* --------------------
   FETCH DATA
---------------------*/
$classes = $conn->query("
    SELECT c.*, s.session_name, s.status AS session_status
    FROM classes c
    LEFT JOIN sessions s ON c.session_id = s.session_id
    WHERE c.class_status != 'deleted'
    ORDER BY c.class_id DESC
");

$sessions = $conn->query("SELECT session_id, session_name FROM sessions WHERE status='active'");

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// Get total count
$total_query = "SELECT COUNT(*) as total FROM classes c
                LEFT JOIN sessions s ON c.session_id = s.session_id
                WHERE c.class_status != 'deleted'";
$total_result = $conn->query($total_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated results
$classes_paginated = $conn->query("
    SELECT c.*, s.session_name, s.status AS session_status
    FROM classes c
    LEFT JOIN sessions s ON c.session_id = s.session_id
    WHERE c.class_status != 'deleted'
    ORDER BY c.class_id DESC
    LIMIT $limit OFFSET $offset
");
?>

<!doctype html>
<html lang="en">
    <head>
    <meta charset="utf-8">
        <title>Manage Classes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<body>

<div class="container">
    <?php include '../partials/sidebar.php'; ?>


    <!-- MAIN CONTENT -->
    <main class="main">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Classes</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClass">
                        + New Class
                    </button>
                </div>

               <?php 
                if (!empty($_SESSION['msg'])) {
                    echo $_SESSION['msg'];
                    unset($_SESSION['msg']);
                }
                ?>


                <div class="card shadow-sm">
                    <div class="card-body table-responsive">

                        <table class="table table-bordered align-middle">
                            <thead class="table-dark text-dark">
                                <tr class=""text-dark>
                                    <th>#</th>
                                    <th>Class Name</th>
                                    <th>Short</th>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th width="220">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $i=1; while($row=$classes_paginated->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                                    <td><?= htmlspecialchars($row['class_short']) ?></td>
                                    <td><?= htmlspecialchars($row['session_name'] ?? '—') ?></td>

                                <td>
                                <span class="badge bg-<?= $row['class_status']=='active'?'success':'secondary' ?>">
                                <?= ucfirst($row['class_status']) ?>
                                </span>
                                </td>

                                <td>

                                <?php if ($row['session_status'] === 'completed'): ?>
                                    <span class="badge bg-secondary">Batch Completed</span>
                                <?php else: ?>
                                <button class="btn btn-sm btn-info"
                                onclick="editClass(
                                <?= $row['class_id'] ?>,
                                '<?= htmlspecialchars($row['class_name'],ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($row['class_short'],ENT_QUOTES) ?>',
                                <?= (int)$row['session_id'] ?>,
                                '<?= $row['class_status'] ?>'
                                )">
                                Edit
                                </button>
                                <?php endif; ?>

                                <a href="?delete=<?= $row['class_id'] ?>&csrf=<?= $csrf ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this class?')">
                                Delete
                                </a>

                                </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Class pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- Previous button -->
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>">
                                        Previous
                                    </a>
                                </li>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $total_pages ?>">
                                        <?= $total_pages ?>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <!-- Next button -->
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>">
                                        Next
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    </div>
                </div>

            </main>
        </div>

        <!-- ADD CLASS MODAL -->
        <div class="modal fade" id="addClass" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">

                    <div class="modal-header">
                        <h5>Add Class</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label>Class Name</label>
                        <input name="class_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Short Name</label>
                        <input name="class_short" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Batch</label>
                        <select name="session_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php while($s=$sessions->fetch_assoc()): ?>
                            <option value="<?= $s['session_id'] ?>"><?= $s['session_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    </div>

                    <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success">Save</button>
                    </div>

                </form>
            </div>
        </div>

        <!-- EDIT CLASS MODAL -->
        <div class="modal fade" id="editClass" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Edit Class</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="class_id" id="eid">

                <div class="mb-3">
                    <label>Class Name</label>
                    <input name="class_name" id="ename" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Short Name</label>
                    <input name="class_short" id="eshort" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Session</label>
                    <select name="session_id" id="esession" class="form-select" required>
                        <?php
                        $sessions2 = $conn->query("SELECT session_id, session_name FROM sessions WHERE status='active'");
                        while($s=$sessions2->fetch_assoc()):
                        ?>
                            <option value="<?= $s['session_id'] ?>"><?= $s['session_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Class Status</label>
                        <select name="class_status" id="estatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success">Update</button>
                    </div>

                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            function editClass(id,name,short,session,status){
            document.getElementById('eid').value = id;
            document.getElementById('ename').value = name;
            document.getElementById('eshort').value = short;
            document.getElementById('esession').value = session;
            document.getElementById('estatus').value = status;
            new bootstrap.Modal(document.getElementById('editClass')).show();
            }
        </script>

    </body>
</html>
