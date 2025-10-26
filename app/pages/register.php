<?php
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/auth.php';

$pageTitle = 'Registrace';

// Pokud uživatel odeslal formulář metodou POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST, $_FILES);
    if ($result['success']) {
        // Redirect po úspěšné registraci
        header('Location: login.php');
        exit;
    } else {
        // Zobrazení chyb uživateli
        $errors = $result['errors'];
    }
}

// Vloží společný header
include __DIR__.'/../partials/header.php';
?>

<!-- Registrační formulář -->
<form method="post" enctype="multipart/form-data"
      class="mx-auto bg-white p-4 rounded shadow-sm"
      style="max-width:480px;">

    <!-- Nadpis formuláře -->
    <h2 class="text-center mb-4">Registrace</h2>

    <!-- Jméno a příjmení -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="text" class="form-label">Jméno</label>
            <input type="text" id="text" name="firstname" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="text" class="form-label">Příjmení</label>
            <input type="text" id="text" name="lastname" class="form-control" required>
        </div>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>

    <!-- Telefon -->
    <div class="mb-3">
        <label for="tel" class="form-label">Telefon</label>
        <input type="tel" id="tel" name="phone" class="form-control"
               pattern="[0-9+ ]{9,15}" required>
    </div>

    <!-- Pohlaví -->
    <div class="mb-3">
        <label class="form-label d-block">Pohlaví</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" value="M" required>
            <label class="form-check-label">Muž</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" value="F" required>
            <label class="form-check-label">Žena</label>
        </div>
    </div>

    <!-- Profilová fotografie -->
    <div class="mb-3">
        <label for="file" class="form-label">Profilová fotografie</label>
        <input type="file" id="file" name="photo" class="form-control"
               accept=".jpg,.jpeg,.png,.gif,.bmp,.tiff" required>
    </div>

    <!-- Login -->
    <div class="mb-3">
        <label for="text" class="form-label">Login</label>
        <input type="text" id="text" name="login" class="form-control" required>
    </div>

    <!-- Heslo -->
    <div class="mb-3">
        <label for="password" class="form-label">Heslo</label>
        <input type="password" id="password" name="password" class="form-control"
               minlength="6" required>
    </div>

    <!-- Tlačítko odeslání -->
    <button class="btn btn-primary w-100">Registrovat</button>

    <!-- Odkaz na přihlášení -->
    <div class="mt-3 text-center">
        <a href="/app/pages/login.php">Už máš účet? Přihlášení</a>
    </div>
</form>

<?php
// Společný footer
include __DIR__.'/../partials/footer.php';
?>
