<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if (!isset($_GET['id'])) {
    die("Location ID missing");
}

$id = $_GET['id'];

// Fetch location
$stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
$stmt->execute([$id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$location) {
    die("Location not found");
}

// Update on submit
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location_name = $_POST['location_name'];

    $update = $conn->prepare("UPDATE locations SET location_name = ? WHERE id = ?");
    $update->execute([$location_name, $id]);

    $message = "Location updated successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Location</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="content">

    <h2>Edit Location</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Location Name</label>
            <input type="text" name="location_name" class="form-control"
                   value="<?= $location['location_name'] ?>" required>
        </div>

        <button class="btn btn-primary">Update Location</button>
        <a href="manage_locations.php" class="btn btn-secondary">Back</a>
    </form>

</div>
</body>
</html>
