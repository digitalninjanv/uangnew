<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Require authentication on all pages that include this header
requireAuth();

$user = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/uangnew/assets/css/style.css">
    <link rel="stylesheet" href="/uangnew/assets/css/components.css">
    <link rel="stylesheet" href="/uangnew/assets/css/app.css">
</head>
<body>
    <!-- Skip Navigation for Accessibility -->
    <a href="#main-content" class="sr-only">Skip to main content</a>
    
    <!-- Top Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <span class="brand-icon">💰</span>
                    <span class="brand-text"><?php echo APP_NAME; ?></span>
                </div>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="navbar-menu" id="navbarMenu">
                    <a href="/uangnew/index.php" class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/uangnew/wallets.php" class="nav-link <?php echo $currentPage === 'wallets' ? 'active' : ''; ?>">
                        <i class="fas fa-wallet"></i>
                        <span>Dompet</span>
                    </a>
                    <a href="/uangnew/transactions.php" class="nav-link <?php echo $currentPage === 'transactions' ? 'active' : ''; ?>">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transaksi</span>
                    </a>
                    <a href="/uangnew/categories.php" class="nav-link <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                    <a href="/uangnew/history.php" class="nav-link <?php echo $currentPage === 'history' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Riwayat</span>
                    </a>
                </div>

                <div class="navbar-user">
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                            <span class="user-name"><?php echo $user['full_name']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="/uangnew/auth/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Keluar</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="main-content" role="main">
        <div class="container">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
