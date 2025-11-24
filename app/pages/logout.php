<?php
// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to home
header('Location: ?page=home');
exit;