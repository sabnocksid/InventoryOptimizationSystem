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

$sql = "SELECT user_id, username FROM users"; 
$result = $conn->query($sql);
?>
<?php include "../layout/navbar.php"; ?>
<br>
<br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini Chat App</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/cyborg/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Welcome, <?php echo $_SESSION['username']; ?> (Role: <?php echo $_SESSION['role']; ?>)</h1>
        <h2 class="mt-4">Send a Message</h2>
        <form action="send_message.php" method="POST">
            <div class="form-group">
                <label for="receiver_id">Receiver:</label>
                <select class="form-control" id="receiver_id" name="receiver_id" required>
                    <option value="">Select a user</option>
                    <?php
                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['user_id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
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
$conn->close();
?>
