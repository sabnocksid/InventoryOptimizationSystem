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
        body {
            padding-top: 56px; /* Space for the navbar */
        }
        .fixed-btn {
            position: fixed;
            bottom: 20px; /* Distance from the bottom */
            right: 20px; /* Distance from the right */
            z-index: 1000; /* Ensure it's on top of other content */
        }
        .fixed-btn a {
            color: white; /* Text color */
            text-decoration: none; /* Remove underline */
        }
        .fixed-btn a:hover {
            color: white; /* Ensure text color remains white on hover */
        }
        .highlight-red {
            background-color: #660000; /* Light red background */
        }
        .highlight-green {
            background-color: #004d00; 
        }
        .chart-container {
            margin: 20px auto;
            max-width: 80%;
            padding-bottom: 20px; /* Space between chart and table */
        }
        .chart-container canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<?php include '../layout/navbar.php'; ?>

<div class="container">
    <div class="chart-container">
        <h2 class="text-center">Product Stock Overview</h2>
        <canvas id="stockChart"></canvas>
    </div>

    <div class="table-wrapper">
    <h6 class="text-center mb-4">Stock Performance of <?php echo date('F'); ?></h6>
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
                ob_start();
                include("../algorithhm/productPerformance.php");
                $jsonData = ob_get_clean();


                $performanceData = json_decode($jsonData, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    die('JSON decode error: ' . json_last_error_msg());
                }

                if (!empty($performanceData)) {
                    foreach ($performanceData as $data) {
                        $highlight_class = ($data['current_stock'] < $data['ROL']) ? 'highlight-red' : 'highlight-green';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($data['product_name']); ?></td>
                    <td><?php echo $data['average_sales'] !== 'NA' ? $data['average_sales'] : 'NA'; ?> Units</td>
                    <td><?php echo $data['average_lead_time'] !== 'NA' ? $data['average_lead_time'] : 'NA'; ?> days</td>
                    <td><?php echo $data['safety_stock']; ?> Units</td>
                    <td><?php echo $data['ROL'] !== 'NA' ? $data['ROL'] : 'NA'; ?> Units</td>
                    <td class="<?php echo $highlight_class; ?>"><?php echo $data['current_stock']; ?> Units</td>
                </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="7">No products found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="fixed-btn">
        <button type="button" class="btn btn-info">
            <a href="../algorithhm/safety_stock.php">Safety Stock</a>
        </button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('stockChart').getContext('2d');
        
        <?php
        echo 'var performanceData = ' . $jsonData . ';';
        ?>

        console.log(performanceData); 

        var labels = performanceData.map(item => item.product_id);
        var currentStock = performanceData.map(item => item.current_stock);
        var ROL = performanceData.map(item => item.ROL);
        
        var stockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Current Stock',
                    data: currentStock,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Reorder Level',
                    data: ROL,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Stock Amount'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Product ID'
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>
