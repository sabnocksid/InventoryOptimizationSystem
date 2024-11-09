<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost'; // Database host
$db = 'WheelsIOS';   // Database name
$user = 'root';      // Database username
$pass = '';          // Database password

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch users and message log
$sqlUsers = "SELECT username, role, created_at, updated_at FROM users ORDER BY created_at DESC";
$sqlMessages = "SELECT sender_user_type, recipient_user_type, message, sent_at FROM contact ORDER BY sent_at DESC";

$resultUsers = $conn->query($sqlUsers);
$resultMessages = $conn->query($sqlMessages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users and Messages</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        .table-wrapper {
            margin: 20px auto;
            max-width: 90%;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #f8f9fa;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #343a40;
            color: #ffffff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<?php include "../layout/navbar.php"; ?>

<div class="container">
    <div class="table-wrapper">
        <h1 class="text-center pt-4 mt-5">Users in the System</h1>
        
        <!-- Users Table -->
        <?php if ($resultUsers->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultUsers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No users found.</p>
        <?php endif; ?>

        <h1 class="text-center pt-4">Message Log</h1>

        <!-- Messages Table -->
        <?php if ($resultMessages->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sent by</th>
                        <th>Sent To</th>
                        <th>Message</th>
                        <th>Sent Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultMessages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['sender_user_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['recipient_user_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No messages found.</p>
        <?php endif; ?>

    </div>
</div>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
