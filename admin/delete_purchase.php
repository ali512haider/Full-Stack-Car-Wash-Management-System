<?php
require '../middleware/admin_protect.php';
require '../config.php';

$id = $_GET['id'];
$conn->query("DELETE FROM purchases WHERE id=$id");

header("Location: purchases.php");
exit;
?>
