<?php
require '../middleware/admin_protect.php';
require '../config.php';

// Fetch all users
$users = $conn->query("
    SELECT users.*, locations.location_name 
    FROM users 
    LEFT JOIN locations ON locations.id = users.location_id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations for dropdown
$locations = $conn->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

    <h2 class="mb-3">User Management</h2>

    <!-- Add User Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
        Add New User
    </button>

    <!-- Users Table -->
    <table id="userTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Location</th>
                <th>Created At</th>
                <th width="180px">Actions</th>
            </tr>
        </thead>
        <tbody>
<?php $counter = 1; ?>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= $counter++ ?></td> <!-- display counter instead of DB id -->
    <td><?= $u['username'] ?></td>
    <td><?= $u['role'] ?></td>
    <td><?= $u['location_name'] ?></td>
    <td><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
    <td>
        <button class="btn btn-sm btn-warning"
            onclick="editUser(<?= $u['id'] ?>, '<?= $u['username'] ?>', '<?= $u['role'] ?>', '<?= $u['location_id'] ?>')">
            Edit
        </button>

        <a href="delete_user.php?id=<?= $u['id'] ?>" 
           class="btn btn-sm btn-danger"
           onclick="return confirm('Are you sure?')">
           Delete
        </a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

    </table>

</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal">
  <div class="modal-dialog">
    <form method="POST" action="add_user.php" class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add User</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label>Username</label>
        <input name="username" class="form-control mb-2" required>

        <label>Password</label>
        <input name="password" type="password" class="form-control mb-2" required>

        <label>Role</label>
        <select name="role" class="form-control mb-2">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>

        <label>Location</label>
        <select name="location_id" class="form-control mb-2">
            <option value="">-- None --</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id'] ?>"><?= $loc['location_name'] ?></option>
            <?php endforeach; ?>
        </select>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
      </div>

    </form>
  </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editUserModal">
  <div class="modal-dialog">
    <form method="POST" action="edit_user.php" class="modal-content">

      <input type="hidden" name="id" id="edit_id">

      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label>Username</label>
        <input name="username" id="edit_username" class="form-control mb-2" required>

        <label>Password (leave blank to keep same)</label>
        <input name="password" type="password" class="form-control mb-2">

        <label>Role</label>
        <select name="role" id="edit_role" class="form-control mb-2">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>

        <label>Location</label>
        <select name="location_id" id="edit_location" class="form-control mb-2">
            <option value="">-- None --</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id'] ?>"><?= $loc['location_name'] ?></option>
            <?php endforeach; ?>
        </select>

      </div>

      <div class="modal-footer">
        <button class="btn btn-warning">Update</button>
      </div>

    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#userTable').DataTable();
});

function editUser(id, username, role, location) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_location').value = location;

    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}
</script>

</body>
</html>
