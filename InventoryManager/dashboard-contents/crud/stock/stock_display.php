<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost'; 
$db = 'WheelsIOS';   
$user = 'root';      
$pass = '';          

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
    p.product_id, 
    p.product_name, 
    IFNULL(pur.total_purchased, 0) AS total_purchased,
    IFNULL(s.total_sold, 0) AS total_sold,
    IFNULL(st.quantity, 0) AS stock_level
FROM 
    product p
LEFT JOIN 
    (SELECT product_id, SUM(quantity) AS total_purchased FROM purchase GROUP BY product_id) pur 
    ON p.product_id = pur.product_id
LEFT JOIN 
    (SELECT product_id, SUM(quantity) AS total_sold FROM sales GROUP BY product_id) s 
    ON p.product_id = s.product_id
LEFT JOIN 
    stock st ON p.product_id = st.product_id;
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Levels</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        .table-wrapper {
            margin: 20px auto;
            max-width: 80%;
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

<?php include 'C:/xampp/htdocs/WheelsIOS/InventoryManager/dashboard-contents/layout/navbar.php'; ?>

<div class="container">
    <div class="table-wrapper">
        <h1 class="text-center mb-4">Stock Levels</h1>
        
        <!-- Stock on Hand Table -->
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Total Purchased</th>
                        <th>Total Sold</th>
                        <th>Stock on Hand</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_purchased']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_sold']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock_level']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No records found</p>
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
