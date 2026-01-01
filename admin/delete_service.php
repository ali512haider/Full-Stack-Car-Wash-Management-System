<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("No service ID provided.");
}

// Make sure $id is integer
$id = (int)$id;

// Prepare and execute DELETE statement using PDO
$stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
if ($stmt->execute([$id])) { // <-- wrap $id in array
    header("Location: reports.php");
    exit;
} else {
    echo "Error deleting service.";
}
?>
