<?php
/**
 * Application Configuration
 */

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Application settings
define('APP_NAME', 'Finance Manager');
define('APP_URL', 'http://localhost/uangnew');
define('CURRENCY_SYMBOL', 'Rp');
define('CURRENCY_CODE', 'IDR');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_LENGTH', 32);

// Pagination
define('ITEMS_PER_PAGE', 20);

// File upload (for future features like receipts)
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Error reporting (change in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
