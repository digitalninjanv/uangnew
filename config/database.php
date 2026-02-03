<?php
/**
 * Database Connection Configuration
 * Uses PDO for secure, prepared statement support
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'finance_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log error in production, display in development
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

/**
 * Execute a prepared statement query
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement
 */
function query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Fetch all rows
 */
function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch single row
 */
function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

/**
 * Execute insert/update/delete and return affected rows
 */
function execute($sql, $params = []) {
    return query($sql, $params)->rowCount();
}

/**
 * Get last inserted ID
 */
function lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}
