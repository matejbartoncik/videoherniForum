<!doctype html>
<html lang="cs">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=htmlspecialchars($pageTitle ?? 'App', ENT_QUOTES)?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/app/public/assets/style/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="d-flex">

    <!-- SIDEBAR (desktop: viditelný od md; mobil: skrytý) -->
    <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark sidebar-shadow sidebar-nav d-none d-md-flex" style="width: 280px; min-height:100vh;">
        <a href="/app/pages/home.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
            <span class="fs-4 align-items-center bg-dark ">Videoherní Fórum</span>
        </a>

        <hr>

        <ul class="nav nav-pills flex-column mb-auto text-center fs-5 ">
            <li class="nav-item ">
                <a href="./app/pages/home.php" class="nav-link text-white <?= $pageTitle=='Domů' ? 'active':'' ?>">
                    <i class="fa-solid fa-house"></i>
                    Domů
                </a>
            </li>

            <li>
                <a href="/app/pages/messages.php" class="nav-link text-white <?= $pageTitle=='Zprávy' ? 'active':'' ?>">
                    <i class="fa-solid fa-inbox"></i>
                    Zprávy

                </a>
            </li>

            <li>
                <a href="/app/pages/profile.php" class="nav-link text-white <?= $pageTitle=='Profil' ? 'active':'' ?>">
                    <i class="fa-solid fa-user"></i>
                    Profil

                </a>
            </li>

            <li>
                <a href="/app/pages/logout.php" class="nav-link text-white">
                    Odhlásit se
                </a>
            </li>
        </ul>

    </div>

    <!-- OFFCANVAS SIDEBAR (mobilní zařízení) -->
    <div class="offcanvas offcanvas-start d-flex d-md-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Videoherní Fórum</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Zavřít"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark" style="width: 280px;">
                <hr>
                <ul class="nav nav-pills flex-column mb-auto text-center fs-5">
                    <li class="nav-item ">
                        <a href="/app/pages/home.php" class="nav-link text-white <?= $pageTitle=='Domů' ? 'active':'' ?>">
                            <i class="fa-solid fa-house"></i>
                            Domů
                        </a>
                    </li>

                    <li>
                        <a href="./../pages/messages.php" class="nav-link text-white <?= $pageTitle=='Zprávy' ? 'active':'' ?>">
                            <i class="fa-solid fa-inbox"></i>
                            Zprávy

                        </a>
                    </li>

                    <li>
                        <a href="./../pages/profile.php" class="nav-link text-white <?= $pageTitle=='Profil' ? 'active':'' ?>">
                            <i class="fa-solid fa-user"></i>
                            Profil

                        </a>
                    </li>

                    <li>
                        <a href="./../pages/logout.php" class="nav-link text-white">
                            Odhlásit se
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <main class="p-4 flex-grow-1">
        <!-- tlačítko pro otevření sidebaru na malých zařízeních -->
        <button class="btn btn-outline-secondary d-md-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="fa-solid fa-bars"></i>
        </button>
