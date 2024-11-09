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

// Perform the SQL JOIN query to get sales and product details
$sql = "
    SELECT 
        s.sales_id, 
        p.product_name, 
        p.price, 
        s.quantity AS sales_quantity, 
        s.sale_date 
    FROM 
        sales s
    JOIN 
        product p 
    ON 
        s.product_id = p.product_id
";

// Execute the query
$result = $conn->query($sql);

// Define the folder and file name
$folder = 'InventoryManagerReport';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true); // Create the folder if it doesn't exist
}
$filename = $folder . '/sales_' . date('Y-m-d_H-i-s') . '.csv';

// Open the file for writing in the folder
$file = fopen($filename, 'w');

// Write the CSV column headers
fputcsv($file, ['Sales Date', 'Sales ID', 'Product Name', 'Sales Quantity', 'Amount']);

// Write the rows to the CSV file
while ($row = $result->fetch_assoc()) {
    $total_amt = $row['sales_quantity'] * $row['price'];
    fputcsv($file, [
        $row['sale_date'], 
        $row['sales_id'], 
        $row['product_name'], 
        $row['sales_quantity'], 
        'Rs ' . number_format($total_amt, 2) . ' /-'
    ]);
}

// Close the file
fclose($file);

// Close the database connection
$conn->close();

// Redirect to sales_display.php with a success query parameter
header('Location: ../../sales/sales_display.php?status=success');
exit();
?>
