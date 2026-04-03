<?php
require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_time']      = time();
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Wrong username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Great Properties GA</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      font-family:Arial,sans-serif;
      background:linear-gradient(135deg,#111,#1a1a2e);
      min-height:100vh;display:flex;align-items:center;
      justify-content:center;padding:20px;
    }
    .card{
      background:#fff;border-radius:16px;padding:48px 40px;
      width:100%;max-width:400px;
      box-shadow:0 24px 80px rgba(0,0,0,0.5);text-align:center;
    }
    .logo{font-size:22px;font-weight:bold;margin-bottom:4px;}
    .logo span{color:#cc0000;}
    .sub{color:#888;font-size:13px;margin-bottom:32px;}
    label{display:block;text-align:left;font-size:13px;font-weight:bold;color:#444;margin-bottom:6px;}
    input{
      width:100%;padding:13px 15px;margin-bottom:18px;
      border:1.5px solid #ddd;border-radius:8px;font-size:15px;
      transition:border-color .2s;
    }
    input:focus{outline:none;border-color:#cc0000;}
    button{
      width:100%;background:#cc0000;color:#fff;border:none;
      padding:14px;border-radius:8px;font-size:16px;
      font-weight:bold;cursor:pointer;
    }
    button:hover{background:#a00;}
    .error{
      background:#fff0f0;border:1px solid #fca5a5;color:#cc0000;
      padding:12px;border-radius:8px;font-size:13px;margin-bottom:18px;
    }
    .back{margin-top:20px;font-size:12px;}
    .back a{color:#888;text-decoration:none;}
  </style>
</head>
<body>
<div class="card">
  <div class="logo">Great <span>Properties</span> GA</div>
  <div class="sub">Admin Dashboard &mdash; Secure Login</div>

  <?php if ($error): ?>
    <div class="error">&#9888; <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (isset($db_error)): ?>
    <div class="error">&#9888; DB Error: <?= htmlspecialchars($db_error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <label>Username</label>
    <input name="username" type="text" placeholder="admin" autocomplete="username" required>
    <label>Password</label>
    <input name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
    <button type="submit">Login to Dashboard &rarr;</button>
  </form>
  <div class="back"><a href="/">&larr; Back to site</a></div>
</div>
</body>
</html>
