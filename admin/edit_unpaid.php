<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Redirect if ID missing
if (!isset($_GET['id'])) {
    header("Location: unpaid_services.php");
    exit;
}

$id = $_GET['id'];
$message = "";

/* ================================================================
   LOAD UNPAID SERVICE DETAILS
   ================================================================ */
$stmt = $conn->prepare("
    SELECT * FROM unpaid_services WHERE id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Invalid unpaid service ID");
}

/* ================================================================
   LOAD SERVICES DROPDOWN
   ================================================================ */
$services = $conn->query("
    SELECT id, service_name FROM services ORDER BY service_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================================================
   LOAD USERS DROPDOWN
   ================================================================ */
$users = $conn->query("
    SELECT id, username FROM users ORDER BY username ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================================================
   LOAD LOCATIONS DROPDOWN
   ================================================================ */
$locations = $conn->query("
    SELECT id, location_name FROM locations ORDER BY location_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================================================
   UPDATE SUBMISSION
   ================================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle = $_POST['vehicle_number'];
    $mobile = $_POST['customer_mobile'];
    $service_id = $_POST['service_id'];
    $price = $_POST['price'];
    $location_id = $_POST['location_id'];
    $added_date = $_POST['added_date'];
    $added_time = $_POST['added_time'];
    $user_id = $_POST['user_id'];

    $update = $conn->prepare("
        UPDATE unpaid_services
        SET vehicle_number = ?, customer_mobile = ?, service_id = ?, price = ?,
            location_id = ?, added_date = ?, added_time = ?, user_id = ?
        WHERE id = ?
    ");

    $update->execute([
        $vehicle,
        $mobile,
        $service_id,
        $price,
        $location_id,
        $added_date,
        $added_time,
        $user_id,
        $id
    ]);

    $message = "Record updated successfully!";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Unpaid Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h3>Edit Unpaid Service</h3>
    <a href="unpaid_services.php" class="btn btn-secondary btn-sm mb-3">Back</a>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Vehicle Number</label>
                <input type="text" name="vehicle_number" class="form-control"
                       value="<?= $data['vehicle_number'] ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Customer Mobile</label>
                <input type="text" name="customer_mobile" class="form-control"
                       value="<?= $data['customer_mobile'] ?>">
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-6">
                <label class="form-label">Service</label>
                <select name="service_id" class="form-select" >
                    <?php foreach ($services as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= ($s['id'] == $data['service_id']) ? 'selected' : '' ?>>
                            <?= $s['service_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Price</label>
                <input type="number" step="0.001" name="price" class="form-control"
                       value="<?= $data['price'] ?>">
            </div>

        </div>

        <div class="row mb-3">

            <div class="col-md-6">
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>"
                            <?= ($loc['id'] == $data['location_id']) ? 'selected' : '' ?>>
                            <?= $loc['location_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Added By (User)</label>
                <select name="user_id" class="form-select">
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"
                            <?= ($u['id'] == $data['user_id']) ? 'selected' : '' ?>>
                            <?= $u['username'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="row mb-3">

            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="added_date" class="form-control"
                       value="<?= $data['added_date'] ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Time</label>
                <input type="time" name="added_time" class="form-control"
                       value="<?= $data['added_time'] ?>">
            </div>

        </div>

        <button class="btn btn-primary">Update</button>

    </form>

</div>

</body>
</html>
