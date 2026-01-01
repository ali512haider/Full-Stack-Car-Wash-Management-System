<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Fetch users & locations
$users = $conn->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
$locations = $conn->query("SELECT id, location_name FROM locations ORDER BY location_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Inputs
$user_filter     = $_GET['user_id']        ?? 'all';
$location_filter = $_GET['location_id']    ?? 'all';
$payment_filter  = $_GET['payment_method'] ?? 'all';
$vehicle_search  = trim($_GET['vehicle']   ?? '');

$from_input = $_GET['from'] ?? date('Y-m-d');
$to_input   = $_GET['to']   ?? date('Y-m-d');

// Convert date to Y-m-d safely
function toMySQLDate($date) {
    $formats = ['Y-m-d', 'd-m-Y'];
    foreach ($formats as $f) {
        $d = DateTime::createFromFormat($f, $date);
        if ($d) return $d->format('Y-m-d');
    }
    return date('Y-m-d');
}

$from = toMySQLDate($from_input);
$to   = toMySQLDate($to_input);

// BASE QUERY
$query = "
    SELECT sr.*, l.location_name, u.username
    FROM service_report sr
    LEFT JOIN locations l ON sr.location_id = l.id
    LEFT JOIN users u ON sr.user_id = u.id
    WHERE sr.report_date BETWEEN :from AND :to
";

// PARAMS
$params = [
    ':from' => $from,
    ':to'   => $to
];

// FILTERS
if ($user_filter != 'all') {
    $query .= " AND sr.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}

if ($location_filter != 'all') {
    $query .= " AND sr.location_id = :location_id";
    $params[':location_id'] = $location_filter;
}

if ($payment_filter != 'all') {
    $query .= " AND sr.payment_method = :payment_method";
    $params[':payment_method'] = $payment_filter;
}

if (!empty($vehicle_search)) {
    $query .= " AND LOWER(sr.vehicle_number) LIKE :vehicle";
    $params[':vehicle'] = "%" . strtolower($vehicle_search) . "%";
}

// FINAL ORDER
$query .= " ORDER BY sr.report_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// TOTALS
$total_cash = $total_bank = 0;
foreach ($report_data as $r) {
    if ($r['payment_method'] === 'Cash') $total_cash += $r['total_price'];
    if ($r['payment_method'] === 'Bank') $total_bank += $r['total_price'];
}
$grand_total = $total_cash + $total_bank;


?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Service Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .sidebar { width: 220px; position: fixed; top:0; bottom:0; left:0; background:#343a40; color:white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
        th { background:#212529; color:white; }
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
    <h2>Admin Service Report</h2>

    <!-- FILTER FORM -->
    <div class="card p-3 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label>User</label>
                <select name="user_id" class="form-control">
                    <option value="all">All Users</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($user_filter == $u['id'])?'selected':'' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>Location</label>
                <select name="location_id" class="form-control">
                    <option value="all">All Locations</option>
                    <?php foreach ($locations as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ($location_filter == $l['id'])?'selected':'' ?>>
                            <?= htmlspecialchars($l['location_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>From</label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>">
            </div>

            <div class="col-md-2">
                <label>To</label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>">
            </div>

            <div class="col-md-2">
                <label>Payment</label>
                <select name="payment_method" class="form-control">
                    <option value="all" <?= $payment_filter=='all'?'selected':'' ?>>All</option>
                    <option value="Cash" <?= $payment_filter=='Cash'?'selected':'' ?>>Cash</option>
                    <option value="Bank" <?= $payment_filter=='Bank'?'selected':'' ?>>Bank</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>Vehicle</label>
                <input type="text" name="vehicle" class="form-control" value="<?= htmlspecialchars($vehicle_search) ?>">
            </div>

            <div class="col-md-12 mt-2 d-flex gap-2">
                <button class="btn btn-primary w-100">Filter</button>

                <a href="download_report_excel.php?from=<?= $from ?>&to=<?= $to ?>&user_id=<?= $user_filter ?>&location_id=<?= $location_filter ?>&payment_method=<?= $payment_filter ?>&vehicle=<?= $vehicle_search ?>" 
                   class="btn btn-success w-100">
                    Download Excel
                </a>
            </div>
        </form>
    </div>

    <!-- TOTALS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="alert alert-success">Total Cash: <b><?= number_format($total_cash,3) ?></b></div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-info">Total Bank: <b><?= number_format($total_bank,3) ?></b></div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-dark">Grand Total: <b><?= number_format($grand_total,3) ?></b></div>
        </div>
    </div>

    <!-- REPORT TABLE -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>User</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Location</th>
                <th>Services</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if (count($report_data) > 0): $i=1; ?>
            <?php foreach ($report_data as $r): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= date('d-m-Y', strtotime($r['report_date'])) ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= htmlspecialchars($r['vehicle_number']) ?></td>
                    <td><?= htmlspecialchars($r['customer_mobile']) ?></td>
                    <td><?= htmlspecialchars($r['location_name']) ?></td>
                    <td>
                        <?php foreach (explode(',', $r['services']) as $s): ?>
                            <?= htmlspecialchars(trim($s)) ?><br>
                        <?php endforeach; ?>
                    </td>
                    <td><?= number_format($r['total_price'],3) ?></td>
                    <td>
                        <span class="badge <?= $r['payment_method']=='Cash'?'bg-success':'bg-info' ?>">
                            <?= htmlspecialchars($r['payment_method']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_report.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="restore_report.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary"
   onclick="return confirm('Restore this entry back to unpaid services?');">
   Restore
</a>
                        <a href="delete_report.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10" class="text-center">No records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
