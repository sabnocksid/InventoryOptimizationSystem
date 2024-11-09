<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Product and Purchase Records</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/simplex/bootstrap.min.css">
    <style>
        /* CSS styles remain unchanged */
        .table-wrapper {
            margin: 20px auto;
            max-width: 80%;
            padding-bottom: 60px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #f8f9fa;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .highlight-red {
            background-color: #f8d7da;
        }

        .highlight-green {
            background-color: #d4edda;
        }

        .fixed-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <?php include 'C:/xampp/htdocs/WheelsIOS/InventoryManager/dashboard-contents/layout/navbar.php'; ?>

    <div class="container">
        <div class="table-wrapper">
            <h1 class="text-center mb-4">Reorder Level of Month <?php echo date('m'); ?></h1>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Average Sales</th>
                        <th>Average Lead Time</th>
                        <th>Safety Stock</th>
                        <th>Reorder Level</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Database credentials
                    $host = 'localhost';
                    $db = 'WheelsIOS';
                    $user = 'root';
                    $pass = '';

                    // User authentication check
                    if (!isset($_SESSION['user_id'])) {
                        header("Location: login.php");
                        exit();
                    }

                    $conn = new mysqli($host, $user, $pass, $db);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Enhanced SQL query with subqueries for latest purchase and sale dates
                    $sql_products = "
                        SELECT 
                            p.product_id,
                            p.product_name,
                            p.safety_stock,
                            COALESCE(s.total_sales_quantity, 0) AS total_sales_quantity,
                            COALESCE(pu.total_purchase_quantity, 0) AS total_purchase_quantity,
                            COALESCE(s.latest_sale_date, NULL) AS latest_sale_date,
                            COALESCE(pu.latest_purchase_date, NULL) AS latest_purchase_date
                        FROM 
                            product p
                        LEFT JOIN 
                            (SELECT product_id, SUM(quantity) AS total_sales_quantity, MAX(sale_date) AS latest_sale_date
                             FROM sales 
                             GROUP BY product_id) s 
                        ON p.product_id = s.product_id
                        LEFT JOIN 
                            (SELECT product_id, SUM(quantity) AS total_purchase_quantity, MAX(purchase_date) AS latest_purchase_date
                             FROM purchase 
                             GROUP BY product_id) pu 
                        ON p.product_id = pu.product_id;
                    ";
                    
                    $result_products = $conn->query($sql_products);

                    if ($result_products->num_rows > 0) {
                        while ($product_row = $result_products->fetch_assoc()) {
                            $product_id = $product_row['product_id'];
                            $product_name = htmlspecialchars($product_row['product_name']);
                            $product_safety_stock = $product_row['safety_stock'];
                            $total_sales_quantity = $product_row['total_sales_quantity'];
                            $total_purchase_quantity = $product_row['total_purchase_quantity'];
                            $latest_sale_date = $product_row['latest_sale_date'];
                            $latest_purchase_date = $product_row['latest_purchase_date'];
                            

                            // Calculate current stock
                            $current_stock = $total_purchase_quantity - $total_sales_quantity;

                            // Calculate average sales for this month
                            $today_month = date('m');
                            $today_year = date('Y');
                            $average_sales_month = 'NA';

                            if ($latest_sale_date && date('m', strtotime($latest_sale_date)) == $today_month && date('Y', strtotime($latest_sale_date)) == $today_year) {
                                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $today_month, $today_year);
                                $average_sales_month = round($total_sales_quantity / $days_in_month, 2);
                            }
                            // Calculate average lead time and ROL
                            $average_lead_time = 'NA';
                            $reorder_level = 'NA';
                            if ($average_sales_month !== 'NA' && $latest_purchase_date && $latest_sale_date) {
                                $lead_time_days = (strtotime($latest_sale_date) - strtotime($latest_purchase_date)) / (60 * 60 * 24);
                                if ($lead_time_days > 0) {
                                    $average_lead_time = round($lead_time_days, 2);
                                    $reorder_level = ($average_sales_month * $average_lead_time) + $product_safety_stock;
                                }
                            }

                            // Determine cell color for current stock
                            $highlight_class = ($current_stock < $reorder_level) ? 'highlight-red' : 'highlight-green';
                    ?>
                            <tr>
                                <td><?php echo $product_id; ?></td>
                                <td><?php echo $product_name; ?></td>
                                <td><?php echo is_numeric($average_sales_month) ? $average_sales_month : 'NA'; ?></td>
                                <td><?php echo is_numeric($average_lead_time) ? $average_lead_time : 'NA'; ?></td>
                                <td><?php echo $product_safety_stock; ?></td>
                                <td><?php echo is_numeric($reorder_level) ? $reorder_level : 'NA'; ?></td>
                                <td class="<?php echo $highlight_class; ?>"><?php echo $current_stock; ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7">No products found</td></tr>';
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
