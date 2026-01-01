<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Add new item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];

    if (!empty($item_name)) {
        $stmt = $conn->prepare("INSERT INTO purchase_items (item_name) VALUES (?)");
        $stmt->execute([$item_name]);
    }
}

// Update item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];

    $stmt = $conn->prepare("UPDATE purchase_items SET item_name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
}

// Delete item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM purchase_items WHERE id = ?")->execute([$id]);
}

// Fetch all items
$items = $conn->query("SELECT * FROM purchase_items ORDER BY item_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Purchase Items</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .sidebar { width: 220px; position: fixed; top:0; bottom:0; left:0; background:#343a40; color:white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:10px; text-decoration:none;}
        .sidebar a:hover { background:#495057; }
        .content { margin-left:230px; padding:20px; }
    </style>
</head>
<body class="bg-light">

<!-- Sidebar -->
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

<!-- Content -->
<div class="content">

    <h2 class="mb-3">Manage Purchase Item Names</h2>

    <!-- Add New Item -->
    <form method="POST" class="d-flex mb-4">
        <input type="text" name="item_name" class="form-control me-2" placeholder="Enter Item Name" required>
        <button class="btn btn-primary" name="add_item">Add</button>
    </form>

    <!-- TABLE -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th width="160px">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($items as $item): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $item['item_name'] ?></td>
                <td>
                    <!-- Edit Button -->
                    <button class="btn btn-warning btn-sm" 
                        onclick="openEditModal('<?= $item['id'] ?>', '<?= htmlspecialchars($item['item_name']) ?>')">
                        Edit
                    </button>

                    <!-- Delete Button -->
                    <a href="?delete=<?= $item['id'] ?>" 
                       onclick="return confirm('Delete this item?')" 
                       class="btn btn-danger btn-sm">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Item Name</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id">

        <label class="form-label">Item Name:</label>
        <input type="text" class="form-control" name="edit_name" id="edit_name" required>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" name="update_item">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;

    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

</body>
</html>
