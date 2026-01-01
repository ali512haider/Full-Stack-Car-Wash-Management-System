<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Logged-in user details
$logged_user_id   = $_SESSION['user_id'];
$logged_user_role = $_SESSION['role']; // "admin" or "user"

// Filters from GET
$user_filter     = $_GET['user_id'] ?? 'all';
$location_filter = $_GET['location_id'] ?? 'all';
$payment_filter  = $_GET['payment_method'] ?? 'all';
$vehicle_search  = trim($_GET['vehicle'] ?? '');
$from            = $_GET['from'] ?? date('Y-m-d');
$to              = $_GET['to'] ?? date('Y-m-d');

// Base query
$query = "
    SELECT sr.*, l.location_name, u.username
    FROM service_report sr
    LEFT JOIN locations l ON sr.location_id = l.id
    LEFT JOIN users u ON sr.user_id = u.id
    WHERE sr.report_date BETWEEN :from AND :to
";

$params = [
    ':from' => $from,
    ':to'   => $to
];

// Restrict to only user's own records UNLESS admin
if ($logged_user_role !== 'admin') {
    $query .= " AND sr.user_id = :only_user";
    $params[':only_user'] = $logged_user_id;

    // IGNORE user_id GET filter for non-admins
    $user_filter = 'all';
}

// Optional Filters (allowed only for admin)
if ($logged_user_role === 'admin') {

    if ($user_filter != 'all') {
        $query .= " AND sr.user_id = :user";
        $params[':user'] = $user_filter;
    }

    if ($location_filter != 'all') {
        $query .= " AND sr.location_id = :location";
        $params[':location'] = $location_filter;
    }

    if ($payment_filter != 'all') {
        $query .= " AND sr.payment_method = :payment";
        $params[':payment'] = $payment_filter;
    }
}

// Vehicle search allowed for both roles
if (!empty($vehicle_search)) {
    $query .= " AND LOWER(sr.vehicle_number) LIKE :vehicle";
    $params[':vehicle'] = "%" . strtolower($vehicle_search) . "%";
}

$query .= " ORDER BY sr.report_date DESC";

// Execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV Export
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="service_report_' . date('d-m-Y') . '.csv"');

$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, ['#', 'Date', 'User', 'Vehicle', 'Customer', 'Location', 'Services', 'Total', 'Payment']);

$counter = 1;
foreach ($data as $r) {
    fputcsv($output, [
        $counter++,
        date('d-m-Y', strtotime($r['report_date'])),
        $r['username'],
        $r['vehicle_number'],
        $r['customer_mobile'],
        $r['location_name'],
        $r['services'],
        number_format($r['total_price'], 2),
        $r['payment_method']
    ]);
}

fclose($output);
exit;
?>
