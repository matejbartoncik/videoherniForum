<?php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../models/user.php';

// Ensure user is logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: ?page=login');
    exit;
}

$pageTitle = 'Profil';
$userId = $_SESSION['user']['id'];
$errors = [];
$success = null;

// Fetch current user data
$user = user_fetch_by_id($userId);

if (!$user) {
    die('User not found');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== csrf_token()) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'update_profile':
                // Validate input
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');

                if (empty($firstName)) {
                    $errors[] = 'Jméno je povinné';
                }
                if (empty($lastName)) {
                    $errors[] = 'Příjmení je povinné';
                }
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Neplatný email';
                }

                // Check if email is already taken by another user
                if (empty($errors)) {
                    $existingUser = user_fetch_by_email($email);
                    if ($existingUser && $existingUser['id'] != $userId) {
                        $errors[] = 'Email již používá jiný uživatel';
                    }
                }

                if (empty($errors)) {
                    if (user_update_profile($userId, [
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'phone' => $phone
                    ])) {
                        $success = 'Profil byl úspěšně aktualizován';
                        // Refresh user data
                        $user = user_fetch_by_id($userId);
                        $_SESSION['user']['username'] = $user['username'];
                    } else {
                        $errors[] = 'Chyba při aktualizaci profilu';
                    }
                }
                break;

            case 'update_avatar':
                if (!empty($_POST['avatar_blob'])) {
                    if (user_update_avatar($userId, $_POST['avatar_blob'])) {
                        $success = 'Avatar byl úspěšně aktualizován';
                        // Refresh user data
                        $user = user_fetch_by_id($userId);
                    } else {
                        $errors[] = 'Chyba při nahrávání avatara';
                    }
                } else {
                    $errors[] = 'Nebyl vybrán žádný obrázek';
                }
                break;

            case 'change_password':
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($currentPassword)) {
                    $errors[] = 'Současné heslo je povinné';
                }
                if (empty($newPassword)) {
                    $errors[] = 'Nové heslo je povinné';
                }
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'Hesla se neshodují';
                }
                if (strlen($newPassword) < 6) {
                    $errors[] = 'Heslo musí mít alespoň 6 znaků';
                }

                if (empty($errors)) {
                    // Verify current password
                    if (!user_verify_password($userId, $currentPassword)) {
                        $errors[] = 'Nesprávné současné heslo';
                    } else {
                        if (user_update_password($userId, $newPassword)) {
                            $success = 'Heslo bylo úspěšně změněno';
                        } else {
                            $errors[] = 'Chyba při změně hesla';
                        }
                    }
                }
                break;
        }
    }
}

$avatarDataUri = user_get_avatar_data_uri($user) ?: 'https://avatar.iran.liara.run/public';
?>
<link rel="stylesheet" href="public/assets/style/profile.css">

<div class="container mt-5">
    <h1 class="mb-4">Můj profil</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Avatar Section -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img id="avatarPreview" src="<?= htmlspecialchars($avatarDataUri) ?>"
                         alt="Avatar" class="rounded-circle mb-3"
                         style="width: 200px; height: 200px; object-fit: cover;">

                    <form method="post" id="avatarForm">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_avatar">
                        <input type="hidden" name="avatar_blob" id="avatarBlob">
                        <input type="file" id="avatarInput" accept="image/*" style="display:none;">
                        <button type="button" class="btn btn-primary" id="uploadAvatarBtn">
                            <i class="fa-solid fa-camera"></i> Změnit avatar
                        </button>
                    </form>

                    <div class="mt-3">
                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                        <div class="text-muted"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Info Section -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Osobní údaje</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jméno</label>
                                <input type="text" name="first_name" class="form-control"
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Příjmení</label>
                                <input type="text" name="last_name" class="form-control"
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Uložit změny
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Změna hesla</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label class="form-label">Současné heslo</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nové heslo</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Potvrzení nového hesla</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-key"></i> Změnit heslo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Avatar upload functionality
    const uploadAvatarBtn = document.getElementById('uploadAvatarBtn');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarBlob = document.getElementById('avatarBlob');
    const avatarForm = document.getElementById('avatarForm');

    uploadAvatarBtn.addEventListener('click', function() {
        avatarInput.click();
    });

    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    // Create canvas for 200x200 crop
                    const canvas = document.createElement('canvas');
                    canvas.width = 200;
                    canvas.height = 200;
                    const ctx = canvas.getContext('2d');

                    // Calculate dimensions to crop to square
                    const size = Math.min(img.width, img.height);
                    const x = (img.width - size) / 2;
                    const y = (img.height - size) / 2;

                    // Draw cropped and resized image
                    ctx.drawImage(img, x, y, size, size, 0, 0, 200, 200);

                    // Update preview
                    avatarPreview.src = canvas.toDataURL('image/jpeg', 0.9);

                    // Set blob value
                    avatarBlob.value = canvas.toDataURL('image/jpeg', 0.9);

                    // Auto-submit the form
                    avatarForm.submit();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>