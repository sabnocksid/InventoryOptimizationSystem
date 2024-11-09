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

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the product ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Convert ID to an integer to prevent SQL injection

    // Fetch the product details
    $sql = "SELECT product_id, product_name, description, price, safety_stock, picture FROM product WHERE product_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    die("No product ID specified");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $picture = null;
    
    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $picture = file_get_contents($_FILES['picture']['tmp_name']);
    }

    // Update the product details
    $sql = "UPDATE product SET product_name = ?, description = ?, price = ?, safety_stock = ?, picture = ? WHERE product_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssdisi", $product_name, $description, $price, $safety_stock, $picture, $product_id);
        if ($stmt->execute()) {
            header("Location: ../landing.php?message=Product updated successfully");
            exit();
        } else {
            die("Error updating product: " . $stmt->error);
        }
        // $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
</head>
<body>


<div class="container mt-5">
    <h1>Edit Product</h1>
    <form action="product_edit.php?id=<?php echo htmlspecialchars($product_id); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        <div class="form-group">
            <label for="picture">Product Picture</label>
            <input type="file" class="form-control-file" id="picture" name="picture">
            <?php if (!empty($product['picture'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($product['picture']); ?>" alt="Product Picture" width="100">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
