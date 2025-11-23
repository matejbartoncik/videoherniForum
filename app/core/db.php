<?php
// Returns PDO connection
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'https://herniforum.page.gd/';
        $dbname = 'your_database';
        $user = 'your_user';
        $pass = 'your_password';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    return $pdo;
}
