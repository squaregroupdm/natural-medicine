<?php
require __DIR__ . '/../../private_app/bootstrap.php';

if (current_user()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    $u = q1('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);

    // Always run the hash check so a wrong email and a wrong password
    // take the same amount of time.
    $ok = $u
        ? password_verify($pass, $u['password_hash'])
        : password_verify($pass, '$2y$12$usesomesillystringfoeahsqE4uJH2Kk7q0XJf.0y7IqSUhZlFxi');

    if ($u && $ok) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $u['id'];
        run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$u['id']]);
        redirect('index.php');
    }
    $error = 'That email and password do not match.';
    usleep(400000);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Square Naturals admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
<form class="login" method="post">
  <h1>Square <span>Naturals</span></h1>
  <p class="sub">Sign in to edit the website.</p>
  <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Email
    <input type="email" name="email" required autofocus autocomplete="username">
  </label>
  <label>Password
    <input type="password" name="password" required autocomplete="current-password">
  </label>
  <button type="submit">Sign in</button>
</form>
</body>
</html>
