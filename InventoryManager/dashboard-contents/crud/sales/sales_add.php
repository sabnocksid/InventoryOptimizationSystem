<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db = 'WheelsIOS';
$user = 'root';
$pass = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $sales_date = $_POST['sales_date'];

    // Validate inputs
    if (empty($product_id) || empty($quantity) || empty($sales_date)) {
        echo "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif ($quantity <= 0) {
        echo "<div class='alert alert-danger'>Quantity must be a positive number.</div>";
    } elseif (!DateTime::createFromFormat('Y-m-d', $sales_date)) {
        echo "<div class='alert alert-danger'>Invalid date format. Use YYYY-MM-DD.</div>";
    } else {
        // Start a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Check stock level
            $stock_sql = "
                SELECT quantity
                FROM stock
                WHERE product_id = ?
            ";
            $stock_stmt = $conn->prepare($stock_sql);
            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();
            $stock_row = $stock_result->fetch_assoc();

            if ($stock_row && $stock_row['quantity'] >= $quantity) {
                // Update stock quantity
                $sql_update_stock = "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?";
                $stmt_update_stock = $conn->prepare($sql_update_stock);
                $stmt_update_stock->bind_param("ii", $quantity, $product_id);
                $stmt_update_stock->execute();

                // Insert sales record
                $sql_insert_sales = "INSERT INTO sales (product_id, quantity, sale_date) VALUES (?, ?, ?)";
                $stmt_insert_sales = $conn->prepare($sql_insert_sales);
                $stmt_insert_sales->bind_param("iis", $product_id, $quantity, $sales_date);
                $stmt_insert_sales->execute();

                // Commit the transaction
                $conn->commit();

                // Redirect to sales_display.php
                header("Location: sales_display.php?message=Sale added successfully");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error: Insufficient stock level for this sale. Available stock: " . ($stock_row['quantity'] ?? 0) . ".</div>";
                $conn->rollback();
            }

        } catch (Exception $e) {
            // Roll back the transaction in case of any error
            $conn->rollback();
            echo "<div class='alert alert-danger'>Failed to add sales entry: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch products for the dropdown menu
$sql = "SELECT product_id, product_name, picture FROM product";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "No products found";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Sales</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h1>Add Sales</h1>
    <form method="POST">
        <div class="form-group">
            <label for="product_id">Select Product</label>
            <select class="form-control" id="product_id" name="product_id" required>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?php echo $row['product_id']; ?>">
                        <?php echo htmlspecialchars($row['product_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="form-group">
            <label for="sales_date">Sales Date</label>
            <input type="date" class="form-control" id="sales_date" name="sales_date" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Sales</button>
    </form>
</div>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
