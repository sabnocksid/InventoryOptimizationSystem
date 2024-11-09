<?php
session_start(); // Start the session

// Database connection settings
$host = 'localhost'; 
$db = 'WheelsIOS'; 
$user = 'root'; 
$pass = ''; 

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$message = '';
$status = '';

// Handle safety stock update form submission
if (isset($_POST['update_safety_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $safety_stock = (int)$_POST['safety_stock'];
    
    // Update safety stock in the database
    $sql_update = "
        UPDATE product
        SET safety_stock = ?
        WHERE product_id = ?
    ";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $safety_stock, $product_id);
    
    if ($stmt_update->execute()) {
        $message = 'Safety stock updated successfully!';
        $status = 'success';
    } else {
        $message = 'Error updating safety stock.';
        $status = 'danger';
    }
    
    $stmt_update->close();
}

// Default product ID
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// SQL query to get product options for the dropdown
$sql_products = "SELECT product_id, product_name FROM product";
$result_products = $conn->query($sql_products);

// Prepare SQL query for fetching purchase and sales details if a product is selected
if ($product_id > 0) {
    $sql_details = "
        SELECT 
            p.product_id,
            pu.quantity AS purchase_quantity,
            s.quantity AS sales_quantity
        FROM 
            product p
        LEFT JOIN 
            purchase pu ON p.product_id = pu.product_id
        LEFT JOIN 
            sales s ON p.product_id = s.product_id
        WHERE 
            p.product_id = ?
    ";
    $stmt_details = $conn->prepare($sql_details);
    $stmt_details->bind_param("i", $product_id);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        
        .fixed-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000; 
        }
    </style>
</head>
<body>
<?php include "../layout/navbar.php"; ?>

<div class="container">
    <div class="mt-5">
        
        <!-- Form for selecting a product -->
        <form method="POST" action="" >
            <div class="form-group">
                <label for="product_id" class="mt-5">Select Product:</label>
                <select id="product_id" name="product_id" class="form-control" onchange="this.form.submit()">
                    <option value="">--Select Product--</option>
                    <?php while ($row = $result_products->fetch_assoc()): ?>
                        <option value="<?php echo $row['product_id']; ?>" <?php echo ($product_id == $row['product_id']) ? 'selected' : ''; ?> >
                            <?php echo htmlspecialchars($row['product_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
        
        <?php if ($product_id > 0): ?>
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Probability Factor (%)</th>
                        <th>Mean</th>
                        <th>Standard Deviation</th>
                        <th>Recommended Safety Stock(units)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_details && $result_details->num_rows > 0): ?>
                        <?php 
                            $total_purchase_quantity = 0;
                            $total_sales_quantity = 0;
                            $productCount = 0;
                            $squared_diff_sum = 0; // For variance calculation
                        ?>
                        <?php while ($row = $result_details->fetch_assoc()): ?>
                            <?php 
                                $purchase_quantity = $row['purchase_quantity'];
                                $sales_quantity = $row['sales_quantity'];
                                $total_purchase_quantity += $purchase_quantity;
                                $total_sales_quantity += $sales_quantity;
                                $productCount++;
                                
                                // Calculate squared differences from the mean
                                $mean = $total_purchase_quantity / $productCount;
                                $squared_diff_sum += pow(($total_purchase_quantity - $mean), 2);
                            ?>
                        <?php endwhile; ?>

                        <?php 
                            // Calculate stock value and probability factor
                            $stock_value = $total_purchase_quantity - $total_sales_quantity;
                            $probability_factor = $total_purchase_quantity > 0 ? ($total_sales_quantity / $total_purchase_quantity) * 100 : 0;
                            $probability_factor = round($probability_factor, 2); // Round to 2 decimal places

                            // Calculate standard deviation
                            $variance = $productCount > 1 ? $squared_diff_sum / ($productCount - 1) : 0;
                            $std_dev = sqrt($variance);
                            $std_dev = round($std_dev, 2); // Round to 2 decimal places

                            $z_score = 1.65; // For 95% service level
                            
                            // Calculate safety stock
                            $safety_stock = $z_score * $std_dev;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product_id); ?></td>
                            <td><?php echo $probability_factor . '%'; ?></td>
                            <td><?php echo (int)$mean; ?></td> 
                            <td><?php echo (int)$std_dev; ?></td>
                            <td><?php echo (int)$safety_stock; ?></td> 
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <form method="POST" action="">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                <input type="hidden" name="safety_stock" value="<?php echo (int)$safety_stock; ?>">
                <button type="submit" name="update_safety_stock" class="btn btn-info fixed-btn">Update Safety Stock</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Notification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-<?php echo $status; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Show the modal if there is a message
    <?php if (!empty($message)): ?>
        $(document).ready(function() {
            $('#alertModal').modal('show');
        });
    <?php endif; ?>
</script>

</body>
</html>

<?php
// Close connections
if (isset($stmt_details)) $stmt_details->close();
$conn->close();
?>
