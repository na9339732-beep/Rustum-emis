<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/db.php'; // database connection
include 'email_functions.php'; // your email sending function

$loginError = '';
$showResendButton = false; // flag to show resend button
$userEmailToResend = '';    // store email for resend

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Check if email is verified
        if ($user['email_verified'] != 1) {
            $loginError = "Please verify your email before logging in.";
            $showResendButton = true;
            $userEmailToResend = $user['email'];
        } 
        // Verify password
        else if (password_verify($password, $user['password'])) {

            // store user in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['cnic'] = $user['cnic'];

            // Redirect based on role
            switch ($user['role']) {
                case 'Admin':
                    header("Location: admin/index.php");
                    break;
                case 'Teacher':
                    header("Location: teacher/index.php");
                    break;
                case 'Student':
                    header("Location: student/index.php");
                    break;
                case 'Parents':
                    header("Location: parent/index.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit;

        } else {
            $loginError = "Incorrect password!";
        }

    } else {
        $loginError = "User not found!";
    }

    $stmt->close();
}

// Handle Resend Verification Email button click
if (isset($_POST['resend_email'])) {
    $emailToResend = $_POST['resend_email'];
    
    // Generate a new verification token
    $token = bin2hex(random_bytes(16));

    $stmt = $conn->prepare("UPDATE users SET verification_token=? WHERE email=?");
    $stmt->bind_param("ss", $token, $emailToResend);
    $stmt->execute();
    $stmt->close();

    $verificationLink = "http://localhost/finalEmis/verify_email.php?token=$token";
    $messageContent = "Please verify your email by clicking this link: <a href='$verificationLink'>$verificationLink</a>";
    sendEmail($emailToResend, "Verify Your Email", $messageContent);

    $loginError = "Verification email has been resent. Please check your inbox.";
    $showResendButton = false; // hide button after sending
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | EMIS Portal</title>
<link rel="stylesheet" href="assets/auth.css">
<style>
.alert {padding:12px;margin-bottom:15px;border-radius:6px;}
.alert-error{background:#ffeaea;border-left:4px solid #ff4d4d;}
.btn-resend {padding:8px 15px;margin-top:10px;background:#4a63e7;color:#fff;border:none;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card text-right">
        <div class="header">
            <h1>Welcome Back</h1>
            <p>Please login to your account</p>
        </div>

        <?php if($loginError): ?>
            <div class="alert alert-error"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button class="btn-primary" type="submit">Login</button>
        </form>

        <?php if($showResendButton): ?>
            <form action="" method="POST">
                <input type="hidden" name="resend_email" value="<?= htmlspecialchars($userEmailToResend) ?>">
                <button class="btn-resend" type="submit">Resend Verification Email</button>
            </form>
        <?php endif; ?>

        <p class="switch-text">
            Don’t have an account? <a href="register.php">Register</a><br>
            Forgot password? <a href="forget_password.php">Reset</a>
        </p>
    </div>
</div>

</body>
</html>

