<?php

// Database connection details
$host = 'localhost'; 
$db = 'WheelsIOS'; 
$user = 'root'; 
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// SQL query to fetch product details
$sql_products = "
    SELECT 
        p.product_id,
        p.product_name,
        p.safety_stock,
        COALESCE(SUM(s.quantity), 0) AS total_sales_quantity,
        COALESCE(SUM(pu.quantity), 0) AS total_purchase_quantity
    FROM 
        product p
    LEFT JOIN 
        sales s ON p.product_id = s.product_id
    LEFT JOIN 
        purchase pu ON p.product_id = pu.product_id
    GROUP BY 
        p.product_id, p.product_name, p.safety_stock
";

$result_products = $conn->query($sql_products);

if ($result_products === false) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    $conn->close();
    exit();
}

$performanceData = [];

while ($product_row = $result_products->fetch_assoc()) {
    $product_id = $product_row['product_id'];
    $product_name = $product_row['product_name'];
    $product_safety_stock = $product_row['safety_stock'];
    $total_sales_quantity = $product_row['total_sales_quantity'];
    $total_purchase_quantity = $product_row['total_purchase_quantity'];

    // Calculate current stock
    $current_stock = $total_purchase_quantity - $total_sales_quantity;

    // Fetch latest purchase date
    $sql_purchase = "SELECT purchase_date FROM purchase WHERE product_id = ?";
    $stmt_purchase = $conn->prepare($sql_purchase);
    $stmt_purchase->bind_param("i", $product_id);
    $stmt_purchase->execute();
    $result_purchase = $stmt_purchase->get_result();
    $LatestPurchaseDate = null;
    while ($purchase_row = $result_purchase->fetch_assoc()) {
        $LatestPurchaseDate = $purchase_row['purchase_date'];
    }

    // Fetch latest sale date
    $sql_sales = "SELECT sale_date FROM sales WHERE product_id = ?";
    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("i", $product_id);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();
    $LatestSaleDate = null;
    while ($sales_row = $result_sales->fetch_assoc()) {
        $LatestSaleDate = $sales_row['sale_date'];
    }

    // Calculate average sales for this month
    $today_date = date('Y-m-d');
    $today_month = date('m', strtotime($today_date));
    $today_year = date('Y', strtotime($today_date));

    $sale_month = $sale_year = 'NA';
    if ($LatestSaleDate) {
        $sale_month = date('m', strtotime($LatestSaleDate));
        $sale_year = date('Y', strtotime($LatestSaleDate));
    }

    if ($sale_month == $today_month && $sale_year == $today_year) {
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $today_month, $today_year);
        $averageSalesMonth = round($total_sales_quantity / $days_in_month, 2);
    } else {
        $averageSalesMonth = 'NA';
    }

    // Calculate average lead time and ROL
    if ($averageSalesMonth !== 'NA' && $averageSalesMonth > 0) {
        if ($total_purchase_quantity % 2 <= $total_sales_quantity) {
            $day1 = date('d', strtotime($LatestSaleDate));
            $day2 = date('d', strtotime($LatestPurchaseDate));
            $averageLeadTime = $day1 - $day2;

            if ($averageLeadTime > 0) {
                $ROL = ($averageSalesMonth * $averageLeadTime) + $product_safety_stock;
            } else {
                $ROL = 'NA';
            }
        } else {
            $averageLeadTime = 'NA';
            $ROL = 'NA';
        }
    } else {
        $averageLeadTime = 'NA';
        $ROL = 'NA';
    }

    $performanceData[] = [
        'product_id' => $product_id,
        'product_name' => $product_name,
        'average_sales' => $averageSalesMonth,
        'average_lead_time' => $averageLeadTime,
        'safety_stock' => $product_safety_stock,
        'ROL' => $ROL,
        'current_stock' => $current_stock
    ];

    $stmt_purchase->close();
    $stmt_sales->close();
}

$conn->close();

echo json_encode($performanceData);
?>
