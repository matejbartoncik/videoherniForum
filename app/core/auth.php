<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/crypto.php';
require_once __DIR__.'/session.php';
require_once __DIR__.'/image.php';

// -----------------------------
// User registration function
// -----------------------------
function registerUser($post, $files) {
    // ... existing validation code ...
    
    // Handle photo upload from blob
    $photoBlob = null;
    if (!empty($post['photo_blob'])) {
        // Decode base64 image
        $imageData = $post['photo_blob'];
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $photoBlob = base64_decode($imageData);
            if ($photoBlob === false) {
                $photoBlob = null;
            }
        }
    }
    
    // Insert user with blob
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, username, password_hash, phone, avatar_blob, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $post['firstname'],
        $post['lastname'],
        $post['email'],
        $post['nickname'],
        password_hash($post['password'], PASSWORD_BCRYPT),
        $post['phone'],
        $photoBlob
    ]);

    return ['success' => true];
}

// -----------------------------
// User login function
// -----------------------------
function auth_login(string $login, string $password): bool {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;

    if (password_verify($password, $user['password_hash'])) {
        loginUserSession($user);
        return true;
    }

    return false;
}

// -----------------------------
// Check if user login exists
// -----------------------------
function userExists(string $login): bool {
    $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$login]);
    return $stmt->fetchColumn() > 0;
}
