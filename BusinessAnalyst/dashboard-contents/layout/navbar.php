<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/cyborg/bootstrap.min.css">
</head>
<body>

<?php
$projectRoot = '/wheelsIOS/BusinessAnalyst/'; // Adjust this to match your project's URL structure
$dashboardURL = $projectRoot . 'dashboard-contents/landing.php';
$salesURL = $projectRoot . 'dashboard-contents/analyser/salesinfo.php';
$stockURL = $projectRoot . 'dashboard-contents/analyser/stocklevel.php';
$messageURL = $projectRoot . 'dashboard-contents/messages/messageInvManager.php';
$logoutURL = $projectRoot . '../logout.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $dashboardURL; ?>">Wheels</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'landing.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $dashboardURL; ?>">Purchases</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'salesinfo.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $salesURL; ?>">Sales</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'stocklevel.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $stockURL; ?>">Stock</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'messageInvManager.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $messageURL; ?>">Message</a>
                </li>
               
                
            </ul>
            <div class="ml-auto">
                <span class="badge badge-success">
                    <a href="<?php echo $logoutURL; ?>" class="text-white text-decoration-none">Logout</a>
                </span>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JavaScript and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
