<?php
require '../middleware/admin_protect.php';
require '../config.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];
$location_id = $_POST['location_id'] ?: null;

$stmt = $conn->prepare("
    INSERT INTO users (username, password, role, location_id)
    VALUES (:u, :p, :r, :l)
");

$stmt->execute([
    ':u' => $username,
    ':p' => $password,
    ':r' => $role,
    ':l' => $location_id
]);

header("Location: users.php");
exit;
?>
