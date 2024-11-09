<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost'; // Database host
$db = 'WheelsIOS'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is set in the query string
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user data
    $sql = "SELECT username, pin, role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $pin = $_POST['pin'];
    $role = $_POST['role'];

    // Update user data and set updated_at to the current time
    $update_sql = "UPDATE users SET username = ?, pin = ?, role = ?, updated_at = NOW() WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $username, $pin, $role, $user_id);

    if ($update_stmt->execute()) {
        // Redirect to the users list page after update
        header("Location: ../landing.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/yeti/bootstrap.min.css">
</head>
<body>

<div class="container p-5 mt-5">
    <h1>Edit User</h1>
    <form method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="pin">PIN</label>
            <input type="password" class="form-control" id="pin" name="pin" value="<?php echo htmlspecialchars($user['pin']); ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="system_administrator" <?php if ($user['role'] == 'system_administrator') echo 'selected'; ?>>System Administrator</option>
                <option value="business_analyst" <?php if ($user['role'] == 'business_analyst') echo 'selected'; ?>>Business Analyst</option>
                <option value="inventory_manager" <?php if ($user['role'] == 'inventory_manager') echo 'selected'; ?>>Inventory Manager</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="../landing.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
