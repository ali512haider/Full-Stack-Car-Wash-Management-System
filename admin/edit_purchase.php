<?php
require '../middleware/admin_protect.php';
require '../config.php';

$id = $_GET['id'];
$items = $conn->query("SELECT id, item_name FROM purchase_items")->fetchAll(PDO::FETCH_ASSOC);

$purchase = $conn->query("
    SELECT purchases.*, purchase_items.item_name 
    FROM purchases
    LEFT JOIN purchase_items 
        ON purchase_items.id = purchases.purchased_item
    WHERE purchases.id = $id
")->fetch(PDO::FETCH_ASSOC);

$locations = $conn->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_id = $_POST['location_id'];
    $purchased_item = $_POST['purchased_item'];
    $quantity = $_POST['quantity'];
    $amount = $_POST['amount'];
    $purchase_date = $_POST['purchase_date'];

    $stmt = $conn->prepare("UPDATE purchases SET location_id=?, purchased_item=?, quantity=?, amount=?, purchase_date=? WHERE id=?");
    $stmt->execute([$location_id, $purchased_item, $quantity, $amount, $purchase_date, $id]);

    header("Location: purchases.php");
    exit;
}
?>
 <!DOCTYPE html>
<html>
<head>
    <title>Edit Purchase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f7f7f9;
        }
        .edit-card {
            max-width: 600px;
            margin: 80px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="edit-card">
    <h3 class="text-center mb-4">Edit Purchase</h3>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Location</label>
            <select name="location_id" class="form-control" required>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>" 
                        <?= $loc['id'] == $purchase['location_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['location_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
    <label class="form-label">Purchase Item</label>
    <select name="purchased_item" class="form-control" required>
        <?php foreach ($items as $item): ?>
            <option value="<?= $item['id'] ?>" 
                <?= $item['id'] == $purchase['purchased_item'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($item['item_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" 
                   value="<?= htmlspecialchars($purchase['quantity']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" 
                   value="<?= htmlspecialchars($purchase['amount']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Purchase Date</label>
            <input type="date" name="purchase_date" class="form-control" 
                   value="<?= htmlspecialchars($purchase['purchase_date']) ?>" required>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Update</button>
            <a href="purchases.php" class="btn btn-secondary w-100">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>