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

<div class="profile-container">
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

    <!-- Avatar Section -->
    <div class="card-profile">
        <div class="avatar-wrapper">
            <img id="avatarPreview" src="<?= htmlspecialchars($avatarDataUri) ?>"
                 alt="Avatar" class="avatar-img">
        </div>

        <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
        <div class="profile-role role-<?= strtolower($user['role']) ?>">
            <?= htmlspecialchars($user['role']) ?>
        </div>

        <form method="post" id="avatarForm" class="avatar-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_avatar">
            <input type="hidden" name="avatar_blob" id="avatarBlob">
            <input type="file" id="avatarInput" accept="image/*" style="display:none;">
            <button type="button" class="btn-main" id="uploadAvatarBtn">
                <i class="fa-solid fa-camera"></i> Změnit avatar
            </button>
        </form>
    </div>

    <!-- Profile Info Section -->
    <div class="card-section">
        <h5 class="section-title">Osobní údaje</h5>
        <form method="post" class="form-section">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">

            <div>
                <label for="first_name" class="input-label">Jméno</label>
                <input type="text" id="first_name" name="first_name" class="input-field"
                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>

            <div>
                <label for="last_name" class="input-label">Příjmení</label>
                <input type="text" id="last_name" name="last_name" class="input-field"
                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>

            <div>
                <label for="email" class="input-label">Email</label>
                <input type="email" id="email" name="email" class="input-field"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div>
                <label for="phone" class="input-label">Telefon</label>
                <input type="tel" id="phone" name="phone" class="input-field"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-main">
                <i class="fa-solid fa-save"></i> Uložit změny
            </button>
        </form>
    </div>

    <!-- Password Change Section -->
    <div class="card-section">
        <h5 class="section-title">Změna hesla</h5>
        <form method="post" class="form-section">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">

            <div>
                <label for="current_password" class="input-label">Současné heslo</label>
                <input type="password" id="current_password" name="current_password" class="input-field" required>
            </div>

            <div>
                <label for="new_password" class="input-label">Nové heslo</label>
                <input type="password" id="new_password" name="new_password" class="input-field" required>
            </div>

            <div>
                <label for="confirm_password" class="input-label">Potvrzení nového hesla</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input-field" required>
            </div>

            <button type="submit" class="btn-main">
                <i class="fa-solid fa-key"></i> Změnit heslo
            </button>
        </form>
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