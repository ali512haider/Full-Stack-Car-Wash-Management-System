<?php
require '../middleware/user_protect.php';
require '../config.php';


$user_id = $_SESSION['user_id'];
// Fetch logged-in user name
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$logged_user = $user_stmt->fetchColumn();
// Fetch user stats
$total_services_done = $conn->prepare("SELECT COUNT(*) FROM service_report WHERE user_id=?");
$total_services_done->execute([$user_id]);
$total_services_done = $total_services_done->fetchColumn();

$total_today_services = $conn->prepare("SELECT COUNT(*) FROM service_report WHERE user_id=? AND report_date=CURDATE()");
$total_today_services->execute([$user_id]);
$total_today_services = $total_today_services->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { width: 220px; position: fixed; top:0; bottom:0; left:0; background:#343a40; color:white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
    </style>
</head>
<body class="bg-light"> 

<div class="sidebar">
    <h4 class="text-center">User Panel</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_service.php">Add Service Provided</a>
    <a href="reports.php">Service History</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h2>Welcome, <?= htmlspecialchars($logged_user) ?> 👋</h2>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Services Done</h5>
                    <p class="card-text"><?= $total_services_done ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Today's Services</h5>
                    <p class="card-text"><?= $total_today_services ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3>Quick Links</h3>
    <div class="row mt-3">
        <div class="col-md-6">
            <a href="add_service.php" class="btn btn-primary w-100">Add Service Provided</a>
        </div>
        <div class="col-md-6">
            <a href="reports.php" class="btn btn-success w-100">View Service History</a>
        </div>
    </div>
</div>

</body>
</html>
