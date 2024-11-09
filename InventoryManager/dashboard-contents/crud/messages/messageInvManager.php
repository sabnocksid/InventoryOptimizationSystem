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

// Query to get users with their roles for the dropdown
$sql = "SELECT user_id, username, role FROM users"; 
$result = $conn->query($sql);

// Handle form submission for sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['receiver_id'], $_POST['message'], $_POST['report_type'])) {
        $receiver_id = intval($_POST['receiver_id']);
        $message = $_POST['message'];
        $report_type = $_POST['report_type'];

        // Retrieve the sender's role from the session
        $sender_user_type = $_SESSION['role']; 

        // Get the role of the receiver to determine the recipient_user_type
        $receiver_role_query = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($receiver_role_query);
        $stmt->bind_param("i", $receiver_id);
        $stmt->execute();
        $receiver_result = $stmt->get_result();
        $receiver_row = $receiver_result->fetch_assoc();
        $recipient_user_type = $receiver_row['role'] ?? null;

        // Check if roles are valid
        $valid_roles = ['system_administrator', 'business_analyst', 'inventory_manager'];
        if (!in_array($sender_user_type, $valid_roles) || !in_array($recipient_user_type, $valid_roles)) {
            echo "<p class='alert alert-danger'>Error: Invalid user type.</p>";
            exit();
        }

        // Directory for storing messages with attached CSV files
        $upload_dir = '../../../../BusinessAnalyst/dashboard-contents/messages/CSV';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
        }

        // Handle CSV file upload
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $csv_file = $_FILES['csv_file'];
            $file_ext = pathinfo($csv_file['name'], PATHINFO_EXTENSION);

            // Check if the uploaded file is a CSV
            if ($file_ext !== 'csv') {
                echo "<p class='alert alert-danger'>Error: Only CSV files are allowed.</p>";
            } else {
                $file_name = basename($csv_file['name']); // Extract only the file name
                $file_path = $upload_dir . '/' . $file_name;

                // Move the uploaded file to the designated directory
                if (move_uploaded_file($csv_file['tmp_name'], $file_path)) {
                    // Store the message and only the file name in the database
                    $stmt = $conn->prepare("INSERT INTO contact (sender_user_type, recipient_user_type, message, file_path) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $sender_user_type, $recipient_user_type, $message, $file_name);

                    if ($stmt->execute()) {
                        echo "<p class='alert alert-success'>Message sent successfully!</p>";
                    } else {
                        echo "<p class='alert alert-danger'>Error sending message: " . $stmt->error . "</p>";
                    }

                    $stmt->close();
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    echo "<p class='alert alert-danger'>Error uploading file.</p>";
                }
            }
        } else {
            // If no file was uploaded, just store the message
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
}
?>

<?php include('../../layout/navbar.php'); ?>
<br>
<br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini Chat App</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Role: <?php echo htmlspecialchars($_SESSION['role']); ?>)</h1>
        <h2 class="mt-4">Send a Message</h2>
        <form action="" method="POST" enctype="multipart/form-data">
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

            <div class="form-group">
                <label for="csv_file">Attach CSV File (optional):</label>
                <input type="file" class="form-control-file" id="csv_file" name="csv_file" accept=".csv">
            </div>

            <div class="form-group">
                <label for="report_type">Report Type:</label>
                <select class="form-control" id="report_type" name="report_type" required>
                    <option value="sales">Sales</option>
                    <option value="purchase">Purchase</option>
                </select>
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
