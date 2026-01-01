<?php
require '../config.php';
session_start();

// Protect admin
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

// Fetch all services
$stmt = $conn->prepare("SELECT * FROM services ORDER BY id ASC");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .table thead { background-color: #343a40; color: white; }
        .btn-action { margin-right: 5px; }
        h2 { margin-bottom: 20px; }
        .sidebar { width: 220px; position: fixed; top:0; bottom:0; left:0; background:#343a40; color:white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
    </style>
</head>
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
    <h2>Manage Services</h2>
    <a href="add_service.php" class="btn btn-success mb-3">➕ Add New Service</a>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php $counter = 1; ?>
    <?php if(count($services) > 0): ?>
        <?php foreach($services as $row): ?>
            <tr>
                <td><?= $counter++ ?></td> <!-- Display consecutive number -->
                <td><?= htmlspecialchars($row['service_name']) ?></td>
                <td><?= number_format($row['price'], 3) ?></td>
                <td>
                    <a href="edit_service.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm btn-action">✏ Edit</a>
                    <a href="delete_service.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this service?');">🗑 Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" class="text-center">No services found.</td>
        </tr>
    <?php endif; ?>
</tbody>

            </table>
        </div>
    </div>
</div>

</body>
</html>
