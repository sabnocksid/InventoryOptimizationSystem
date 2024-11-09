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

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the product ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Convert ID to an integer to prevent SQL injection

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related records in the sales table
        $deleteSalesSql = "DELETE FROM sales WHERE product_id = ?";
        $stmt = $conn->prepare($deleteSalesSql);
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing delete from sales table: " . $stmt->error);
        }

        // Delete related records in the purchase table
        $deletePurchaseSql = "DELETE FROM purchase WHERE product_id = ?";
        $stmt = $conn->prepare($deletePurchaseSql);
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing delete from purchase table: " . $stmt->error);
        }

        // Delete related records in the stock table
        $deleteStockSql = "DELETE FROM stock WHERE product_id = ?";
        $stmt = $conn->prepare($deleteStockSql);
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing delete from stock table: " . $stmt->error);
        }

        // Delete the product from the product table
        $deleteProductSql = "DELETE FROM product WHERE product_id = ?";
        $stmt = $conn->prepare($deleteProductSql);
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing delete from product table: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();

        header("Location: ../../landing.php?message=Product deleted successfully");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Log the error and redirect to the product list page with an error message
        error_log("Error deleting product: " . $e->getMessage());
        header("Location: ../../landing.php?message=Error deleting product: " . $e->getMessage());
        exit();
    }
} else {
    // Redirect to the product list page with an error message
    header("Location: ../../landing.php?message=No product ID provided");
    exit();
}

// Close the connection
$conn->close();
?>
