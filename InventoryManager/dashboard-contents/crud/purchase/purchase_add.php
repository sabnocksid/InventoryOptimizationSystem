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
    $quantity = (int)$_POST['quantity'];  // Ensure quantity is an integer
    $purchase_date = $_POST['purchase_date'];

    // Validate inputs
    if (empty($product_id) || empty($quantity) || empty($purchase_date)) {
        echo "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif (!filter_var($product_id, FILTER_VALIDATE_INT)) {
        echo "<div class='alert alert-danger'>Invalid product ID.</div>";
    } elseif ($quantity <= 0) {
        echo "<div class='alert alert-danger'>Quantity must be a positive number.</div>";
    } elseif (!DateTime::createFromFormat('Y-m-d', $purchase_date)) {
        echo "<div class='alert alert-danger'>Invalid date format. Use YYYY-MM-DD.</div>";
    } else {
        // Start a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Always insert a new record into the purchase table
            $sql = "INSERT INTO purchase (product_id, quantity, purchase_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $product_id, $quantity, $purchase_date);
            $stmt->execute();

            // Check if the product exists in the stock table
            $sql_check = "SELECT * FROM stock WHERE product_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // If the product exists, update the quantity
                $sql_update = "UPDATE stock SET quantity = quantity + ? WHERE product_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ii", $quantity, $product_id);
                $stmt_update->execute();
            } else {
                // If the product does not exist, insert a new record
                $sql_insert = "INSERT INTO stock (product_id, quantity) VALUES (?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ii", $product_id, $quantity);
                $stmt_insert->execute();
            }

            // Commit the transaction
            $conn->commit();

            // Redirect to the purchase display page with a success message
            header("Location: purchase_display.php?message=Purchase added successfully");
            exit();

        } catch (Exception $e) {
            // Roll back the transaction in case of any error
            $conn->rollback();
            echo "<div class='alert alert-danger'>Failed to add purchase: " . $e->getMessage() . "</div>";
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
    <title>Add Purchase</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h1>Add Purchase</h1>
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
            <label for="purchase_date">Purchase Date</label>
            <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Purchase</button>
    </form>
</div>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
