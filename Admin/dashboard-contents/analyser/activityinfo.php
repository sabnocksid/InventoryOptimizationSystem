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

// SQL query to fetch purchase and sales records with product names, transaction dates, and quantities
$sql = "
    SELECT 
        p.product_id,
        p.product_name,
        pur.purchase_date,
        pur.quantity AS purchase_quantity,
        sal.sale_date,
        sal.quantity AS sale_quantity
    FROM 
        product p
    LEFT JOIN 
        purchase pur ON p.product_id = pur.product_id
    LEFT JOIN 
        sales sal ON p.product_id = sal.product_id
    ORDER BY 
        COALESCE(pur.purchase_date, sal.sale_date) DESC
";

// Execute the query
$result = $conn->query($sql);

$totalPurchases = 0;
$totalSales = 0;
$totalStock = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Records</title>
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
        <h1 class="text-center pt-4 mt-5">Stock Activities</h1>

        <!-- Purchase Records Section -->
        <h2 class="text-center mt-4">Purchase Records</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Purchase Date</th>
                        <th>Purchase Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset result pointer for purchases
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()): 
                        if ($row['purchase_quantity']): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['purchase_date'] ? $row['purchase_date'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['purchase_quantity']); ?> units</td>
                            </tr>
                            <?php 
                                $totalPurchases += $row['purchase_quantity'];
                            ?>
                        <?php endif; 
                    endwhile; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total Purchases</th><td><?php echo htmlspecialchars($totalPurchases); ?> Units</td></tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p class="text-center">No purchase records found.</p>
        <?php endif; ?>

        <!-- Sales Records Section -->
        <h2 class="text-center mt-4">Sales Records</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Sale Date</th>
                        <th>Sale Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset result pointer for sales
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()): 
                        if ($row['sale_quantity']): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sale_date'] ? $row['sale_date'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['sale_quantity']); ?> units</td>
                            </tr>
                            <?php 
                                $totalSales += $row['sale_quantity'];
                            ?>
                        <?php endif; 
                    endwhile; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total Sales</th><td><?php echo htmlspecialchars($totalSales); ?> Units</td></tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p class="text-center">No sales records found.</p>
        <?php endif; ?>

        <!-- Total Stock -->
        <h2 class="text-center mt-4">Total Stock</h2>
        <table class="table table-striped">
            <tfoot>
                <tr>
                    <th>Total Stock Available</th>
                    <td><?php echo htmlspecialchars($totalPurchases - $totalSales); ?> Units</td>
                </tr>
            </tfoot>
        </table>

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
