<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['service_name'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO services (service_name, price) VALUES (?, ?)");

    if ($stmt->execute([$name, $price])) {
        header("Location: services.php");
        exit;
    } else {
        $message = "Error: Could not save service.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Service</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7fa;
        }
        .sidebar {
            width: 220px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            background: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .content {
            margin-left: 240px;
            padding: 30px;
        }
    </style>
</head>

<body>

<!-- Sidebar -->
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

<!-- Content -->
<div class="content">
    <div class="container">

        <div class="card shadow-lg" style="max-width: 650px; margin:auto;">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">Add New Service</h4>
            </div>

            <div class="card-body">

                <?php if ($message): ?>
                    <div class="alert alert-danger"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" name="service_name" class="form-control" placeholder="Enter service name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="Enter price" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">+ Add Service</button>

                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>
