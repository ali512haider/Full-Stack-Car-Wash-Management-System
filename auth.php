<?php
require_once 'config.php';


// index.php logic (POST email, password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';


$stmt = $pdo->prepare('SELECT id, name, email, password, role, location_id FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();


if ($user && password_verify($password, $user['password'])) {
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['location_id'] = $user['location_id'];
header('Location: admin/dashboard.php');
exit;
} else {
$error = 'Invalid credentials';
}
}


// logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
session_destroy();
header('Location: index.php');
exit;
}