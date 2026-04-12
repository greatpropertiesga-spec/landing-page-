<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        trim($_POST['username'] ?? '') === ADMIN_USER &&
        trim($_POST['password'] ?? '') === ADMIN_PASS
    ) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php'); exit;
    }
    $error = 'Wrong username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login | Great Properties GA</title>

  <!-- PWA -->
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#cc0000">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="GP Admin">
  <link rel="apple-touch-icon" href="/icon-512.svg">

  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;min-height:100vh;display:flex;
         align-items:center;justify-content:center;padding:20px;
         background:linear-gradient(135deg,#0f0f0f,#1a1a2e)}
    .card{background:#fff;border-radius:16px;padding:48px 40px;
          width:100%;max-width:400px;
          box-shadow:0 24px 80px rgba(0,0,0,.6);text-align:center}
    .logo{font-size:22px;font-weight:bold;margin-bottom:4px}
    .logo span{color:#cc0000}
    .sub{color:#888;font-size:13px;margin-bottom:32px}
    label{display:block;text-align:left;font-size:13px;
          font-weight:bold;color:#444;margin-bottom:6px}
    input{width:100%;padding:13px 15px;margin-bottom:18px;
          border:1.5px solid #ddd;border-radius:8px;font-size:15px;
          transition:border-color .2s;-webkit-appearance:none}
    input:focus{outline:none;border-color:#cc0000}
    .btn{width:100%;background:#cc0000;color:#fff;border:none;
         padding:15px;border-radius:8px;font-size:16px;
         font-weight:bold;cursor:pointer}
    .btn:hover{background:#a00}
    .error{background:#fff0f0;border:1px solid #fca5a5;color:#cc0000;
           padding:12px;border-radius:8px;font-size:13px;
           margin-bottom:18px;text-align:left}
    .back{margin-top:20px;font-size:12px}
    .back a{color:#888;text-decoration:none}

    /* Install banner */
    #install-banner{
      display:none;
      position:fixed;bottom:0;left:0;right:0;
      background:#cc0000;color:#fff;
      padding:14px 20px;
      flex-direction:column;
      align-items:center;
      gap:10px;
      z-index:999;
      box-shadow:0 -4px 20px rgba(0,0,0,.4);
    }
    #install-banner.show{display:flex;}
    .install-top{display:flex;align-items:center;gap:12px;width:100%;max-width:400px}
    .install-icon{font-size:32px;flex-shrink:0}
    .install-text{flex:1;text-align:left}
    .install-text strong{display:block;font-size:15px}
    .install-text span{font-size:12px;opacity:.85}
    .install-btns{display:flex;gap:10px;width:100%;max-width:400px}
    .btn-install{
      flex:1;background:#fff;color:#cc0000;
      border:none;padding:11px;
      border-radius:8px;font-size:14px;
      font-weight:bold;cursor:pointer;
    }
    .btn-dismiss{
      background:rgba(255,255,255,.2);color:#fff;
      border:none;padding:11px 18px;
      border-radius:8px;font-size:14px;
      cursor:pointer;
    }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">Great <span>Properties</span> GA</div>
  <div class="sub">Admin Dashboard &mdash; Secure Login</div>
  <?php if ($error): ?>
    <div class="error">&#9888; <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="login.php">
    <label>Username</label>
    <input name="username" type="text" placeholder="admin" autocomplete="username" required>
    <label>Password</label>
    <input name="password" type="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" autocomplete="current-password" required>
    <button class="btn" type="submit">Login to Dashboard &rarr;</button>
  </form>
  <div class="back"><a href="/">&larr; Back to site</a></div>
</div>

<!-- Install App Banner -->
<div id="install-banner">
  <div class="install-top">
    <div class="install-icon">&#127968;</div>
    <div class="install-text">
      <strong>Install GP Admin App</strong>
      <span>Add to your home screen for quick access</span>
    </div>
  </div>
  <div class="install-btns">
    <button class="btn-install" id="btn-install">&#11015; Install App</button>
    <button class="btn-dismiss" id="btn-dismiss">Not now</button>
  </div>
</div>

<script>
// Register service worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js').catch(()=>{});
}

// PWA install prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  document.getElementById('install-banner').classList.add('show');
});

document.getElementById('btn-install').addEventListener('click', async () => {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  const { outcome } = await deferredPrompt.userChoice;
  deferredPrompt = null;
  document.getElementById('install-banner').classList.remove('show');
});

document.getElementById('btn-dismiss').addEventListener('click', () => {
  document.getElementById('install-banner').classList.remove('show');
});

// iOS Safari manual instructions
const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
const isSafari = /safari/i.test(navigator.userAgent) && !/chrome/i.test(navigator.userAgent);
const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
if (isIOS && isSafari && !isStandalone) {
  const banner = document.getElementById('install-banner');
  banner.querySelector('.install-text strong').textContent = 'Install on iPhone';
  banner.querySelector('.install-text span').textContent = 'Tap ⋯ Share → Add to Home Screen';
  banner.querySelector('#btn-install').textContent = '📱 How to Install';
  banner.classList.add('show');
  document.getElementById('btn-install').addEventListener('click', () => {
    alert('To install:\n\n1. Tap the Share button \u22EF at the bottom\n2. Scroll down and tap \'Add to Home Screen\'\n3. Tap \'Add\' to confirm');
  }, { once: true });
}
</script>
</body>
</html>
