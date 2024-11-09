<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost'; // Database host
$db = 'WheelsIOS'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

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
        s.sales_id, 
        p.product_name, 
        p.picture,
        p.price, 
        s.quantity AS sales_quantity, 
        s.sale_date 
    FROM 
        sales s
    JOIN 
        product p 
    ON 
        s.product_id = p.product_id
    ORDER by sale_date
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Records</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        .table-wrapper {
            margin: 20px auto;
            max-width: 80%;
            padding-bottom: 60px; /* Space for the fixed button */
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
        img {
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .fixed-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000; /* Ensures it stays above other content */
        }
        .fixed-btn-left {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
    }
    </style>
</head>
<body>
<?php include 'C:/xampp/htdocs/WheelsIOS/InventoryManager/dashboard-contents/layout/navbar.php'; ?>

<div class="container">
    <div class="table-wrapper">
        <h1 class="text-center mb-4">Sales Records</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sales Date</th>
                        <th>Product Name</th>
                        <th>Picture</th>
                        <th>Sales Quantity</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                           <td><?php echo htmlspecialchars($row['sale_date']); ?></td>

                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>
                                <?php if (!empty($row['picture'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" width="100">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['sales_quantity']); ?></td>
                            <td><?php 
                                $total_amt = $row['sales_quantity'] * $row['price']; 
                                echo htmlspecialchars('Rs '.$total_amt.' /-'); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No records found</p>
        <?php endif; ?>
    </div>
</div>

<!-- Fixed Add Sales Button -->
<a href="sales_add.php" class="btn btn-primary fixed-btn">Add Sales</a>
<a href="javascript:void(0);" id="downloadCSV" class="btn btn-success fixed-btn-left">Download CSV</a>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.getElementById('downloadCSV').addEventListener('click', function () {
    window.location.href = 'report/salesCSV.php'; // This PHP script will generate and download the CSV file
});
</script>
<script>
        // Function to get query parameters
        function getQueryParam(param) {
            let urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Check if status is success and show alert
        if (getQueryParam('status') === 'success') {
            alert('CSV file generated and saved successfully!');
        }
    </script>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
