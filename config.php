<?php
$host = "localhost:3306";
$dbname = "nnthwfte_carwash_db";
$username = "nnthwfte_nnthwfte";
$password = "Car@123@123";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
