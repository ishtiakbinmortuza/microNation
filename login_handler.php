<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.php'); exit; }

$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';
if ($identifier === '' || $password === '') {
    flash_set('error','Credentials required.');
    header('Location: login.php'); exit;
}

$stmt = db()->prepare("SELECT * FROM users WHERE username = :id OR email = :email LIMIT 1");
$stmt->execute(['id' => strtolower($identifier), 'email' => strtolower($identifier)]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    flash_set('error','Invalid credentials.');
    header('Location: login.php'); exit;
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role']
];
flash_set('success','Welcome back, ' . sanitize($user['username']) . '!');

// Redirect admin users to the admin CRUD page, others to the normal dashboard
if (isset($user['role']) && strtolower($user['role']) === 'admin') {
    header('Location: admin_manage.php');
    exit;
}

       header('Location: profile.php');
exit;
