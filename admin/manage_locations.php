<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

// Fetch all locations
$stmt = $conn->prepare("SELECT * FROM locations ORDER BY id ASC");
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Locations</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ffffff;
            padding: 30px;
        }

        .container {
            background: white;
            width: 800px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #aaa;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        a.add-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        a.add-btn:hover {
            background: #1e7e34;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #bbb;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        .actions a {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 3px;
            color: white;
        }

        .edit-btn { background: #ffc107; color: black; }
        .delete-btn { background: #dc3545; }

        .edit-btn:hover { background: #e0a800; }
        .delete-btn:hover { background: #bd2130; }
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

    <h2>📍 Manage Locations</h2>

    <a class="add-btn" href="add_location.php">➕ Add New Location</a>

    <table>
    <tr>
        <th>ID</th>
        <th>Location Name</th>
        <th>Actions</th>
    </tr>

    <?php $counter = 1; ?>
    <?php foreach ($locations as $loc): ?>
    <tr>
        <td><?= $counter++ ?></td> <!-- Display consecutive number -->
        <td><?= $loc['location_name'] ?></td>
        <td class="actions">
            <a class="edit-btn" href="edit_location.php?id=<?= $loc['id'] ?>">✏ Edit</a>
            <a class="delete-btn" href="delete_location.php?id=<?= $loc['id'] ?>"
               onclick="return confirm('Are you sure you want to delete this location?')">🗑 Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>


</div>

</body>
</html>
