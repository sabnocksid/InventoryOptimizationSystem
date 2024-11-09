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

// Perform the SQL JOIN query to get the data
$sql = "
    SELECT 
        pu.purchase_date, 
        pu.purchase_id, 
        p.product_name, 
        pu.quantity AS purchase_quantity, 
        p.price 
    FROM 
        purchase pu
    LEFT JOIN 
        product p 
    ON 
        pu.product_id = p.product_id
";

// Execute the query
$result = $conn->query($sql);

// Define the folder and file name
$folder = 'InventoryManagerReport';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true); // Create the folder if it doesn't exist
}
$filename = $folder . '/purchases_' . date('Y-m-d_H-i-s') . '.csv';

// Open the file for writing in the folder
$file = fopen($filename, 'w');

// Write the CSV column headers
fputcsv($file, ['Purchase Date', 'Purchase ID', 'Product Name', 'Purchase Quantity', 'Total Amount']);

// Write the rows to the CSV file
while ($row = $result->fetch_assoc()) {
    $total_amt = $row['purchase_quantity'] * $row['price'];
    fputcsv($file, [
        $row['purchase_date'], 
        $row['purchase_id'], 
        $row['product_name'], 
        $row['purchase_quantity'], 
        'Rs ' . number_format($total_amt, 2) . ' /-'
    ]);
}

// Close the file
fclose($file);

// Close the database connection
$conn->close();

// Redirect to purchase_display.php with a success query parameter
header('Location: ../../purchase/purchase_display.php?status=success');
exit();
?>
