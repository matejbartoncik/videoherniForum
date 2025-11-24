<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/crypto.php';
require_once __DIR__.'/session.php';
require_once __DIR__.'/image.php';

// -----------------------------
// User registration function
// -----------------------------
function registerUser(array $data, array $file): array {
    $errors = [];

    // check required fields
    $required = ['nickname', 'password', 'password_confirm', 'firstname', 'lastname', 'email', 'phone'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Pole $field je povinné.";
        }
    }

    if ($data['password'] !== $data['password_confirm']) {
        $errors[] = "Hesla se neshodují.";
    }

    // basic validation for email and phone
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Neplatný e-mail.";
    }
    if (!preg_match('/^[0-9+ ]{9,15}$/', $data['phone'])) {
        $errors[] = "Neplatný telefon.";
    }

    // unique login check
    if (userExists($data['login'])) {
        $errors[] = "Uživatel s tímto loginem již existuje.";
    }

    // profile photo processing
    if (isset($file['photo']) && $file['photo']['error'] === 0) {
        $photoPath = processImage($file['photo']);
        if (!$photoPath) {
            $errors[] = "Chyba při zpracování fotky.";
        }
    } else {
        $photoPath = null;
    }

    // return errors if any
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // password hashing
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // encrypt sesitive data
    $emailEncrypted = encryptData($data['email']);
    $phoneEncrypted = encryptData($data['phone']);

    // save user to database
    $sql = "INSERT INTO users
            (username, first_name, last_name, email, phone, password_hash, avatar_path, role, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'user', NOW(), NOW())";
    $stmt = db()->prepare($sql);
    $stmt->execute([
        $data['nickname'],
        $data['firstname'],
        $data['lastname'],
        $emailEncrypted,
        $phoneEncrypted,
        $passwordHash,
        $photoPath
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
