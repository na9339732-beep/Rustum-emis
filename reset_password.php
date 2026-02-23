<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php';

$token = $_GET['token'] ?? '';
$message = '';

if (!$token) {
    die("No token provided.");
}

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

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("ss", $new_password, $token);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "Password reset successfully! You can now <a href='login.php'>login</a>.";
        } else {
            $message = "Invalid or expired token.";
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
<title>Reset Password | EMIS Portal</title>
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
<h2>Reset Password</h2>

<?php if($message): ?>
    <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error' ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<?php if(strpos($message, 'successfully') === false): ?>
<form method="POST">
    <label>New Password</label>
    <input type="password" name="password" placeholder="Enter new password" required>

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" placeholder="Confirm new password" required>

    <button type="submit">Reset Password</button>
</form>
<?php endif; ?>
</div>
</body>
</html>

