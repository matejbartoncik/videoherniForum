<?php
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../core/csrf.php';


// DEMO režim: stránka používá hardcoded data místo databáze
//auth_require();
$pageTitle = "Profil";
//csrf_check();

// Hardcoded uživatel (placeholder)
$user = [
    'id' => 1,
    'first_name' => 'Jan',
    'last_name' => 'Novák',
    'login' => 'jan.novak',
    'role' => 'user',
    'email' => 'jan.novak@example.com',
    'avatar_path' => null, // žádný avatar
    'phone' => '777123456'
];
$phone = $user['phone'];
$alerts = [];
include __DIR__.'/../partials/header.php';
?>

<link rel="stylesheet" href="./../public/assets/style/profile.css">

<div class="profile-container">

    <!-- AVATAR CARD -->
    <div class="card-profile">
        <div class="avatar-wrapper">
            <?php if ($user['avatar_path']): ?>
                <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar_path']) ?>" class="avatar-img" alt="Avatar uživatele">
            <?php else: ?>
                <div class="avatar-placeholder">N/A</div>
            <?php endif; ?>
        </div>

        <div class="profile-name">
            <?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?>
        </div>

        <div class="profile-role <?= $user['role']==='admin'?'role-admin':'role-user' ?>">
            <?= htmlspecialchars($user['role']) ?>
        </div>

        <p class="demo-note">Demo režim – změna avatara není dostupná.</p>
    </div>

    <!-- PERSONAL INFO -->
    <div class="card-section">
        <h3 class="section-title">Osobní údaje</h3>

        <div class="info-row">
            <div class="info-label">Jméno:</div>
            <div class="info-value"><?= htmlspecialchars($user['first_name']) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Příjmení:</div>
            <div class="info-value"><?= htmlspecialchars($user['last_name']) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Login:</div>
            <div class="info-value"><?= htmlspecialchars($user['login']) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Role:</div>
            <div class="info-value"><?= htmlspecialchars($user['role']) ?></div>
        </div>
    </div>

    <!-- CONTACTS (statické) -->
    <div class="card-section">
        <h3 class="section-title">Kontaktní údaje</h3>
        <div class="info-row">
            <div class="info-label">E-mail:</div>
            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Telefon:</div>
            <div class="info-value"><?= htmlspecialchars($phone) ?></div>
        </div>
        <p class="demo-note">Demo režim – úprava kontaktů není dostupná.</p>
    </div>

    <!-- PASSWORD (skryto) -->
    <div class="card-section">
        <h3 class="section-title">Heslo</h3>
        <p class="demo-note">Demo režim – změna hesla není dostupná.</p>
    </div>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
