<?php
require '../middleware/admin_protect.php';
require '../config.php';

$id = $_POST['id'];
$username = $_POST['username'];
$role = $_POST['role'];
$location_id = $_POST['location_id'] ?: null;

// If password entered → update
if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "UPDATE users SET username=?, password=?, role=?, location_id=? WHERE id=?";
    $conn->prepare($sql)->execute([$username, $password, $role, $location_id, $id]);
} else {
    $sql = "UPDATE users SET username=?, role=?, location_id=? WHERE id=?";
    $conn->prepare($sql)->execute([$username, $role, $location_id, $id]);
}

header("Location: users.php");
exit;
?>
