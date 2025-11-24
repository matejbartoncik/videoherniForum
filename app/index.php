<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the requested page from URL parameter, default to 'home'
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitize page name to prevent directory traversal
$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

// Define which pages require authentication
$protectedPages = ['messages', 'profile', 'settings', 'dashboard'];

// Define which pages are only for guests (redirect if logged in)
$guestOnlyPages = ['login', 'register'];

// Check authentication for protected pages
if (in_array($page, $protectedPages)) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        // User not logged in, redirect to login
        header('Location: ?page=login&redirect=' . urlencode($page));
        exit;
    }
}

// Redirect logged-in users away from guest-only pages
if (in_array($page, $guestOnlyPages)) {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
        // User already logged in, redirect to home or profile
        header('Location: ?page=home');
        exit;
    }
}

// Define the path to the page file
$pagePath = __DIR__ . '/pages/' . $page . '.php';

// Check if the page exists, otherwise default to home
if (!file_exists($pagePath)) {
    $page = 'home';
    $pagePath = __DIR__ . '/pages/home.php';
}

// Set page title based on the page
$pageTitles = [
    'home' => 'Domů',
    'messages' => 'Zprávy',
    'profile' => 'Profil',
    'login' => 'Přihlášení',
    'register' => 'Registrace',
    'settings' => 'Nastavení',
    'dashboard' => 'Přehled'
];

$pageTitle = isset($pageTitles[$page]) ? $pageTitles[$page] : ucfirst($page);

// Include header
include __DIR__ . '/partials/header.php';

// Include the requested page
include $pagePath;

// Include footer
include __DIR__ . '/partials/footer.php';