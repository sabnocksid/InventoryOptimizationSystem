<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wheelsios"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT user_id, username, role FROM users"; 
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['receiver_id'], $_POST['message'])) {
        $receiver_id = intval($_POST['receiver_id']);
        $message = $_POST['message'];

        $sender_user_type = $_SESSION['role']; 

        $receiver_role_query = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($receiver_role_query);
        $stmt->bind_param("i", $receiver_id);
        $stmt->execute();
        $receiver_result = $stmt->get_result();
        $receiver_row = $receiver_result->fetch_assoc();
        $recipient_user_type = $receiver_row['role'] ?? null;

        $valid_roles = ['system_administrator', 'business_analyst', 'inventory_manager'];
        if (!in_array($sender_user_type, $valid_roles) || !in_array($recipient_user_type, $valid_roles)) {
            echo "<p class='alert alert-danger'>Error: Invalid user type.</p>";
            exit();
        }

        // Insert the message into the contact table
        $stmt = $conn->prepare("INSERT INTO contact (sender_user_type, recipient_user_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $sender_user_type, $recipient_user_type, $message);

        if ($stmt->execute()) {
            echo "<p class='alert alert-success'>Message sent successfully!</p>";
        } else {
            echo "<p class='alert alert-danger'>Error sending message: " . $stmt->error . "</p>";
        }

        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<?php include('../layout/navbar.php'); ?>
<br>
<br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/yeti/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h6 class="mt-5">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Role: <?php echo htmlspecialchars($_SESSION['role']); ?>)</h6>
        <h2 class="mt-4">Send a Message</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="receiver_id">Receiver:</label>
                <select class="form-control" id="receiver_id" name="receiver_id" required>
                    <option value="">Select a user</option>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['user_id']) . "'>" . htmlspecialchars($row['username']) . " (Role: " . htmlspecialchars($row['role']) . ")</option>";
                        }
                    } else {
                        echo "<option value=''>No users found</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send</button>
        </form>

        <h2 class="mt-5">Your Messages</h2>
        <?php include 'view_messages.php'; ?>
    </div>
</body>
</html>

<?php
// $conn->close();
?>
