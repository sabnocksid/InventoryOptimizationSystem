<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost'; // Database host
$db = 'WheelsIOS'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Create a new database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
   

    $picture_data = null;

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $upload_ok = 1;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            $upload_ok = 1;
        } else {
            echo "<p>File is not an image.</p>";
            $upload_ok = 0;
        }

        // Check file size (limit to 5MB)
        if ($_FILES['image']['size'] > 5000000) {
            echo "<p>Sorry, your file is too large.</p>";
            $upload_ok = 0;
        }

        // Allow certain file formats
        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif") {
            echo "<p>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
            $upload_ok = 0;
        }

        // Check if $upload_ok is set to 0 by an error
        if ($upload_ok == 0) {
            echo "<p>Sorry, your file was not uploaded.</p>";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $picture_data = file_get_contents($target_file);
                echo "<p>The file ". htmlspecialchars(basename($_FILES['image']['name'])). " has been uploaded.</p>";
            } else {
                echo "<p>Sorry, there was an error uploading your file.</p>";
            }
        }
    }

    // Prepare and execute the query to insert product details
    $sql = "INSERT INTO product (product_name, description, price, picture) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssds", $product_name, $description, $price, $picture_data);

    if ($stmt->execute()) {
        // Close the statement
        $stmt->close();
        
        // Redirect to landing page with success message
        header("Location: ../../landing.php?status=success&message=Product%20added%20successfully");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Close the connection
    $conn->close();
}
?>
