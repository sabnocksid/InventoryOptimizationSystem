<?php
$projectRoot = '/wheelsIOS/InventoryManager/';
$crudRoot = $projectRoot . 'dashboard-contents/crud/product/';
$dashboardRoot = $projectRoot . 'dashboard-contents/';

// Database connection settings
$host = 'localhost'; // Database host
$db = 'WheelsIOS'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Fetch products with the total stock level (sum of positive and negative quantities)
$sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.safety_stock, p.picture, 
               COALESCE(SUM(s.quantity), 0) AS total_stock
        FROM product p
        LEFT JOIN stock s ON p.product_id = s.product_id
        GROUP BY p.product_id, p.product_name, p.description, p.price, p.safety_stock, p.picture";
$result = $conn->query($sql);

// Retrieve status and message from the URL
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        body {
            padding-top: 56px; /* Adjust this if necessary to match the navbar height */
            position: relative;
            min-height: 100vh; /* Ensure the body takes up full height */
        }
        .table-container {
            max-height: 500px; /* Adjust height as needed */
            overflow-y: auto;
        }
        .fixed-bottom-button {
            position: fixed;
            bottom: 20px; /* Space from the bottom */
            right: 20px; /* Space from the right */
            z-index: 1000; /* Ensure the button is on top */
        }
        .low-stock {
            background-color: #f8d7da !important; /* Red background for low stock */
        }
        .high-stock {
            background-color: #d4edda !important; /* Green background for sufficient stock */
        }
        .alert-message {
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<?php include 'layout/navbar.php'; ?>
<div class="container mt-4">
<?php if (isset($message) && !empty($message)): ?>
    <div class="alert <?php echo $status == 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

    <h1>Product List</h1>
    <div class="table-container">
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Safety Stock</th>
                        <th>Total Stock</th>
                        <th>Picture</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // Determine the class based on stock quantities
                        $rowClass = $row['total_stock'] < $row['safety_stock'] ? 'low-stock' : 'high-stock';
                        ?>
                        <tr class="<?php echo htmlspecialchars($rowClass); ?>">
                            <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['safety_stock']); ?></td>
                            <td>
                                <?php 
                                    echo htmlspecialchars($row['total_stock']);
                                    if ($row['total_stock'] < $row['safety_stock']) {
                                        echo "<span style='color: red;'> (Low Stock!)</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row['picture'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" width="100">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Edit Button -->
                                <a href="<?php echo $crudRoot; ?>product_edit.php?id=<?php echo htmlspecialchars($row['product_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                
                                <!-- Delete Button -->
                                <a href="<?php echo $crudRoot; ?>product_delete.php?id=<?php echo htmlspecialchars($row['product_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Button -->
<a href="<?php echo $dashboardRoot; ?>addproductpage.php" class="btn btn-primary fixed-bottom-button">Add Product</a>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
