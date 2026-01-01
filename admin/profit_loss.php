<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Date filter
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// Load all locations
$loc_stmt = $conn->prepare("SELECT id, location_name FROM locations ORDER BY location_name ASC");
$loc_stmt->execute();
$locations = $loc_stmt->fetchAll(PDO::FETCH_ASSOC);

// Selected location
$selected_location = isset($_GET['location']) && $_GET['location'] !== "all" ? intval($_GET['location']) : "all";

// Prepare base params
$sales_params = [$from, $to];
$purchase_params = [$from, $to];

// Location filter SQL
$sales_filter_sql = "";
$purchase_filter_sql = "";

if ($selected_location != "all") {
    $sales_filter_sql = " AND sr.location_id = ?";
    $sales_params[] = $selected_location;

    $purchase_filter_sql = " AND p.location_id = ?";
    $purchase_params[] = $selected_location;
}

// FETCH SALES (keyed by location_id)
$sales_sql = "
    SELECT sr.location_id, SUM(sr.total_price) AS total_sales
    FROM service_report sr
    WHERE sr.report_date BETWEEN ? AND ? $sales_filter_sql
    GROUP BY sr.location_id
";
$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->execute($sales_params);
$sales_data = [];
foreach ($sales_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $sales_data[$row['location_id']] = $row['total_sales'];
}

// FETCH PURCHASES (keyed by location_id)
$purchase_sql = "
    SELECT p.location_id, SUM(p.amount * p.quantity) AS total_purchases
    FROM purchases p
    WHERE p.purchase_date BETWEEN ? AND ? $purchase_filter_sql
    GROUP BY p.location_id
";
$purchase_stmt = $conn->prepare($purchase_sql);
$purchase_stmt->execute($purchase_params);
$purchase_data = [];
foreach ($purchase_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $purchase_data[$row['location_id']] = $row['total_purchases'];
}

// Initialize totals
$total_sales_sum = 0;
$total_purchase_sum = 0;
$total_profit_sum = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profit & Loss Report</title>
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
    <h2>Profit & Loss Report</h2>

    <!-- FILTER FORM -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-auto">
            <label>From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
        </div>

        <div class="col-auto">
            <label>To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
        </div>

        <div class="col-md-2">
            <label>Select Location</label>
            <select name="location" class="form-control" onchange="this.form.submit()">
                <option value="all" <?= $selected_location === "all" ? "selected" : "" ?>>All Locations</option>
                <?php foreach ($locations as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= ($selected_location == $l['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['location_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto mt-4">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- REPORT TABLE -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Location</th>
                <th>Total Sales</th>
                <th>Total Purchases</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $loc):

                if ($selected_location != "all" && $loc['id'] != $selected_location) continue;

                $loc_id = $loc['id'];
                $loc_name = $loc['location_name'];
                $total_sales = $sales_data[$loc_id] ?? 0;
                $total_purchases = $purchase_data[$loc_id] ?? 0;
                $profit = $total_sales - $total_purchases;

                $total_sales_sum += $total_sales;
                $total_purchase_sum += $total_purchases;
                $total_profit_sum += $profit;
            ?>
            <tr>
                <td><?= htmlspecialchars($loc_name) ?></td>
                <td><?= number_format($total_sales, 3) ?></td>
                <td><?= number_format($total_purchases, 3) ?></td>
                <td><strong><?= number_format($profit, 3) ?></strong></td>
            </tr>
            <?php endforeach; ?>

            <!-- TOTAL ROW -->
            <tr class="table-secondary">
                <td class="text-center"><strong>Total</strong></td>
                <td><strong><?= number_format($total_sales_sum, 3) ?></strong></td>
                <td><strong><?= number_format($total_purchase_sum, 3) ?></strong></td>
                <td><strong><?= number_format($total_profit_sum, 3) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>
