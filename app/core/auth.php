<?php
require_once __DIR__.'/db.php';
require_once __DIR__.'/crypto.php';
require_once __DIR__.'/session.php';
require_once __DIR__.'/image.php';

// -----------------------------
// Funkce pro registraci uživatele
// -----------------------------
function registerUser(array $data, array $file): array {
    $errors = [];

    // kontrola povinných polí
    $required = ['firstname', 'lastname', 'email', 'phone', 'gender', 'login', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Pole $field je povinné.";
        }
    }

    // základní validace e-mailu a telefonu
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Neplatný e-mail.";
    }
    if (!preg_match('/^[0-9+ ]{9,15}$/', $data['phone'])) {
        $errors[] = "Neplatný telefon.";
    }

    // kontrola unikátního loginu
    if (userExists($data['login'])) {
        $errors[] = "Uživatel s tímto loginem již existuje.";
    }

    // zpracování profilové fotky
    /*
    if (isset($file['photo']) && $file['photo']['error'] === 0) {
        $photoPath = processImage($file['photo']); // TODO: implementace v image.php
        if (!$photoPath) {
            $errors[] = "Chyba při zpracování fotky.";
        }
    } else {
        $photoPath = null;
    }
    */

    // Pokud jsou chyby, vrátíme je
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Hashování hesla
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Šifrování citlivých údajů
    /*
    $emailEncrypted = encryptData($data['email']);  // TODO: implementace v crypto.php
    $phoneEncrypted = encryptData($data['phone']);  // TODO: implementace v crypto.php
    */

    // Uložení do databáze
    /*
    $sql = "INSERT INTO users
            (firstname, lastname, email, phone, gender, login, password_hash, photo_path, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user')";
    $stmt = db()->prepare($sql); // TODO: implementace db() v db.php
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
    */

    return ['success' => true];
}

// -----------------------------
// Funkce pro přihlášení uživatele
// -----------------------------
function auth_login(string $login, string $password): bool {
    // TODO: napojení na databázi přes db()
    $sql = "SELECT * FROM users WHERE login = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;

    if (password_verify($password, $user['password_hash'])) {
        loginUserSession($user); // TODO: implementace session.php
        return true;
    }

    return false;
}

// -----------------------------
// Kontrola existujícího loginu
// -----------------------------
function userExists(string $login): bool {
    $sql = "SELECT COUNT(*) FROM users WHERE login = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$login]);
    return $stmt->fetchColumn() > 0;
}
