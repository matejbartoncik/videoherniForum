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
    $required = ['firstname', 'lastname', 'email', 'phone', 'gender', 'login', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Pole $field je povinné.";
        }
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
            (firstname, lastname, email, phone, gender, login, password_hash, photo_path, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user')";
    $stmt = db()->prepare($sql);
    $stmt->execute([
        $data['firstname'],
        $data['lastname'],
        $emailEncrypted,
        $phoneEncrypted,
        $data['gender'],
        $data['login'],
        $passwordHash,
        $photoPath
    ]);

    return ['success' => true];
}

// -----------------------------
// User login function
// -----------------------------
function auth_login(string $login, string $password): bool {
    $sql = "SELECT * FROM users WHERE login = ?";
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
    $sql = "SELECT COUNT(*) FROM users WHERE login = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$login]);
    return $stmt->fetchColumn() > 0;
}
