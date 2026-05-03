<?php
include '../config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['session_name']);
    $starting_date = $_POST['starting_date'];
    $remarks = $_POST['remarks'];

   // 🎯 Only allow format: 2024-2025
if (!preg_match('/^\d{4}-\d{4}$/', $name)) {
    $msg = "<div class='alert alert-danger'>Batch Name must be in format: yyyy-yyyy</div>";
}
    // 📅 Validate date
    elseif (!empty($starting_date)) {
        $dateParts = explode('-', $starting_date);

        if (count($dateParts) !== 3 || !checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
            $msg = "<div class='alert alert-danger'>Invalid date format.</div>";
        }

        // Optional:  prevent past date
    

        else {

            // 🔍 Check duplicate
            $checkStmt = $conn->prepare("SELECT session_id FROM sessions WHERE session_name = ? OR starting_date = ?");
            $checkStmt->bind_param("ss", $name, $starting_date);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $msg = "<div class='alert alert-danger'>Error: This session already exists.</div>";
            } else {

                //  Insert
                $stmt = $conn->prepare("
                    INSERT INTO sessions (session_name, starting_date, remarks) 
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("sss", $name, $starting_date, $remarks);

                if ($stmt->execute()) {
                    $msg = "<div class='alert alert-success'>Session Created Successfully!</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Error: Unable to create session.</div>";
                }
            }
        }

    } else {
        $msg = "<div class='alert alert-warning'>Please fill required fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/admin-routine.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/admin-dashboard.css">
</head>
<div class="container">
    <?php include '../partials/sidebar.php'; ?>
    <main class="main">
        <div class="card-header -white mt-5">
            <h4>Create New Batch</h4>
        </div>
        <div class="card-body">

            <?= $msg ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Batch Name</label>
                    <input type="text" name="session_name" class="form-control" placeholder="e.g., 2024-2025" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Starting Date</label>
                    <input type="date" name="starting_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>

                <button class="btn">Create Batch</button>
            </form>

        </div>
</main>
</div>

</body>
</html>
