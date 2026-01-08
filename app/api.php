<?php
declare(strict_types=1);

require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/session.php';
require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/models/topic.php';
require_once __DIR__ . '/models/comment.php';
require_once __DIR__ . '/models/like.php';

header('Content-Type: application/json; charset=utf-8');

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if ($authHeader === '' && function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}

if (preg_match('/^Bearer\s+(\S+)/', $authHeader, $matches)) {
    session_id($matches[1]);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function api_response(int $statusCode, array $payload): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function api_error(int $statusCode, string $message): void {
    api_response($statusCode, ['success' => false, 'error' => $message]);
}

function api_get_json_body(): array {
    $input = file_get_contents('php://input');
    if ($input === false || $input === '') {
        return [];
    }
    $data = json_decode($input, true);
    if (!is_array($data)) {
        return [];
    }
    return $data;
}

function api_get_param(array $source, string $key, ?string $default = null): ?string {
    if (!array_key_exists($key, $source)) {
        return $default;
    }
    $value = $source[$key];
    if (is_string($value)) {
        $value = trim($value);
    }
    if ($value === '' || $value === null) {
        return $default;
    }
    return (string) $value;
}

function api_require_login(): array {
    if (!isset($_SESSION['user']['id'])) {
        api_error(401, 'Nejste přihlášen.');
    }
    return $_SESSION['user'];
}

function api_user_profile(array $user): array {
    return [
        'id' => (int) $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'username' => $user['username'],
        'role' => $user['role'],
        'phone' => $user['phone'],
        'avatar' => user_get_avatar_data_uri($user),
        'created_at' => $user['created_at'],
        'updated_at' => $user['updated_at'],
    ];
}

function api_post_summary(array $post): array {
    $avatar = null;
    if (!empty($post['author_avatar'])) {
        $avatar = 'data:image/jpeg;base64,' . base64_encode($post['author_avatar']);
    }

    return [
        'id' => (int) $post['id'],
        'title' => $post['title'],
        'content' => $post['content'],
        'created_at' => $post['created_at'],
        'updated_at' => $post['updated_at'],
        'author' => [
            'id' => (int) $post['author_id'],
            'username' => $post['author_username'],
            'first_name' => $post['author_first_name'],
            'last_name' => $post['author_last_name'],
            'avatar' => $avatar,
        ],
        'comments_count' => (int) ($post['comments_count'] ?? 0),
        'likes_count' => (int) ($post['likes_count'] ?? 0),
    ];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($action === '') {
    api_error(400, 'Chybí parametr action.');
}

$jsonBody = api_get_json_body();

switch ($action) {
    case 'register':
        if ($method !== 'POST') {
            api_error(405, 'Použijte POST.');
        }

        $firstName = api_get_param($jsonBody, 'first_name');
        $lastName = api_get_param($jsonBody, 'last_name');
        $email = api_get_param($jsonBody, 'email');
        $username = api_get_param($jsonBody, 'username');
        $password = api_get_param($jsonBody, 'password');
        $phone = api_get_param($jsonBody, 'phone');

        if (!$firstName || !$lastName || !$email || !$username || !$password) {
            api_error(422, 'Vyplňte všechna povinná pole.');
        }

        if (user_fetch_by_username($username)) {
            api_error(409, 'Uživatelské jméno už existuje.');
        }

        if (user_fetch_by_email($email)) {
            api_error(409, 'Email už existuje.');
        }

        $pdo = db();
        $stmt = $pdo->prepare("\n            INSERT INTO users (first_name, last_name, email, username, password_hash, phone, created_at, updated_at)\n            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())\n        ");
        $created = $stmt->execute([
            $firstName,
            $lastName,
            $email,
            $username,
            password_hash($password, PASSWORD_BCRYPT),
            $phone,
        ]);

        if (!$created) {
            api_error(500, 'Nepodařilo se vytvořit účet.');
        }

        $userId = (int) $pdo->lastInsertId();
        $user = user_fetch_by_id($userId);

        if (!$user) {
            api_error(500, 'Nepodařilo se načíst profil.');
        }

        loginUserSession([
            'id' => $userId,
            'username' => $user['username'],
            'role' => $user['role'],
        ]);

        api_response(201, [
            'success' => true,
            'token' => session_id(),
            'user' => api_user_profile($user),
        ]);
        break;

    case 'login':
        if ($method !== 'POST') {
            api_error(405, 'Použijte POST.');
        }

        $username = api_get_param($jsonBody, 'username');
        $password = api_get_param($jsonBody, 'password');

        if (!$username || !$password) {
            api_error(422, 'Zadejte uživatelské jméno i heslo.');
        }

        $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            api_error(401, 'Špatné přihlašovací údaje.');
        }

        loginUserSession($user);

        api_response(200, [
            'success' => true,
            'token' => session_id(),
            'user' => api_user_profile($user),
        ]);
        break;

    case 'create-post':
        if ($method !== 'POST') {
            api_error(405, 'Použijte POST.');
        }

        $sessionUser = api_require_login();
        $title = api_get_param($jsonBody, 'title');
        $content = api_get_param($jsonBody, 'content');

        if (!$title || !$content) {
            api_error(422, 'Vyplňte název i obsah.');
        }

        $created = topic_create((int) $sessionUser['id'], $title, $content);
        if (!$created) {
            api_error(500, 'Nepodařilo se vytvořit příspěvek.');
        }

        api_response(201, [
            'success' => true,
        ]);
        break;

    case 'create-comment':
        if ($method !== 'POST') {
            api_error(405, 'Použijte POST.');
        }

        $sessionUser = api_require_login();
        $postId = api_get_param($jsonBody, 'post_id');
        $body = api_get_param($jsonBody, 'body');

        if (!$postId || !$body) {
            api_error(422, 'Vyplňte ID příspěvku i text komentáře.');
        }

        $created = comment_create((int) $postId, (int) $sessionUser['id'], $body);
        if (!$created) {
            api_error(500, 'Nepodařilo se vytvořit komentář.');
        }

        api_response(201, [
            'success' => true,
        ]);
        break;

    case 'like-post':
        if ($method !== 'POST') {
            api_error(405, 'Použijte POST.');
        }

        $sessionUser = api_require_login();
        $postId = api_get_param($jsonBody, 'post_id');
        if (!$postId) {
            api_error(422, 'Chybí ID příspěvku.');
        }

        $liked = like_toggle((int) $postId, (int) $sessionUser['id']);
        if ($liked === false) {
            api_error(500, 'Nepodařilo se upravit like.');
        }

        api_response(200, [
            'success' => true,
        ]);
        break;

    case 'profile':
        if ($method !== 'GET') {
            api_error(405, 'Použijte GET.');
        }

        $userId = api_get_param($_GET, 'user_id');
        $username = api_get_param($_GET, 'username');

        if ($userId) {
            $user = user_fetch_by_id((int) $userId);
        } elseif ($username) {
            $user = user_fetch_by_username($username);
        } else {
            $sessionUser = api_require_login();
            $user = user_fetch_by_id((int) $sessionUser['id']);
        }

        if (!$user) {
            api_error(404, 'Uživatel nenalezen.');
        }

        api_response(200, [
            'success' => true,
            'user' => api_user_profile($user),
        ]);
        break;

    case 'posts':
        if ($method !== 'GET') {
            api_error(405, 'Použijte GET.');
        }

        $limit = api_get_param($_GET, 'limit');
        $offset = api_get_param($_GET, 'offset');
        $limitValue = $limit !== null ? max(1, (int) $limit) : 50;
        $offsetValue = $offset !== null ? max(0, (int) $offset) : 0;

        $posts = topic_fetch_all($limitValue, $offsetValue);
        $payload = [];
        foreach ($posts as $post) {
            $payload[] = api_post_summary($post);
        }

        api_response(200, [
            'success' => true,
            'posts' => $payload,
        ]);
        break;

    default:
        api_error(404, 'Neznámá akce.');
}
