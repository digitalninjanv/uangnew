<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/uangnew/index.php');
}

$error = '';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
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
                <h1 class="auth-title"><?php echo APP_NAME; ?></h1>
                <p class="auth-subtitle">Kelola keuangan pribadi Anda dengan mudah</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Username atau Email</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username atau email"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-full" id="loginBtn">
                    <span>Masuk</span>
                </button>
            </form>

            <div class="text-center mt-3">
                <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Belum punya akun?</p>
                <a href="register.php" class="btn btn-outline w-full">Daftar Sekarang</a>
            </div>

            <div class="text-center mt-3" style="color: var(--text-muted); font-size: 0.875rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                <p style="margin-bottom: 0.5rem;"><strong>Demo Account:</strong></p>
                <p style="margin: 0;">Username: <strong>demo</strong></p>
                <p style="margin: 0;">Password: <strong>demo123</strong></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div><span>Memproses...</span>';
        });
    </script>
</body>
</html>
