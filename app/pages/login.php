<?php
require_once __DIR__ . '/../core/db.php';

$err = null;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = 'Prosím vyplňte přihlašovací údaje';
    } else {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("
                SELECT id, first_name, last_name, username, email, password_hash, role 
                FROM users 
                WHERE username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                ];

                // Redirect to requested page or home
                $redirect = $_GET['redirect'] ?? 'home';
                header('Location: ?page=' . urlencode($redirect));
                exit;
            } else {
                $err = 'Neplatné přihlašovací údaje';
            }
        } catch (PDOException $e) {
            $err = 'Chyba databáze: ' . $e->getMessage();
        }
    }
}
?>

<div class="container" style="max-width: 420px; margin: 80px auto;">
    <div class="card" style="padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 class="text-center mb-4">
            <i class="fa-solid fa-gamepad me-2"></i>
            Přihlášení
        </h2>

        <?php if ($err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Uživatelské jméno</label>
                <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Heslo</label>
                <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fa-solid fa-sign-in-alt me-2"></i>
                Přihlásit se
            </button>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="mb-2 text-muted">Demo účty:</p>
            <small class="text-muted d-block">
                Admin: <code>admin</code> / <code>password</code><br>
                User: <code>jan</code> / <code>password</code>
            </small>
        </div>

        <div class="text-center mt-3">
            <a href="?page=register" class="text-decoration-none">
                Nemáte účet? Zaregistrujte se
            </a>
        </div>
    </div>
</div>