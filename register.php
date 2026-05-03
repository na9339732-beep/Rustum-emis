<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

include 'config/db.php';
include './email_functions.php'; // <-- for sending emails

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed.");
}

$successMsg = $errorMsg = "";
$step = 1;
$allowedRoles = ['Student', 'Parents'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================
       STEP 1 — REGISTER USER
    ==========================*/
    if (isset($_POST['step1'])) {

        $role        = $_POST['role'] ?? '';
        $fullname    = trim($_POST['fullname'] ?? '');
        $email       = strtolower(trim($_POST['email'] ?? ''));
        $password    = $_POST['password'] ?? '';
        $parent_cnic = trim($_POST['parent_cnic'] ?? '');

        if (!in_array($role, $allowedRoles)) {
            $errorMsg = "Invalid role selected.";
        } elseif (!$fullname || !$email || !$password) {
            $errorMsg = "All fields are required.";
        } elseif (!preg_match("/^[a-zA-Z ]+$/", $fullname)) {
            $errorMsg = "Only alphabets allowed in name.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Invalid email address.";
        } elseif (strlen($password) < 8) {
            $errorMsg = "Password must be at least 8 characters.";
        } elseif ($role === 'Parents' && !$parent_cnic) {
            $errorMsg = "Parent CNIC is required.";
        } else {

            $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errorMsg = "Email already registered. <a href='login.php'>Login</a>";
            } else {

                $hash  = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(16)); // verification token

                /* ---------- PARENT ---------- */
                if ($role === 'Parents') {

                    $c = $conn->prepare("SELECT student_id FROM students WHERE father_cnic=?");
                    $c->bind_param("s", $parent_cnic);
                    $c->execute();
                    $c->store_result();

                    if ($c->num_rows == 0) {
                        $errorMsg = "No student found with this CNIC.";
                    } else {
                        if($role === 'Parents'){
                        $ins = $conn->prepare("
                            INSERT INTO users (username,email,password,role,cnic,status,email_verified,verification_token)
                            VALUES (?,?,?,?,?,'inactive',0,?)
                        ");}
                        else {
                            $ins = $conn->prepare("
                            INSERT INTO users (username,email,password,role,status,email_verified,verification_token)
                            VALUES (?,?,?,?,?,'active',0,?)");
                        }
                        $ins->bind_param("ssssss", $fullname, $email, $hash, $role, $parent_cnic, $token);

                        if ($ins->execute()) {

                            // Send verification email
                            $verifyLink = "http://localhost/finalemis/verify.php?token=$token";
                            $message = "Click the link to verify your email: $verifyLink";
                            sendEmail($email, "Verify Your Email", $message);

                            $successMsg = "Parent registered successfully! Check your email to verify account.";
                            header("refresh:5;url=login.php");

                        } else {
                            $errorMsg = "Registration failed.";
                        }
                        $ins->close();
                    }
                    $c->close();

                /* ---------- STUDENT ---------- */
                } else {

                    $ins = $conn->prepare("
                        INSERT INTO users (username,email,password,role,cnic,status,email_verified,verification_token)
                        VALUES (?,?,?,?,?,'inactive',0,?)
                    ");
                    $ins->bind_param("ssssss", $fullname, $email, $hash, $role, $parent_cnic, $token);

                    if ($ins->execute()) {
                        $_SESSION['temp_user_id'] = $conn->insert_id;
                        $_SESSION['temp_email']   = $email;
                        $_SESSION['temp_name']    = $fullname;
                        $_SESSION['temp_token']   = $token;
                        $step = 2;
                        $successMsg = "Account created! Complete student profile.";

                        // Send verification email
                        $verifyLink = "http://localhost/finalemis/verify.php?token=$token";
                        $message = "Click the link to verify your email: $verifyLink";
                        sendEmail($email, "Verify Your Email", $message);
                    $conn->query("update users set status='active' where user_id=" . $conn->insert_id);
                    }  else {
                        $errorMsg = "Registration failed.";
                    }
                    $ins->close();
                }
            }
            $check->close();
        }
    }

    /* =========================
       STEP 2 — STUDENT PROFILE
    ==========================*/
    elseif (isset($_POST['step2'])) {

        $user_id   = $_SESSION['temp_user_id'] ?? 0;
        $email     = $_SESSION['temp_email'] ?? '';
        $name      = $_SESSION['temp_name'] ?? '';

        if ($user_id <= 0) {
            $errorMsg = "Session expired.";
        } else {

            $father_name  = trim($_POST['father_name']);
            $phone        = trim($_POST['phone']);
            $gender       = $_POST['gender'];
            $dob          = $_POST['dob'];
            $class_id     = (int)$_POST['class_id'];
            $address      = trim($_POST['address'] ?? '');
            $city         = trim($_POST['city'] ?? '');
            $student_cnic = trim($_POST['student_cnic'] ?? '');
            $father_cnic  = trim($_POST['father_cnic'] ?? '');
            $group_id      = $_POST['group_id'];

            if (!$father_name || !$phone || !$gender || !$dob || !$class_id) {
                $errorMsg = "Please fill all required fields.";
            } elseif (!preg_match("/^[a-zA-Z ]+$/", $father_name)) {
                 $errorMsg = "Only alphabets allowed in name.";
             } elseif (!preg_match("/^[0-9]{11}$/", $phone)) {
                $errorMsg = "Phone must be 11 digits.";
            } elseif (!preg_match("/^[0-9]{13}$/", $student_cnic)) {
                $errorMsg = "Student CNIC must be 13 digits.";
            } elseif (!preg_match("/^[0-9]{13}$/", $father_cnic)) {
                $errorMsg = "Father CNIC must be 13 digits.";
            } else {

                $s = $conn->query("SELECT session_id FROM sessions WHERE status='active' LIMIT 1");
                $session_id = $s ? $s->fetch_assoc()['session_id'] : 0;

                $stmt = $conn->prepare("
                    INSERT INTO students
                    (student_name,father_name,email,phone,address,gender,dob,class_id,city,
                     student_cnic,father_cnic,status,user_id,session_id,group_id)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,'registered',?,?,?)
                ");

                $stmt->bind_param(
                    "sssssssiissiii",
                    $name,$father_name,$email,$phone,$address,$gender,$dob,
                    $class_id,$city,$student_cnic,$father_cnic,$user_id,$session_id,$group_id
                );

                if ($stmt->execute()) {
                    // Update users table safely
                    $update = $conn->prepare("UPDATE users SET cnic=? WHERE user_id=?");
                    $update->bind_param("si", $student_cnic, $user_id);
                    $update->execute();
                    $update->close();

                    session_unset();
                    $successMsg = "Student registration completed! Verify your email to login.";
                    header("refresh:5;url=login.php");
                } else {
                    $errorMsg = "Failed to save student profile.";
                }
                $stmt->close();
            }
        }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/auth.css">
<title>Register | EMIS</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
        <div class="text-center mt-5"><h2>Create Account</h2></div>
        
            <?php if($successMsg): ?><div class="alert-success"><?= $successMsg ?></div><?php endif; ?>
            <?php if($errorMsg): ?><div class="alert-error"><?= $errorMsg ?></div><?php endif; ?>
                
            <div class="form-body">
            <?php if($step==1): ?>
            <form method="post">
                <input type="hidden" name="step1">
                <div class="mb-3">
                <label class="form-label">Register As</label><br>
                <select class="form-select" name="role" onchange="toggleCNIC(this.value)" required>
                        <option value="">Select</option>
                        <option value="Student">Student</option>
                        <option value="Parents">Parents</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Full Name</label>
                    <input class="rounded" type="text" name="fullname" required><br>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input class="rounded" type="email" name="email" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input class="rounded" type="password" name="password" required>
                </div>
                <div class="input-group">
                    <div id="pcnic" style="display:none; min-width:100%">
                        <label>Parent CNIC</label>
                        <input class="rounded" type="text" name="parent_cnic" placeholder="Required for Parents">
                    </div>
                 </div>
                <button class="btn-primary">Continue</button>
                Already have an account? <a href="login.php">Login</a>
                </form>

                <?php endif; ?>

                <?php if($step==2): ?>
                <form method="post" class="mt-4">  
                
                    <input type="hidden" name="step2">
                 </div>
                <div class="input-group">
                    <div class="col-md-5">
                        <label>Father Name</label>
                        <input class="rounded" type="text" name="father_name" required>
                    </div>
                    <div class="col-md-5 ms-3">
                        <label>Phone</label>
                    <input class="rounded" type="text" name="phone" required>
                    </div>
                 </div>
                <div class="input-group">
                    <div class="col-md-5">
                        <label>City</label>
                        <input class="rounded" type="text" name="city">
                    </div>
                    <div class="col-md-5 ms-3">
                        <label>Date of Birth</label>
                        <input class="rounded" type="date" name="dob" required>
                    </div>
                </div>
                <div class="input-group">
                    <div class="col-md-5">
                        <label>Student CNIC</label>
                            <input class="rounded" type="text" name="student_cnic">
                    </div>
                    <div class="col-md-5 ms-3">
                        <label>Father CNIC</label>
                        <input class="rounded" type="text" name="father_cnic">
                    </div>
                </div>
                <div class="mb-3 text-start">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Other</option>
                        </select>
                        <label>Class</label>
                        <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        $q = $conn->query("SELECT class_id,class_name FROM classes WHERE class_status='active'");
                        while($c=$q->fetch_assoc()):
                        ?>
                        <option value="<?= $c['class_id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endwhile; ?>
                        </select>
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <input class="rounded" type="text" name="address">
                    
                </div>
                <div class="mb-3 text-start">
                    <label>Group</label>
                    <select name="group_id">
                        <option value="">Select Group</option>
                        <?php
                        $q = $conn->query("SELECT group_id,group_name FROM student_groups WHERE status='Active'");
                        while($c=$q->fetch_assoc()):
                        ?>
                        <option value="<?= $c['group_id'] ?>"><?= htmlspecialchars($c['group_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                    <button style="width:100%" class="btn-primary">Complete Registration</button>
            </form>
            <?php endif; ?>
                </div>
        </div>
    </div>

    <script>
    function toggleCNIC(v){
        document.getElementById('pcnic').style.display = v==='Parents'?'block':'none';
    }
    </script>
</body>
</html>