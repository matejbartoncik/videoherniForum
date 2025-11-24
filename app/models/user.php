
<?php
require_once __DIR__ . '/../core/db.php';

/**
 * Fetch user by ID
 * @param int $userId
 * @return array|null User data or null if not found
 */
function user_fetch_by_id($userId) {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, username, role, phone, avatar_blob, created_at, updated_at
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch user by username
 * @param string $username
 * @return array|null User data or null if not found
 */
function user_fetch_by_username($username) {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, username, role, phone, avatar_blob, created_at, updated_at
        FROM users
        WHERE username = ?
    ");
    $stmt->execute([$username]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch user by email
 * @param string $email
 * @return array|null User data or null if not found
 */
function user_fetch_by_email($email) {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, username, role, phone, avatar_blob, created_at, updated_at
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
}

/**
 * Update user profile information
 * @param int $userId
 * @param array $data Array with keys: first_name, last_name, email, phone
 * @return bool Success status
 */
function user_update_profile($userId, $data) {
    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'] ?? null,
        $userId
    ]);
}

/**
 * Update user avatar
 * @param int $userId
 * @param string $avatarBlob Base64 encoded image data
 * @return bool Success status
 */
function user_update_avatar($userId, $avatarBlob) {
    // Decode base64 image
    if (preg_match('/^data:image\/(\w+);base64,/', $avatarBlob)) {
        $avatarBlob = substr($avatarBlob, strpos($avatarBlob, ',') + 1);
        $avatarBlob = base64_decode($avatarBlob);
        if ($avatarBlob === false) {
            return false;
        }
    }

    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE users
        SET avatar_blob = ?, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$avatarBlob, $userId]);
}

/**
 * Update user password
 * @param int $userId
 * @param string $newPassword
 * @return bool Success status
 */
function user_update_password($userId, $newPassword) {
    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE users
        SET password_hash = ?, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([
        password_hash($newPassword, PASSWORD_BCRYPT),
        $userId
    ]);
}

/**
 * Verify user password
 * @param int $userId
 * @param string $password
 * @return bool True if password matches
 */
function user_verify_password($userId, $password) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    return password_verify($password, $user['password_hash']);
}

/**
 * Get user avatar as base64 data URI
 * @param array|null $user User data with avatar_blob
 * @return string|null Data URI or null
 */
function user_get_avatar_data_uri($user) {
    if (!$user || empty($user['avatar_blob'])) {
        return null;
    }
    return 'data:image/jpeg;base64,' . base64_encode($user['avatar_blob']);
}

/**
 * Delete user account
 * @param int $userId
 * @return bool Success status
 */
function user_delete($userId) {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$userId]);
}

/**
 * Fetch all users (excluding current user)
 * @param int $excludeUserId User ID to exclude from results
 * @return array List of users
 */
function user_fetch_all($excludeUserId = null) {
    $pdo = db();

    if ($excludeUserId) {
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, username, role, avatar_blob
            FROM users
            WHERE id != ?
            ORDER BY username
        ");
        $stmt->execute([$excludeUserId]);
    } else {
        $stmt = $pdo->query("
            SELECT id, first_name, last_name, email, username, role, avatar_blob
            FROM users
            ORDER BY username
        ");
    }

    return $stmt->fetchAll();
}