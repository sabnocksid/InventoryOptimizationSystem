<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <!-- Bootswatch Cyborg theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.1.3/dist/simplex/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-dark">

<div class="container mt-4">
    <div class="card ">
        <div class="card-header">
            <h4 class="text-center text-dark">Messages</h4>
        </div>
        <div class="card-body">

            <?php
            $host = 'localhost';
            $db = 'WheelsIOS';
            $user = 'root';
            $pass = '';

            $conn = new mysqli($host, $user, $pass, $db);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            } 
            $name = $_SESSION['username'];
            $role = $_SESSION['role'] ;

            $sql = "SELECT * FROM contact WHERE recipient_user_type = '$role' ORDER BY sent_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="d-flex justify-content-start mb-4">';
                    echo '<div class="card text-dark w-75 p-2">';
                    echo "<strong>From:</strong> ". $row['sender_user_type'] ."<br>";
                    echo "<p> Mesage: " . $row['message'] . "</p>";
                    echo "<small class='text-muted'>Received on: " . $row['created_at'] . "</small>";
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center">No messages found.</p>';
            }

            // $conn->close();
            // ?>

        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies (optional for additional components) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
