<?php
require '../middleware/admin_protect.php';
require '../config.php';

$purchases = $conn->query("
    SELECT p.*, l.location_name, pi.item_name
    FROM purchases p
    JOIN locations l ON p.location_id = l.id
    JOIN purchase_items pi ON p.purchased_item = pi.id
    ORDER BY p.id ASC
")->fetchAll(PDO::FETCH_ASSOC);


$locations = $conn->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Purchases</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
    <h2>Purchases Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">Add Purchase</button>

    <table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Location</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Total Price</th> <!-- New column -->
            <th>Purchase Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php $counter = 1; ?>
    <?php foreach ($purchases as $p): ?>
        <tr>
            <td><?= $counter++ ?></td> <!-- Consecutive number -->
            <td><?= $p['location_name'] ?></td>
            <td><?= $p['item_name'] ?></td>
            <td><?= $p['quantity'] ?></td>
            <td><?= number_format($p['amount'],3) ?></td>
            <td><?= number_format($p['quantity'] * $p['amount'], 3) ?></td>
            <td><?= date('d-m-Y', strtotime($p['purchase_date'])) ?></td>
            <td>
                <a href="edit_purchase.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete_purchase.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

</table>

</div>

<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal">
  <div class="modal-dialog">
    <form method="POST" action="add_purchase.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Purchase</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Location</label>
        <select name="location_id" class="form-control mb-2" required>
            <option value="">-- Select --</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id'] ?>"><?= $loc['location_name'] ?></option>
            <?php endforeach; ?>
        </select>

        <label>Item Name</label>
<label>Item Name</label>
<select id="purchaseItemDropdown" name="purchased_item" class="form-control mb-2" required>
    <option value="">Search or select item...</option>

    <?php
    $itemList = $conn->query("
        SELECT id, item_name 
        FROM purchase_items
        ORDER BY item_name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itemList as $it):
    ?>
        <option value="<?= $it['id'] ?>"><?= $it['item_name'] ?></option>
    <?php endforeach; ?>
</select>




        <label>Quantity</label>
        <input type="number" name="quantity" class="form-control mb-2" required>

        <label>Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control mb-2" required>

        <label>Purchase Date</label>
        <input type="date" name="purchase_date" class="form-control mb-2" required>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    $("#purchaseItemDropdown").select2({
        dropdownParent: $("#addPurchaseModal"), 
        placeholder: "Search or select item...",
        width: "100%"
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
