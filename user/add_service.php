<?php
require '../middleware/user_protect.php';
require '../config.php';

$user_id = $_SESSION['user_id'];

// ========================
// FETCH LOCATIONS
// ========================
$locations_stmt = $conn->prepare("
    SELECT * FROM locations 
    WHERE id = (SELECT location_id FROM users WHERE id = ?)
");
$locations_stmt->execute([$user_id]);
$locations = $locations_stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================
// FETCH ALL SERVICES
// ========================
$services = $conn->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

// ========================
// 1) ADD SERVICES TO unpaid_services
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {

    $vehicle = $_POST['vehicle_number'];
    $mobile = $_POST['customer_mobile'] ?: null;
    $location_id = $_POST['location_id'];
    $service_ids = $_POST['service_id'] ?? [];

    $service_date = $_POST['service_date'];   // ✅ SERVICE DATE
    $time = date('H:i');

    foreach ($service_ids as $sid) {

        $pstmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
        $pstmt->execute([$sid]);
        $price = $pstmt->fetchColumn();

        $stmt = $conn->prepare("
            INSERT INTO unpaid_services 
            (user_id, service_id, vehicle_number, customer_mobile, location_id, price, added_date, added_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $sid,
            $vehicle,
            $mobile,
            $location_id,
            $price,
            $service_date, // ✅ stored as service date
            $time
        ]);
    }

    $message = "Services added successfully! Now select payment method below.";
}

// ========================
// 2) PAY UNPAID SERVICES → MOVE TO service_report
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now_group'])) {

    $row_ids = $_POST['row_id'];
    $payment_method = $_POST['payment_method'];

    $total_price = 0;
    $vehicle = '';
    $mobile = '';
    $location_id = '';
    $service_names = [];

    $service_date = null;            // ✅ SERVICE DATE (from unpaid)
    $payment_date = date('Y-m-d');   // ✅ PAYMENT DATE (today)

    foreach ($row_ids as $row_id) {

        $stmt = $conn->prepare("SELECT * FROM unpaid_services WHERE id = ?");
        $stmt->execute([$row_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {

            $total_price += $row['price'];
            $vehicle = $row['vehicle_number'];
            $mobile = $row['customer_mobile'];
            $location_id = $row['location_id'];

            // ✅ Take service date from unpaid_services
            $service_date = $row['added_date'];

            $sstmt = $conn->prepare("SELECT service_name FROM services WHERE id = ?");
            $sstmt->execute([$row['service_id']]);
            $service_names[] = $sstmt->fetchColumn();

            $d_stmt = $conn->prepare("DELETE FROM unpaid_services WHERE id = ?");
            $d_stmt->execute([$row_id]);
        }
    }

    // ✅ INSERT BOTH SERVICE DATE + PAYMENT DATE
    $r_stmt = $conn->prepare("
        INSERT INTO service_report
        (user_id, vehicle_number, customer_mobile, location_id, total_price, payment_method, service_date, report_date, services)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $r_stmt->execute([
        $user_id,
        $vehicle,
        $mobile,
        $location_id,
        $total_price,
        $payment_method,
        $service_date,  // ✅ SERVICE DATE
        $payment_date,  // ✅ PAYMENT DATE
        implode(", ", $service_names)
    ]);

    $message = "Payment completed for vehicle $vehicle!";
}

// ========================
// 3) FETCH UNPAID SERVICES
// ========================
$unpaid_stmt = $conn->prepare("
    SELECT u.*, s.service_name 
    FROM unpaid_services u
    JOIN services s ON u.service_id = s.id
    WHERE u.user_id = ?
    ORDER BY u.added_date DESC, u.added_time DESC
");
$unpaid_stmt->execute([$user_id]);
$unpaid = $unpaid_stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Add Service Provided</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .sidebar {
            width: 220px;
            position: fixed;
            top:0; bottom:0; left:0;
            background:#343a40;
            color:white;
            padding-top:20px;
        }
        .sidebar a {
            color:white;
            display:block;
            padding:10px;
            text-decoration:none;
        }
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
    <h2>Add Service Provided</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- ADD SERVICE FORM -->
    <form method="POST">

        <div class="mb-3">
            <label><strong>Select Service</strong></label>
            <select id="serviceDropdown" class="form-control">
                <option value="">Search or select service...</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['id'] ?>" data-price="<?= $s['price'] ?>">
                        <?= $s['service_name'] ?> — <?= number_format($s['price'],3) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="button" class="btn btn-success mb-3" onclick="addService()">+ Add Service</button>

        <table class="table table-bordered" id="serviceTable">
            <thead class="table-dark">
                <tr>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <h4>Total: <span id="totalPrice">0.000</span></h4>

        <hr>

        <div class="mb-2">
            <label>Vehicle Number</label>
            <input type="text" name="vehicle_number" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Customer Mobile</label>
            <input type="text" name="customer_mobile" class="form-control">
        </div>

        <div class="mb-2">
            <label>Service Date</label>
            <input type="date" id="service_date" name="service_date" class="form-control" readonly required>
        </div>

        <div class="mb-2">
            <label>Location</label>
            <select name="location_id" class="form-control" required>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>"><?= $loc['location_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" name="final_submit" value="1">
        <button class="btn btn-primary mt-3">Submit All</button>
    </form>

    <hr>
    <h3>Unpaid Services</h3>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Vehicle</th>
                <th>Service Date</th>
                <th>Mobile</th>
                <th>Services</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Submit</th>
            </tr>
        </thead>
        <tbody>
<?php
$grouped = [];

foreach ($unpaid as $u) {
    $grouped[$u['vehicle_number']][$u['added_date']]['mobile'] = $u['customer_mobile'];
    $grouped[$u['vehicle_number']][$u['added_date']]['services'][$u['service_name']] =
        ($grouped[$u['vehicle_number']][$u['added_date']]['services'][$u['service_name']] ?? 0) + $u['price'];
    $grouped[$u['vehicle_number']][$u['added_date']]['total'] =
        ($grouped[$u['vehicle_number']][$u['added_date']]['total'] ?? 0) + $u['price'];
    $grouped[$u['vehicle_number']][$u['added_date']]['ids'][] = $u['id'];
}

foreach ($grouped as $vehicle => $dates):
foreach ($dates as $date => $row):
?>
<tr>
<form method="POST">
<td><?= $vehicle ?></td>
<td><?= date("d-m-Y", strtotime($date)) ?></td>
<td><?= $row['mobile'] ?: '—' ?></td>
<td>
<?php foreach ($row['services'] as $name => $price): ?>
    <?= $name ?> (<?= number_format($price,3) ?>)<br>
<?php endforeach; ?>
</td>
<td><?= number_format($row['total'],3) ?></td>
<td>
<select name="payment_method" class="form-select" required>
    <option value="Cash">Cash</option>
    <option value="Bank">Bank</option>
</select>
</td>
<td>
<?php foreach ($row['ids'] as $id): ?>
    <input type="hidden" name="row_id[]" value="<?= $id ?>">
<?php endforeach; ?>
<button name="pay_now_group" class="btn btn-success btn-sm">Submit</button>
</td>
</form>
</tr>
<?php endforeach; endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $("#serviceDropdown").select2({ width:"100%" });
});

function addService() {
    const d = document.getElementById("serviceDropdown");
    if (!d.value) return alert("Select service");

    const name = d.options[d.selectedIndex].text;
    const price = parseFloat(d.options[d.selectedIndex].dataset.price);

    document.querySelector("#serviceTable tbody").insertAdjacentHTML("beforeend", `
        <tr>
            <td>${name}<input type="hidden" name="service_id[]" value="${d.value}"></td>
            <td class="price">${price.toFixed(3)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();updateTotal()">X</button></td>
        </tr>
    `);
    updateTotal();
}

function updateTotal() {
    let sum = 0;
    document.querySelectorAll(".price").forEach(p => sum += parseFloat(p.innerText));
    document.getElementById("totalPrice").innerText = sum.toFixed(3);
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("service_date").value =
        new Date().toISOString().split("T")[0];
});
</script>

</body>
</html>
