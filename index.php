<?php
session_start();
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['location_id'] = $user['location_id'];

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carwash Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-card {
            max-width: 500px; /* wider */
            padding: 40px;     /* more padding */
            margin-top: 80px;  /* move down */
            border-radius: 12px;
        }
        .login-card h3 {
            font-size: 28px;   /* bigger title */
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-start">
    <div class="card shadow login-card">
        <div class="card-body">
            <h3 class="text-center mb-4">Login</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label>Username</label>
                    <input name="username" class="form-control form-control-lg" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input name="password" type="password" class="form-control form-control-lg" required>
                </div>

                <button class="btn btn-primary btn-lg w-100">Login</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
