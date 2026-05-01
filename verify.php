<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config/db.php';

$token = $_GET['token'] ?? '';

if (!$conn) {
    die("Database connection failed.");
}

if ($token) {
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        // Redirect to login page with a success message
        header("Location: login.php");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        echo "Invalid or expired token.";
    }
} else {
    $conn->close();
    echo "No token provided.";
}
?>

