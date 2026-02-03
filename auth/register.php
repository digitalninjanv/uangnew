<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/uangnew/index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate input
        $username = clean($_POST['username'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $full_name = clean($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username)) $errors[] = 'Username harus diisi';
        if (empty($email)) $errors[] = 'Email harus diisi';
        if (empty($full_name)) $errors[] = 'Nama lengkap harus diisi';
        if (empty($password)) $errors[] = 'Password harus diisi';
        
        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter';
        if (!isValidEmail($email)) $errors[] = 'Format email tidak valid';
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
        if ($password !== $confirm_password) $errors[] = 'Password tidak cocok';

        // Check if username or email already exists
        if (empty($errors)) {
            $stmt = query("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Username atau email sudah terdaftar';
            }
        }

        // Create user
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                query(
                    "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)",
                    [$username, $email, $hashedPassword, $full_name]
                );
                $userId = lastInsertId();

                // Copy default categories for this user
                query(
                    "INSERT INTO categories (user_id, name, type, color, icon)
                     SELECT ?, name, type, color, icon FROM categories WHERE user_id = 0",
                    [$userId]
                );

                // Create default wallet
                query(
                    "INSERT INTO wallets (user_id, name, type, balance, color, icon) VALUES (?, ?, ?, ?, ?, ?)",
                    [$userId, 'Dompet Utama', 'cash', 0, '#10B981', 'wallet']
                );

                $pdo->commit();

                setFlash('success', 'Registrasi berhasil! Silakan login.');
                redirect('/uangnew/auth/login.php');
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">💰</div>
                <h1 class="auth-title">Daftar Akun</h1>
                <p class="auth-subtitle">Mulai kelola keuangan Anda sekarang</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-control" 
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo $_POST['full_name'] ?? ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username"
                        value="<?php echo $_POST['username'] ?? ''; ?>"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Masukkan email"
                        value="<?php echo $_POST['email'] ?? ''; ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Minimal 6 karakter"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="Ulangi password"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-full" id="registerBtn">
                    <span>Daftar</span>
                </button>
            </form>

            <div class="text-center mt-3">
                <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Sudah punya akun?</p>
                <a href="login.php" class="btn btn-outline w-full">Masuk</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div><span>Memproses...</span>';
        });
    </script>
</body>
</html>
