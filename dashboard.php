<?php
session_start();
$host = 'localhost'; // Database host
$db = 'WheelsIOS'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">

</head>
<body>
    <?php if ($role == 'inventory_manager'): ?>
        <?php include "InventoryManager/dashboard-contents/landing.php" ?>
    <?php elseif ($role == 'business_analyst'): ?>
        <?php include "BusinessAnalyst/dashboard-contents/landing.php" ?>
    <?php elseif ($role == 'system_administrator'): ?>
        <?php include "Admin/dashboard-contents/landing.php" ?>
    <?php else: ?>
        <p>You are not authorized</p>
    <?php endif; ?>

    
</body>
</html>
