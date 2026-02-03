<?php
require_once __DIR__ . '/../config/config.php';

// Destroy session
session_start();
session_unset();
session_destroy();

// Redirect to login
header("Location: /uangnew/auth/login.php");
exit;
