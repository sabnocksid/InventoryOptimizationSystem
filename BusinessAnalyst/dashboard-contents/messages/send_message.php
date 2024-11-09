<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db = 'WheelsIOS';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sender_user_type = "sender_type";  
$recipient_user_type = "recipient_type";
$message = "This is a test message";  
$stmt = $conn->prepare("INSERT INTO contact (sender_user_type, recipient_user_type, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $sender_user_type, $recipient_user_type, $message);

if ($stmt->execute()) {
    echo "<p class='alert alert-success'>Message sent successfully!</p>";
} else {
    echo "<p class='alert alert-danger'>Error sending message: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

// Redirect to smessageInvManager.php after successful execution
header("Location: messageInvManager.php");
exit();
?>
