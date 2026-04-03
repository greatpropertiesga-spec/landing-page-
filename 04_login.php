<?php
include 'config.php';
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Wrong username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
</head>
<body>
<form method="POST">
<input name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button>Login</button>
</form>
</body>
</html>