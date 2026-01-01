<?php require '../config.php';
session_start(); 
// Protect admin 
if ($_SESSION['role'] !== 'admin') { die("Unauthorized"); } $id = $_GET['id']; 
$message = ""; 
$service = $conn->query("SELECT * FROM services WHERE id=$id")->fetch(PDO::FETCH_ASSOC); 
if (!$service) { die("Service not found!"); } 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $name = $_POST['service_name']; 
$price = $_POST['price']; 
$stmt = $conn->prepare("UPDATE services SET service_name = ?, price = ? WHERE id = ?");
if ($stmt->execute([$name, $price, $id])) 
{ header("Location: services.php"); exit; } 
else { $message = "Error updating service."; } } 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
        }
        .edit-card {
            max-width: 500px;
            margin: 80px auto;
            padding: 25px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="edit-card">
    <h3 class="text-center mb-3">Edit Service</h3>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Service Name</label>
            <input type="text" name="service_name" class="form-control" value="<?= htmlspecialchars($service['service_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($service['price']) ?>" required>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary w-100">Update</button>
            <a href="services.php" class="btn btn-secondary w-100">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
