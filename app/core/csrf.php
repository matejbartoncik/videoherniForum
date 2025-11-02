<?php
require_once __DIR__.'/session.php';

// Generate or return current CSRF token
function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Check CSRF token on form submission
function csrf_check(){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedToken = $_POST['csrf'] ?? '';
        if (!hash_equals(csrf_token(), $postedToken)) {
            // Invalid token
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }
}
