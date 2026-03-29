<?php
<<<<<<< HEAD
=======
function clean($v){
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function validCNIC($cnic){
    return preg_match('/^[0-9]{13}$/', $cnic);
}

function validPhone($phone){
    return preg_match('/^03[0-9]{9}$/', $phone);
}

>>>>>>> 874cf89 (Updated project)
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

include 'config/db.php';
<<<<<<< HEAD
include './email_functions.php'; // <-- for sending emails
=======
include './email_functions.php'; 
>>>>>>> 874cf89 (Updated project)

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

<<<<<<< HEAD
        $role        = $_POST['role'] ?? '';
        $fullname    = trim($_POST['fullname'] ?? '');
        $email       = strtolower(trim($_POST['email'] ?? ''));
        $password    = $_POST['password'] ?? '';
        $parent_cnic = trim($_POST['parent_cnic'] ?? '');

=======
        $role        = clean($_POST['role'] ?? '');
        $fullname    = clean($_POST['fullname'] ?? '');
        $email       = strtolower(clean($_POST['email'] ?? ''));
        $password    = $_POST['password'] ?? '';
        $parent_cnic = clean($_POST['parent_cnic'] ?? '');
        

        
>>>>>>> 874cf89 (Updated project)
        if (!in_array($role, $allowedRoles)) {
            $errorMsg = "Invalid role selected.";
        } elseif (!$fullname || !$email || !$password) {
            $errorMsg = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Invalid email address.";
        } elseif (strlen($password) < 8) {
            $errorMsg = "Password must be at least 8 characters.";
        } elseif ($role === 'Parents' && !$parent_cnic) {
            $errorMsg = "Parent CNIC is required.";
<<<<<<< HEAD
=======
        } elseif (!preg_match('/^[a-zA-Z ]{3,50}$/', $fullname)) {
            $errorMsg = "Invalid name format.";
        } elseif ($role === 'Parents' && !validCNIC($parent_cnic)) {
            $errorMsg = "Invalid CNIC format (13 digits required).";
>>>>>>> 874cf89 (Updated project)
        } else {

            $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errorMsg = "Email already registered. <a href='login.php'>Login</a>";
            } else {

                $hash  = password_hash($password, PASSWORD_DEFAULT);
<<<<<<< HEAD
                $token = bin2hex(random_bytes(16)); // verification token
=======
                $token = bin2hex(random_bytes(16));
>>>>>>> 874cf89 (Updated project)

                /* ---------- PARENT ---------- */
                if ($role === 'Parents') {

                    $c = $conn->prepare("SELECT student_id FROM students WHERE father_cnic=?");
                    $c->bind_param("s", $parent_cnic);
                    $c->execute();
                    $c->store_result();

                    if ($c->num_rows == 0) {
                        $errorMsg = "No student found with this CNIC.";
                    } else {

                        $ins = $conn->prepare("
                            INSERT INTO users (username,email,password,role,cnic,status,email_verified,verification_token)
                            VALUES (?,?,?,?,?,'inactive',0,?)
                        ");
                        $ins->bind_param("ssssss", $fullname, $email, $hash, $role, $parent_cnic, $token);

                        if ($ins->execute()) {

<<<<<<< HEAD
                            // Send verification email
=======
>>>>>>> 874cf89 (Updated project)
                            $verifyLink = "http://localhost/finalEmis/verify.php?token=$token";
                            $message = "Click the link to verify your email: $verifyLink";
                            sendEmail($email, "Verify Your Email", $message);

<<<<<<< HEAD
                            $successMsg = "Parent registered successfully! Check your email to verify account.";
=======
                            $successMsg = "Parent registered successfully! Check your email to verify account. Refreshing in 5 seconds";
>>>>>>> 874cf89 (Updated project)
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
<<<<<<< HEAD
                        $verifyLink = "http://localhost/finalEmis//verify.php?token=$token";
=======
                        $verifyLink = "http://localhost/finalEmis/verify.php?token=$token";
>>>>>>> 874cf89 (Updated project)
                        $message = "Click the link to verify your email: $verifyLink";
                        sendEmail($email, "Verify Your Email", $message);

                    } else {
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

<<<<<<< HEAD
            $father_name  = trim($_POST['father_name']);
            $phone        = trim($_POST['phone']);
            $gender       = $_POST['gender'];
            $dob          = $_POST['dob'];
            $class_id     = (int)$_POST['class_id'];
            $address      = trim($_POST['address'] ?? '');
            $city         = trim($_POST['city'] ?? '');
            $student_cnic = trim($_POST['student_cnic'] ?? '');
            $father_cnic  = trim($_POST['father_cnic'] ?? '');

            if (!$father_name || !$phone || !$gender || !$dob || !$class_id) {
                $errorMsg = "Please fill all required fields.";
            } else {
=======
           $father_name  = clean($_POST['father_name']);
            $phone        = clean($_POST['phone']);
            $gender       = $_POST['gender'];
            $dob          = $_POST['dob'];
            $class_id     = (int)$_POST['class_id'];
            $address      = clean($_POST['address'] ?? '');
            $city         = clean($_POST['city'] ?? '');
            $student_cnic = clean($_POST['student_cnic'] ?? '');
            $father_cnic  = clean($_POST['father_cnic'] ?? '');


            if (!$father_name || !$phone || !$gender || !$dob || !$class_id) {
                $errorMsg = "Please fill all required fields.";
                }
                elseif (!validPhone($phone)) {
                    $errorMsg = "Invalid phone number.";
                }
                elseif ($student_cnic && !validCNIC($student_cnic)) {
                    $errorMsg = "Invalid student CNIC.";
                }
                elseif ($father_cnic && !validCNIC($father_cnic)) {
                    $errorMsg = "Invalid father CNIC.";
                }
                elseif (!in_array($gender, ['Male','Female','Other'])) {
                    $errorMsg = "Invalid gender selection.";
                }
                elseif (strtotime($dob) >= time()) {
                    $errorMsg = "Invalid date of birth.";
                }
                 else {
>>>>>>> 874cf89 (Updated project)

                $s = $conn->query("SELECT session_id FROM sessions WHERE status='active' LIMIT 1");
                $session_id = $s ? $s->fetch_assoc()['session_id'] : 0;

                $stmt = $conn->prepare("
                    INSERT INTO students
                    (student_name,father_name,email,phone,address,gender,dob,class_id,city,
                     student_cnic,father_cnic,status,user_id,session_id)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,'registered',?,?)
                ");

                $stmt->bind_param(
                    "sssssssiissii",
                    $name,$father_name,$email,$phone,$address,$gender,$dob,
                    $class_id,$city,$student_cnic,$father_cnic,$user_id,$session_id
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
<title>Register | EMIS</title>
<<<<<<< HEAD

<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Inter,sans-serif;background:#f4f6fb;overflow-x:hidden}
.container{max-width:520px;width:100%;background:#fff;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.15)}
.header{padding:30px;text-align:center;background:linear-gradient(135deg,#4a63e7,#2a4bb8);color:#fff}
.form-body{padding:30px}
input,select,button{width:100%;padding:12px;margin-bottom:15px;border-radius:10px;border:1px solid #ccc}
button{background:#4a63e7;color:#fff;font-weight:600;border:none}
.alert-success{background:#e7fff5;padding:12px;border-left:4px solid #00c896}
.alert-error{background:#ffeaea;padding:12px;border-left:4px solid #ff4d4d}
label{font-weight:600}
</style>
</head>

<body>
<div class="container">
<div class="header"><h2>Create Account</h2></div>
<div class="form-body">

<?php if($successMsg): ?><div class="alert-success"><?= $successMsg ?></div><?php endif; ?>
<?php if($errorMsg): ?><div class="alert-error"><?= $errorMsg ?></div><?php endif; ?>

<?php if($step==1): ?>
<form method="post">
<input type="hidden" name="step1">

<label>Register As</label>
<select name="role" onchange="toggleCNIC(this.value)" required>
<option value="">Select</option>
<option value="Student">Student</option>
<option value="Parents">Parents</option>
</select>

<label>Full Name</label>
<input type="text" name="fullname" required>

<label>Email</label>
<input type="email" name="email" required>

<label>Password</label>
<input type="password" name="password" required>

<div id="pcnic" style="display:none">
<label>Parent CNIC</label>
<input type="text" name="parent_cnic">
</div>

<button>Continue</button>
</form>
<?php endif; ?>

<?php if($step==2): ?>
<form method="post">
<input type="hidden" name="step2">

<label>Father Name</label>
<input type="text" name="father_name" required>

<label>Phone</label>
<input type="text" name="phone" required>

<label>Gender</label>
<select name="gender" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>

<label>Date of Birth</label>
<input type="date" name="dob" required>

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

<label>Address</label>
<input type="text" name="address">

<label>City</label>
<input type="text" name="city">

<label>Student CNIC</label>
<input type="text" name="student_cnic">

<label>Father CNIC</label>
<input type="text" name="father_cnic">

<button>Complete Registration</button>
</form>
<?php endif; ?>

</div>
</div>
=======
    <style>
        *,*::before,*::after {
            box-sizing:border-box
        }

        body {
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family:Inter,sans-serif;
            background:#f4f6fb;
            overflow-x:hidden
        }
        .container {
            max-width:520px;
            width:100%;
            background:#fff;
            border-radius:16px;
            box-shadow:0 20px 40px rgba(0,0,0,.15)
        }
        .header {
            padding:30px;
            text-align:center;
            background:linear-gradient(135deg,#4a63e7,#2a4bb8);
            color:#fff
        }
        .form-body {
            padding:30px
        }
        input,select,button {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border-radius:10px;
            border:1px solid #ccc
        }
        button{
            background:#4a63e7;
            color:#fff;
            font-weight:600;
            border:none
        }
        .alert-success{
            background:#e7fff5;
            padding:12px;
            border-left:4px solid #00c896
        }
        .alert-error{
            background:#ffeaea;
            padding:12px;
            border-left:4px solid #ff4d4d
        }
        label{
            font-weight:600
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h2>Create Account</h2></div>
        <div class="form-body">

        <?php if($successMsg): ?><div class="alert-success"><?= $successMsg ?></div><?php endif; ?>
        <?php if($errorMsg): ?><div class="alert-error"><?= $errorMsg ?></div><?php endif; ?>

        <?php if($step==1): ?>
        <form method="post">
            <input type="hidden" name="step1">

            <label>Register As</label>
            <select name="role" onchange="toggleCNIC(this.value)" required>
                <option value="">Select</option>
                <option value="Student">Student</option>
                <option value="Parents">Parents</option>
            </select>

            <label>Full Name</label>
            <input type="text" name="fullname" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <div id="pcnic" style="display:none">
                <label>Parent CNIC</label>
                <input type="text" name="parent_cnic">
            </div>

            <button>Continue</button>
        </form>
        <?php endif; ?>

        <?php if($step==2): ?>
            <form method="post">
                <input type="hidden" name="step2">

                <label>Father Name</label>
                <input type="text" name="father_name" required>

                <label>Phone</label>
                <input type="text" name="phone" required>

                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>

                <label>Date of Birth</label>
                <input type="date" name="dob" required>

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

                <label>Address</label>
                <input type="text" name="address">

                <label>City</label>
                <input type="text" name="city">

                <label>Student CNIC</label>
                <input type="text" name="student_cnic">

                <label>Father CNIC</label>
                <input type="text" name="father_cnic">

                <button>Complete Registration</button>
                </form>
            <?php endif; ?>

        </div>
    </div>
>>>>>>> 874cf89 (Updated project)

<script>
function toggleCNIC(v){
    document.getElementById('pcnic').style.display = v==='Parents'?'block':'none';
}
</script>
</body>
</html>

