<?php
require '../middleware/admin_protect.php';
require '../config.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid service ID.");
}

$stmt = $conn->prepare("SELECT * FROM service_report WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
$users = $conn->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
$locations = $conn->query("SELECT id, location_name FROM locations")->fetchAll(PDO::FETCH_ASSOC);

if (!$service) die("Service record not found.");


// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle  = $_POST['vehicle_number'];
    $customer = $_POST['customer_mobile'];
    $services = $_POST['services'];
    $total    = $_POST['total_price'];
    $payment  = $_POST['payment_method'];
    $user     = $_POST['user_name'];
    $location = $_POST['location'];
    $date     = $_POST['service_date'];

 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $conn->prepare("
        UPDATE service_report SET 
            vehicle_number=?, 
            customer_mobile=?, 
            services=?, 
            total_price=?, 
            payment_method=?, 
            report_date=?, 
            user_id=?, 
            location_id=?
        WHERE id=?
    ");

    $update->execute([
        $_POST['vehicle_number'],
        $_POST['customer_mobile'],
        $_POST['services'],
        $_POST['total_price'],
        $_POST['payment_method'],
        $_POST['report_date'],
        $_POST['user_id'],
        $_POST['location_id'],
        $id
    ]);

    header("Location: reports.php");
    exit;
}
}

// Convert date to Y-m-d for input type="date"
$formattedDate = date('Y-m-d', strtotime($service['date']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Service</h2>
    <form method="POST">

    <div class="mb-3">
        <label>Vehicle Number</label>
        <input type="text" name="vehicle_number" class="form-control"
               value="<?= htmlspecialchars($service['vehicle_number']) ?>" required>
    </div>

    <div class="mb-3">
        <label>Customer Mobile</label>
        <input type="text" name="customer_mobile" class="form-control"
               value="<?= htmlspecialchars($service['customer_mobile']) ?>">
    </div>

    <div class="mb-3">
        <label>Services</label>
        <input type="text" name="services" class="form-control"
               value="<?= htmlspecialchars($service['services']) ?>" required>
    </div>

    <div class="mb-3">
        <label>Total Price</label>
        <input type="number" step="0.01" name="total_price" class="form-control"
               value="<?= $service['total_price'] ?>" required>
    </div>

    <div class="mb-3">
        <label>Payment Method</label>
        <select name="payment_method" class="form-control">
            <option value="Cash" <?= $service['payment_method']=='Cash'?'selected':'' ?>>Cash</option>
            <option value="Bank" <?= $service['payment_method']=='Bank'?'selected':'' ?>>Bank</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Report Date</label>
        <input type="date" name="report_date" class="form-control"
               value="<?= $service['report_date'] ?>">
    </div>

    <div class="mb-3">
        <label>User</label>
        <select name="user_id" class="form-control">
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" 
                    <?= $service['user_id'] == $u['id'] ? 'selected' : '' ?>>
                    <?= $u['username'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Location</label>
        <select name="location_id" class="form-control">
            <?php foreach ($locations as $l): ?>
                <option value="<?= $l['id'] ?>" 
                    <?= $service['location_id'] == $l['id'] ? 'selected' : '' ?>>
                    <?= $l['location_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="reports.php" class="btn btn-secondary">Cancel</a>

</form>

</div>
</body>
</html>
