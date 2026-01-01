<?php
require '../middleware/admin_protect.php';
require '../config.php';
// Fetch logged-in admin name
$admin_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$admin_stmt->execute([$_SESSION['user_id']]);
$admin = $admin_stmt->fetchColumn();

// Fetch quick stats
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_services = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
$total_purchases = $conn->query("SELECT COUNT(*) FROM purchases")->fetchColumn();
$total_appointments = $conn->query("SELECT COUNT(*) FROM service_report")->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { width: 220px; position: fixed; top:0; bottom:0; left:0; background:#343a40; color:white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center">Admin Panel</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="purchases.php">Purchases</a>
    <a href="reports.php">Reports</a>
    <a href="profit_loss.php">Profit/Loss</a>
    <a href="unpaid_services.php">Unpaid Services</a>
    <a href="users.php">Manage Users</a>
    <a href="manage_locations.php">Manage Location</a>
    <a href="services.php">Manage Services</a>
    <a href="manage_purchase_items.php">Purchase Items</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h2>Welcome, <?= htmlspecialchars($admin) ?> 👋</h2>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text"><?= $total_users ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
                    <p class="card-text"><?= $total_services ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Purchases</h5>
                    <p class="card-text"><?= $total_purchases ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Services Done</h5>
                    <p class="card-text"><?= $total_appointments ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3>Quick Links</h3>
    <div class="row mt-3">
        <div class="col-md-3">
            <a href="users.php" class="btn btn-primary w-100">Manage Users</a>
        </div>
        <div class="col-md-3">
            <a href="services/services.php" class="btn btn-success w-100">Manage Services</a>
        </div>
        <div class="col-md-3">
            <a href="purchases.php" class="btn btn-warning w-100">Manage Purchases</a>
        </div>
        <div class="col-md-3">
            <a href="reports.php" class="btn btn-info w-100">Reports</a>
        </div>
    </div>

</div>

</body>
</html>
