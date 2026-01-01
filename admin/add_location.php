<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_name = trim($_POST['location_name']);

    if ($location_name === "") {
        $message = "Location name is required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO locations (location_name) VALUES (:name)");
        $stmt->bindParam(':name', $location_name);

        if ($stmt->execute()) {
            $message = "Location Added Successfully!";
        } else {
            $message = "Error adding location!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Location</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            padding: 30px;
        }

        .container {
            background: white;
            width: 400px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #aaa;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #bbb;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #0056b3;
        }

        .msg {
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .success { background: #28a745; color: white; }
        .error { background: #dc3545; color: white; }

        a {
            text-decoration: none;
            color: #007bff;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>

</head>
<body>

<div class="container">
    <h2>➕ Add New Location</h2>

    <?php if ($message != ""): ?>
        <div class="msg <?= strpos($message, 'Success') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Location Name</label>
        <input type="text" name="location_name" placeholder="Enter Location Name">
        <button type="submit">Add Location</button>
    </form>

    <a href="manage_locations.php">⬅ Back</a>
</div>

</body>
</html>
