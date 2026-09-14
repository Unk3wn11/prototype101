<?php
session_start();

define('ADMIN_USER', 'admin123');
define('ADMIN_PASS', 'tccadmin');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Tantiado's Online E-bike Registration</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .login-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
    .login-box {
      background:var(--dark2); border:1px solid rgba(255,255,255,0.08);
      border-radius:12px; padding:2.5rem; width:100%; max-width:420px;
    }
    .login-logo { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--green); letter-spacing:2px; margin-bottom:0.25rem; }
    .login-box h2 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:1.5px; margin-bottom:0.4rem; }
    .login-box p { font-size:0.88rem; color:var(--gray); margin-bottom:2rem; }
  </style>
</head>
<body>

<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">⚡TOER</div>
    <h2>ADMIN LOGIN</h2>
    <p>Enter your credentials to access the admin panel.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Username" required autofocus
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
        Login →
      </button>
    </form>
    <p style="text-align:center;margin-top:1.5rem;font-size:0.8rem">
      <a href="../index.php" style="color:var(--gray)">← Back to site</a>
    </p>
  </div>
</div>

</body>
</html>
