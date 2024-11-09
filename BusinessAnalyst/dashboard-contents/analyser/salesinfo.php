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
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for the chart
$sql = "SELECT p.product_name, SUM(s.quantity) AS total_quantity
        FROM sales s
        JOIN product p ON s.product_id = p.product_id
        GROUP BY p.product_name";
$result = $conn->query($sql);

$products = [];
$quantities = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row['product_name'];
        $quantities[] = $row['total_quantity'];
    }
} else {
    echo "No sales data found.";
}

// Fetch all sales details for the table
$sql_all = "SELECT s.sales_id, p.product_name,p.price, s.quantity, s.sale_date 
            FROM sales s
            JOIN product p ON s.product_id = p.product_id
            ORDER BY s.sale_date DESC";
$result_all = $conn->query($sql_all);

$sales = [];

if ($result_all->num_rows > 0) {
    while ($row = $result_all->fetch_assoc()) {
        $sales[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Visualization</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 56px;
            position: relative;
            min-height: 100vh;
        }
        .chart-container {
            width: 80%;
            margin: auto;
        }
        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<?php include "../layout/navbar.php"; ?>

<div class="container mt-4">
<h2 class="mt-5">Sales Details</h2>
<div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Sale Date</th>
                <th>Sale ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Amount</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?php echo $sale['sale_date']; ?></td>
                    <td><?php echo $sale['sales_id']; ?></td>
                    <td><?php echo $sale['product_name']; ?></td>
                    <td><?php echo $sale['quantity']; ?></td>
                    <td><?php echo 'Rs '. $sale['price'] . ' /-'; ?></td>
                    <td><?php echo 'Rs '. $sale['quantity'] * $sale['price']. ' /-'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Data from PHP
    const productNames = <?php echo json_encode($products); ?>;
    const productQuantities = <?php echo json_encode($quantities); ?>;

    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: productNames,
            datasets: [{
                label: 'Total Sales',
                data: productQuantities,
                backgroundColor: [

                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(255, 159, 64, 0.6)',
                    'rgba(201, 203, 207, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(199, 195, 254, 0.6)',
                    'rgba(236, 112, 99, 0.6)',
                    'rgba(123, 239, 178, 0.6)',
                    'rgba(162, 217, 206, 0.6)',
                    'rgba(246, 215, 176, 0.6)',
                    'rgba(52, 73, 94, 0.6)',
                    'rgba(241, 148, 138, 0.6)',
                    'rgba(120, 144, 156, 0.6)',
                    'rgba(111, 213, 162, 0.6)',
                    'rgba(40, 116, 166, 0.6)',
                    'rgba(214, 137, 16, 0.6)',
                    'rgba(123, 36, 28, 0.6)',
                    'rgba(30, 132, 73, 0.6)'
                ],
                borderColor: [

                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(199, 195, 254, 1)',
                    'rgba(236, 112, 99, 1)',
                    'rgba(123, 239, 178, 1)',
                    'rgba(162, 217, 206, 1)',
                    'rgba(246, 215, 176, 1)',
                    'rgba(52, 73, 94, 1)',
                    'rgba(241, 148, 138, 1)',
                    'rgba(120, 144, 156, 1)',
                    'rgba(111, 213, 162, 1)',
                    'rgba(40, 116, 166, 1)',
                    'rgba(214, 137, 16, 1)',
                    'rgba(123, 36, 28, 1)',
                    'rgba(30, 132, 73, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>


<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
