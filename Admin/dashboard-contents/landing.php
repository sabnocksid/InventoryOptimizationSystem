<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost'; 
$db = 'WheelsIOS'; 
$user = 'root'; 
$pass = ''; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . dirname(__FILE__) . "/../../login.php");
    exit();
}

$role = $_SESSION['role'];

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT user_id, username, pin, role, created_at FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Data</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/yeti/bootstrap.min.css">
    <style>
        .fixed-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
   
<?php include "layout/navbar.php"; ?>

    <div class="container p-5 mt-5">
        <h1>Users</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>PIN</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['pin']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <a href="/wheelsIOS/admin/dashboard-contents/analyser/edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to edit this user?');">Edit</a>
                                <a href="/wheelsIOS/admin/dashboard-contents/analyser/delete_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

    <a href="/wheelsIOS/admin/dashboard-contents/analyser/add_user.php" class="btn btn-success btn-lg fixed-button">Add User</a>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php
    $conn->close();
    ?>
</body>
</html>
