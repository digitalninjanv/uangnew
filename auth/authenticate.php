<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/uangnew/auth/login.php');
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect('/uangnew/auth/login.php');
}

$username = clean($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    setFlash('error', 'Username dan password harus diisi');
    redirect('/uangnew/auth/login.php');
}

try {
    // Find user by username or email
    $sql = "SELECT id, username, email, password, full_name FROM users WHERE username = ? OR email = ?";
    $user = fetchOne($sql, [$username, $username]);

    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Username atau password salah');
        redirect('/uangnew/auth/login.php');
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['last_activity'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);

    setFlash('success', 'Selamat datang, ' . $user['full_name'] . '!');
    redirect('/uangnew/index.php');

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silakan coba lagi.');
    redirect('/uangnew/auth/login.php');
}
