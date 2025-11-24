<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=htmlspecialchars($pageTitle ?? 'App', ENT_QUOTES)?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/assets/style/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php $currentUser = $_SESSION['user'] ?? null; ?>

<div class="d-flex">
    <!-- SIDEBAR (desktop) -->
    <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark sidebar-shadow sidebar-nav d-none d-md-flex" style="width: 280px; min-height:100vh;">
        <a href="?page=home" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
            <span class="fs-4 align-items-center bg-dark">Videoherní Fórum</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto text-center fs-5">
            <li class="nav-item">
                <a href="?page=home" class="nav-link text-white <?= $pageTitle=='Domů' ? 'active':'' ?>">
                    <i class="fa-solid fa-house"></i> Domů
                </a>
            </li>

            <?php if ($currentUser): ?>
                <li>
                    <a href="?page=messages" class="nav-link text-white <?= $pageTitle=='Zprávy' ? 'active':'' ?>">
                        <i class="fa-solid fa-inbox"></i> Zprávy
                    </a>
                </li>
                <li>
                    <a href="?page=profile" class="nav-link text-white <?= $pageTitle=='Profil' ? 'active':'' ?>">
                        <i class="fa-solid fa-user"></i> Profil
                    </a>
                </li>
                <li>
                    <a href="?page=logout" class="nav-link text-white">
                        <i class="fa-solid fa-sign-out-alt"></i> Odhlásit se
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="?page=login" class="nav-link text-white <?= $pageTitle=='Přihlášení' ? 'active':'' ?>">
                        <i class="fa-solid fa-sign-in-alt"></i> Přihlásit se
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Mobile sidebar - same changes -->
    <!-- (keeping your existing offcanvas code but with same login/logout logic) -->

    <main class="p-4 flex-grow-1">
        <button class="btn btn-outline-secondary d-md-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="fa-solid fa-bars"></i>
        </button>