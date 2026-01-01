<?php
require '../middleware/admin_protect.php';
require '../config.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid service ID.");
}

$stmt = $conn->prepare("DELETE FROM service_report WHERE id = ?");
$stmt->execute([$id]);

header("Location: reports.php");
exit;

