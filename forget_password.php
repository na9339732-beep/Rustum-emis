<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/db.php';
include 'email_functions.php';

$token = $_GET['token'] ?? '';
$message = '';
$tokenExists = false; // Flag to show debug JS alert if needed

if ($token) {
    echo $token;
    // ===== RESET PASSWORD FLOW =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$password || !$confirm) {
            $message = "Both fields are required.";
        } elseif ($password !== $confirm) {
            $message = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters.";
        } else {
            $new_password = password_hash($password, PASSWORD_DEFAULT);

            // Check if token exists and is not expired
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows > 0) {
                $tokenExists = true;

                // Update password and clear token
                $stmt_update = $conn->prepare(
                    "UPDATE users 
                     SET password = ?, reset_token = NULL, reset_expires = NULL 
                     WHERE reset_token = ?"
                );
                $stmt_update->bind_param("ss", $new_password, $token);
                $stmt_update->execute();
                $stmt_update->close();

                $message = "Password reset successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $message = "Invalid or expired token.";
            }

            $stmt->close();
        }
    }

} else {
    // ===== FORGOT PASSWORD FLOW =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = strtolower(trim($_POST['email']));

        // Check if user exists and email is verified
        $stmt = $conn->prepare("SELECT user_id, email_verified FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['email_verified'] != 1) {
                $message = "Please verify your email before resetting your password.";
            } else {
                // Generate reset token and expiry
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $update->bind_param("sss", $token, $expires, $email);
                $update->execute();

                if ($update->affected_rows > 0) {
                    $resetLink = "http://localhost/finalEmis/forget_password.php?token=$token";
                    $messageContent = "Click this link to reset your password (valid for 1 hour): <a href='$resetLink'>$resetLink</a>";
                    sendEmail($email, "Reset Your Password", $messageContent);

                    $message = "Check your email for the reset link!";
                } else {
                    $message = "Failed to generate reset link. Try again later.";
                }
                $update->close();
            }
        } else {
            $message = "Email not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $token ? 'Reset Password' : 'Forgot Password' ?> | EMIS Portal</title>
<style>
body{font-family:sans-serif;background:#f4f6fb;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
.container{background:#fff;padding:30px;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.15);width:100%;max-width:400px;}
input{width:100%;padding:12px;margin-bottom:15px;border-radius:10px;border:1px solid #ccc;}
button{width:100%;padding:12px;background:#4a63e7;color:#fff;font-weight:600;border:none;border-radius:10px;}
.alert{padding:12px;margin-bottom:15px;border-radius:6px;}
.alert-success{background:#e7fff5;border-left:4px solid #00c896;}
.alert-error{background:#ffeaea;border-left:4px solid #ff4d4d;}
</style>
</head>
<body>
<div class="container">
<h2><?= $token ? 'Reset Password' : 'Forgot Password' ?></h2>

<?php if($message): ?>
    <div class="alert <?= (strpos($message,'Check your email')!==false || strpos($message,'successfully')!==false)?'alert-success':'alert-error' ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<?php if($token): ?>
<form method="POST" action="forget_password.php?token=<?php echo htmlspecialchars($token); ?>">
    <label>New Password</label>
    <input type="password" name="password" placeholder="Enter new password" required>
    <label>Confirm Password</label>
    <input type="password" name="confirm_password" placeholder="Confirm new password" required>
    <button type="submit">Reset Password</button>
</form>
<?php else: ?>
<form method="POST">
    <label>Email</label>
    <input type="email" name="email" placeholder="Enter your registered email" required>
    <button type="submit">Send Reset Link</button>
</form>
<p>Remember your password? <a href="login.php">Login</a></p>
<?php endif; ?>
</div>

<?php if($tokenExists): ?>
<script>
    alert('Token exists');
</script>
<?php endif; ?>

</body>
</html>

