<?php
// Database configuration for Docker MySQL
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'videoherniforum');
define('DB_USER', 'phpuser');
define('DB_PASS', 'phppassword');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');

// Returns PDO connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage() . "<br>Make sure Docker MySQL is running: docker ps");
        }
    }
    return $pdo;
}

// Alias for backward compatibility
function get_db_connection() {
    return db();
}