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
    header("Location: " . dirname(__FILE__) . "/../../login.php");
    exit();
}

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $pin = $_POST['pin'];
    $confirm_pin = $_POST['confirm_pin'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username) || empty($pin) || empty($confirm_pin) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($pin !== $confirm_pin) {
        $error = "PIN and confirmation PIN do not match.";
    } elseif (!preg_match('/^\d{4,6}$/', $pin)) {
        $error = "PIN must be 4-6 digits long.";
    } else {
        // Check if the username already exists in the database
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already exists. Please choose a different one.";
        } else {
            // If the username doesn't exist, insert the new user
            $sql = "INSERT INTO users (username, pin, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $pin, $role);

            if ($stmt->execute()) {
                header("Location: ../landing.php");
                exit();
            } else {
                $error = "Error adding user: " . $conn->error;
            }
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/yeti/bootstrap.min.css">
</head>
<body>

<div class="container p-5 mt-5">
    <h1>Add New User</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="pin">New PIN</label>
            <input type="password" class="form-control" id="pin" name="pin" maxlength="6" inputmode="numeric" pattern="\d{4,6}" required>
            <small class="form-text text-muted">Enter a 4-6 digit PIN.</small>
        </div>
        <div class="form-group">
            <label for="confirm_pin">Confirm PIN</label>
            <input type="password" class="form-control" id="confirm_pin" name="confirm_pin" maxlength="6" inputmode="numeric" pattern="\d{4,6}" required>
            <small class="form-text text-muted">Re-enter the 4-6 digit PIN.</small>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="system_administrator">System Administrator</option>
                <option value="business_analyst">Business Analyst</option>
                <option value="inventory_manager">Inventory Manager</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="../landing.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
