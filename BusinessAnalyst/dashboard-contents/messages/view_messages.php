<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.1.3/dist/cyborg/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        .message-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            max-height: 400px; 
            overflow-y: auto;  
        }

        .message {
            max-width: 70%;
            padding: 10px;
            margin: 5px;
            border-radius: 8px;
            word-wrap: break-word;
        }

        .my-message {
            color: white;
            background-color: #007bff; 
            align-self: flex-end; 
        }

        .other-message {
            background-color: #f1f1f1; 
            color: black;
            align-self: flex-start; 
        }

        .message-meta {
            font-size: 0.8rem;
            color: #000;
        }

        .file-link {
            text-decoration: none;
        }

        .card-body {
            max-height: 500px;  
            overflow-y: auto;  
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
            $role = $_SESSION['role'];

            $sql = "SELECT * FROM contact WHERE sender_user_type = '$role' OR recipient_user_type = '$role' ORDER BY sent_at ";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $message_class = ($row['sender_user_type'] === $role) ? 'my-message' : 'other-message';

                    echo '<div class="message-container">';
                    echo '<div class="message ' . $message_class . '">';

                    echo "<strong>From:</strong> " . htmlspecialchars($row['sender_user_type']) . "<br>";
                    echo "<p>" . htmlspecialchars($row['message']) . "</p>";
                    echo '<small class="message-meta">Received on: ' . htmlspecialchars($row['sent_at']) . '</small><br>';

                    if (!empty($row['file_path'])) {
                        $file_path = htmlspecialchars($row['file_path']);
                        echo '<a href="/WheelsIOS/BusinessAnalyst/dashboard-contents/messages/CSV/' . $file_path . '" class="file-link" target="_blank"><i class="fa fa-file-csv"></i> Open CSV</a><br>';
                    }

                    if (!empty($row['report_type'])) {
                        $report_type = htmlspecialchars($row['report_type']);
                        echo " " . ucfirst($report_type) . "<br>";
                    }

                    echo '</div>';  
                    echo '</div>';  
                }
            } else {
                echo '<p class="text-center">No messages found.</p>';
            }

            $conn->close();
            ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

</body>
</html>
