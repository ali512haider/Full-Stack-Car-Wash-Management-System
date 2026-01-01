<?php
require '../middleware/user_protect.php';
require '../config.php';

$user_id = $_SESSION['user_id'];

// ==============================
// FILTER INPUTS (TRANSACTION DATE)
// ==============================

$payment_filter = $_GET['payment_method'] ?? 'all';
$vehicle_search = trim($_GET['vehicle'] ?? '');

$from_input = $_GET['from'] ?? date('Y-m-d');
$to_input   = $_GET['to']   ?? date('Y-m-d');

// Normalize dates
$from = DateTime::createFromFormat('Y-m-d', $from_input)
        ? $from_input
        : DateTime::createFromFormat('d-m-Y', $from_input)->format('Y-m-d');

$to = DateTime::createFromFormat('Y-m-d', $to_input)
        ? $to_input
        : DateTime::createFromFormat('d-m-Y', $to_input)->format('Y-m-d');

// ==============================
// FETCH REPORT DATA
// ==============================

$query = "
    SELECT 
        sr.id,
        sr.vehicle_number,
        sr.customer_mobile,
        sr.service_date,
        sr.report_date AS transaction_date,
        l.location_name,
        sr.payment_method,
        sr.services,
        sr.total_price
    FROM service_report sr
    LEFT JOIN locations l ON sr.location_id = l.id
    WHERE sr.user_id = :uid
      AND sr.report_date BETWEEN :from AND :to
";

$params = [
    ':uid'  => $user_id,
    ':from' => $from,
    ':to'   => $to
];

if ($payment_filter !== 'all') {
    $query .= " AND sr.payment_method = :payment";
    $params[':payment'] = $payment_filter;
}

if (!empty($vehicle_search)) {
    $query .= " AND LOWER(sr.vehicle_number) LIKE :vehicle";
    $params[':vehicle'] = '%' . strtolower($vehicle_search) . '%';
}

$query .= " ORDER BY sr.report_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// CALCULATE TOTALS
// ==============================

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
    <title>Service Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { width:220px; position:fixed; top:0; bottom:0; left:0; background:#343a40; color:#fff; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
        th { background:#212529; color:white; }
    </style>
</head>

<body>

<div class="sidebar">
    <h4 class="text-center">User Panel</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_service.php">Add Service Provided</a>
    <a href="reports.php">Service History</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h2>Service Report</h2>

    <!-- FILTER FORM -->
    <div class="card p-3 mb-4">
        <form method="GET" class="row g-3">

            <div class="col-md-2">
                <label>From (Transaction)</label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>">
            </div>

            <div class="col-md-2">
                <label>To (Transaction)</label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>">
            </div>

            <div class="col-md-2">
                <label>Payment</label>
                <select name="payment_method" class="form-control">
                    <option value="all">All</option>
                    <option value="Cash" <?= $payment_filter=='Cash'?'selected':'' ?>>Cash</option>
                    <option value="Bank" <?= $payment_filter=='Bank'?'selected':'' ?>>Bank</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Vehicle</label>
                <input type="text" name="vehicle" class="form-control" value="<?= htmlspecialchars($vehicle_search) ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>

        </form>
    </div>

    <!-- TOTALS -->
    <div class="row mb-4">
        <div class="col-md-4 alert alert-success">Cash: <b><?= number_format($total_cash,3) ?></b></div>
        <div class="col-md-4 alert alert-info">Bank: <b><?= number_format($total_bank,3) ?></b></div>
        <div class="col-md-4 alert alert-dark">Total: <b><?= number_format($grand_total,3) ?></b></div>
    </div>

    <!-- REPORT TABLE -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Service Date</th>
                <th>Transaction Date</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Location</th>
                <th>Services</th>
                <th>Total</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($report_data): foreach ($report_data as $r): ?>
            <tr>
                <td><?= date('d-m-Y', strtotime($r['service_date'])) ?></td>
                <td><?= date('d-m-Y', strtotime($r['transaction_date'])) ?></td>
                <td><?= htmlspecialchars($r['vehicle_number']) ?></td>
                <td><?= htmlspecialchars($r['customer_mobile'] ?: '—') ?></td>
                <td><?= htmlspecialchars($r['location_name']) ?></td>
                <td>
                    <?php foreach (explode(',', $r['services']) as $s): ?>
                        <?= htmlspecialchars(trim($s)) ?><br>
                    <?php endforeach; ?>
                </td>
                <td><?= number_format($r['total_price'],3) ?></td>
                <td>
                    <span class="badge <?= $r['payment_method']=='Cash'?'bg-success':'bg-info' ?>">
                        <?= $r['payment_method'] ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="8" class="text-center">No records found</td></tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>
</body>
</html>
