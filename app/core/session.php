<?php
session_start();

// Log user into session
function loginUserSession(array $user)
{
    $_SESSION['user'] = [
        'id' => $user['id'],
        'login' => $user['login'],
        'role' => $user['role']
    ];
    $_SESSION['last_activity'] = time();
}

// Check if user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

// Logout user
function logoutUser()
{
    session_unset();
    session_destroy();
}

// Session timeout (20 minutes)
function checkSessionTimeout()
{
    $timeout = 20 * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logoutUser();
    } else {
        $_SESSION['last_activity'] = time();
    }
}
