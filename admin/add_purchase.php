<?php
require '../middleware/admin_protect.php';
require '../config.php';

$location_id = $_POST['location_id'];
$purchased_item = $_POST['purchased_item'];
$quantity = $_POST['quantity'];
$amount = $_POST['amount'];
$purchase_date = $_POST['purchase_date'];

$stmt = $conn->prepare("INSERT INTO purchases (location_id, purchased_item, quantity, amount, purchase_date) VALUES (?,?,?,?,?)");
$stmt->execute([$location_id, $purchased_item, $quantity, $amount, $purchase_date]);

header("Location: purchases.php");
exit;
?>
