<!doctype html><html lang="cs"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($pageTitle ?? 'App',ENT_QUOTES)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/main.css" rel="stylesheet">
</head><body>
<nav class="navbar navbar-expand-lg bg-body-tertiary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/app/pages/home.php">Moje App</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/app/pages/home.php">Domů</a></li>
                <li class="nav-item"><a class="nav-link" href="/app/pages/messages.php">Zprávy</a></li>
                <li class="nav-item"><a class="nav-link" href="/app/pages/profile.php">Profil</a></li>
                <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="/app/pages/logout.php">Odhlásit</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
