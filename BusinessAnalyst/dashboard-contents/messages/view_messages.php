<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <!-- Bootswatch Cyborg theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.1.3/dist/cyborg/bootstrap.min.css" rel="stylesheet">
    <style>
        .message-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .message-card {
            width: 75%;
        }
        .file-link {
            margin-left: 20px;
        }
    </style>
</head>
<body class="bg-light text-dark">

<div class="container mt-4">
    <div class="card">
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
            $role = $_SESSION['role'];

            $sql = "SELECT * FROM contact WHERE recipient_user_type = '$role' ORDER BY sent_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="message-container">';
                    
                    // Message card (left side)
                    echo '<div class="card text-dark message-card p-2">';
                    echo "<strong>From:</strong> ". $row['sender_user_type'] ."<br>";
                    echo "<p>Message: " . $row['message'] . "</p>";
                    echo "<small class='text-muted'>Received on: " . $row['sent_at'] . "</small>";
                    echo '</div>';

                    // File link (right side) if available
                    if (!empty($row['file_path']) && pathinfo($row['file_path'], PATHINFO_EXTENSION) === 'csv') {
                        $file_url = './CSV/' . $row['file_path'];
                        echo '<div class="file-link">';
                        echo '<a href="' . htmlspecialchars($file_url) . '" class="btn btn-primary" download>' . htmlspecialchars(basename($row['file_path'])) . ' (Download CSV)</a>';
                        echo '</div>';
                    }

                    echo '</div>';
                }
            } else {
                echo '<p class="text-center">No messages found.</p>';
            }

            // $conn->close();
            ?>

        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies (optional for additional components) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>