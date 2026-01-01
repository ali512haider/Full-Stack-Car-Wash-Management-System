<?php
require '../middleware/admin_protect.php';
require '../config.php';

$message = "";

/* ================================================================
   1) FETCH ALL UNPAID SERVICES (Admin View)
   ================================================================ */
$unpaid_stmt = $conn->prepare("
    SELECT u.*, s.service_name, l.location_name, usr.username 
    FROM unpaid_services u
    JOIN services s ON u.service_id = s.id
    JOIN locations l ON u.location_id = l.id
    JOIN users usr ON u.user_id = usr.id
    ORDER BY u.added_date DESC, u.added_time DESC
");
$unpaid_stmt->execute();
$unpaid = $unpaid_stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================================================
   2) DELETE SINGLE UNPAID ROW
   ================================================================ */
if (isset($_GET['delete_id'])) {
    $del = $conn->prepare("DELETE FROM unpaid_services WHERE id = ?");
    $del->execute([$_GET['delete_id']]);
    $message = "Entry deleted successfully!";
    header("Location: unpaid_services.php");
    exit;
}


/* ================================================================
   3) ADMIN PAYMENT PROCESSING
   ================================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pay'])) {

    $row_ids = $_POST['row_id'] ?? [];
    $payment_method = $_POST['payment_method'];

    $total_price = 0;
    $vehicle = '';
    $mobile = '';
    $location_id = 0;
    $service_names = [];
    $user_id = 0;
    $added_date = date('Y-m-d');

    foreach ($row_ids as $rid) {

        $stmt = $conn->prepare("SELECT * FROM unpaid_services WHERE id = ?");
        $stmt->execute([$rid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) continue;

        $total_price += $row['price'];
        $vehicle = $row['vehicle_number'];
        $mobile = $row['customer_mobile'];
        $location_id = $row['location_id'];
        $user_id = $row['user_id'];
        $added_date = $row['added_date'];

        $s = $conn->prepare("SELECT service_name FROM services WHERE id = ?");
        $s->execute([$row['service_id']]);
        $service_names[] = $s->fetchColumn();

        $del = $conn->prepare("DELETE FROM unpaid_services WHERE id = ?");
        $del->execute([$rid]);
    }

    $ins = $conn->prepare("
        INSERT INTO service_report 
        (user_id, vehicle_number, customer_mobile, location_id, total_price, 
        payment_method, report_date, services)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([
        $user_id,
        $vehicle,
        $mobile,
        $location_id,
        $total_price,
        $payment_method,
        $added_date,
        implode(', ', $service_names)
    ]);

header("Location: reports.php");
exit;
}


/* ================================================================
   GROUPING LOGIC
   ================================================================ */

$group = [];

foreach ($unpaid as $u) {

    $vehicle = $u['vehicle_number'];
    $date = $u['added_date'];

    if (!isset($group[$vehicle])) {
        $group[$vehicle] = [];
    }

    if (!isset($group[$vehicle][$date])) {
        $group[$vehicle][$date] = [
            'date' => $date,
            'vehicle' => $vehicle,
            'user' => $u['username'],
            'location' => $u['location_name'],
            'services' => [],
            'total' => 0,
            'ids' => [],
        ];
    }

    $service = $u['service_name'];
    $price = $u['price'];

    // If service already added → add price only
    if (isset($group[$vehicle][$date]['services'][$service])) {
        $group[$vehicle][$date]['services'][$service] += $price;
    } else {
        $group[$vehicle][$date]['services'][$service] = $price;
    }

    $group[$vehicle][$date]['total'] += $price;
    $group[$vehicle][$date]['ids'][] = $u['id'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Unpaid Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <style>
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

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <h3>Unpaid Services (Admin)</h3>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Vehicle</th>
                <th>Services</th>
                <th>Total</th>
                <th>Added By</th>
                <th>Location</th>
                <th>Payment Method</th>
                <th>Submit</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>

<tbody>
<?php foreach ($group as $vehicle => $dates): ?>
    <?php foreach ($dates as $dateKey => $data): ?>

        <tr>
            <form method="POST">

                <!-- DATE -->
                <td><?= date('d-m-Y', strtotime($data['date'])) ?></td>

                <!-- VEHICLE -->
                <td><?= $vehicle ?></td>

                <!-- SERVICES (service => price) -->
                <td>
                    <?php foreach ($data['services'] as $service => $price): ?>
                        <?= $service ?> - <b><?= number_format($price, 3) ?></b><br>
                    <?php endforeach; ?>
                </td>

                <!-- TOTAL -->
                <td><b><?= number_format($data['total'], 3) ?></b></td>

                <!-- USER -->
                <td><?= $data['user'] ?></td>

                <!-- LOCATION -->
                <td><?= $data['location'] ?></td>

                <!-- PAYMENT METHOD -->
                <td>
                    <select name="payment_method" class="form-select" required>
                        <option value="Cash">Cash</option>
                        <option value="Bank">Bank</option>
                    </select>
                </td>

                <!-- HIDDEN IDs -->
                <?php foreach ($data['ids'] as $id): ?>
                    <input type="hidden" name="row_id[]" value="<?= $id ?>">
                <?php endforeach; ?>

                <!-- PAY BUTTON -->
                <td>
                    <button type="submit" name="admin_pay" class="btn btn-success btn-sm">
                        Pay
                    </button>
                </td>

                <!-- EDIT -->
                <td>
                    <a href="edit_unpaid.php?id=<?= $data['ids'][0] ?>" class="btn btn-warning btn-sm">Edit</a>
                </td>

                <!-- DELETE -->
                <td>
                    <a href="unpaid_services.php?delete_id=<?= $data['ids'][0] ?>" 
                       onclick="return confirm('Are you sure?')" 
                       class="btn btn-danger btn-sm">Delete</a>
                </td>

            </form>
        </tr>

    <?php endforeach; ?>
<?php endforeach; ?>
</tbody>




    </table>

</div>

</body>
</html>
