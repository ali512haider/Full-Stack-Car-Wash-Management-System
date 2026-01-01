<?php
require '../middleware/admin_protect.php';
require '../config.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$report_id = $_GET['id'];

/* =======================================================
   1) Get the record from service_report
   ======================================================= */
$stmt = $conn->prepare("SELECT * FROM service_report WHERE id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Record not found.");
}

$user_id       = $report['user_id'];
$vehicle       = $report['vehicle_number'];
$mobile        = $report['customer_mobile'];
$location_id   = $report['location_id'];
$service_list  = explode(',', $report['services']);  // individual service names
$added_date          = $report['created_at'];
$added_time          = date('H:i');

/* =======================================================
   2) Insert each service back into unpaid_services
   ======================================================= */
foreach ($service_list as $service_name_raw) {

    $service_name = trim($service_name_raw);

    // find service id and price
    $s = $conn->prepare("SELECT id, price FROM services WHERE service_name = ?");
    $s->execute([$service_name]);
    $service = $s->fetch(PDO::FETCH_ASSOC);

    if (!$service) continue; // Skip if service name mismatch

    $service_id = $service['id'];
    $price      = $service['price'];

    // Insert back to unpaid_services
    $insert_unpaid = $conn->prepare("
        INSERT INTO unpaid_services 
        (user_id, service_id, vehicle_number, customer_mobile, location_id, price, added_date, added_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert_unpaid->execute([
        $user_id,
        $service_id,
        $vehicle,
        $mobile,
        $location_id,
        $price,
        $added_date,
        $added_time
    ]);
}

/* =======================================================
   3) Delete record from service_report
   ======================================================= */
$del = $conn->prepare("DELETE FROM service_report WHERE id = ?");
$del->execute([$report_id]);

/* =======================================================
   4) Redirect back
   ======================================================= */
header("Location: reports.php?msg=Restored successfully");
exit;

?>
