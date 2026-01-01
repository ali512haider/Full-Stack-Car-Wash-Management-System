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

// Delete location
$stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
$stmt->execute([$id]);

header("Location: manage_locations.php?msg=deleted");
exit;
