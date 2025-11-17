<?php
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../core/csrf.php';

$pageTitle = 'Registrace'; csrf_check();

// Handle form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST, $_FILES);
    if ($result['success']) {
        // Redirect after successful registration
        header('Location: login.php');
        exit;
    } else {
        // Store errors to display the user
        $errors = $result['errors'];
    }
}
?>

<link rel="stylesheet" href="../public/assets/style/register.css">

<div class="register-wrapper">
    <form class="register-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token(),ENT_QUOTES)?>">

        <h1>Registrace</h1>

        <div class="avatar-wrapper">
            <div class="avatar-placeholder"></div>
            <button type="button" class="upload-btn">Nahrát obrázek</button>
        </div>

        <div class="form-group">
            <label>Přezdívka</label>
            <input type="text" name="nickname" placeholder="Vaše přezdívka" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Heslo</label>
                <input type="password" name="password" placeholder="Vaše heslo" required>
            </div>
            <div class="form-group">
                <label>Heslo - kontrola</label>
                <input type="password" name="password_confirm" placeholder="Heslo znovu" required>
                <span class="error-message" style="color:red; font-size:0.9rem; display:none;"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Jméno</label>
                <input type="text" name="firstname" placeholder="Vaše jméno" required>
            </div>
            <div class="form-group">
                <label>Příjmení</label>
                <input type="text" name="lastname" placeholder="Vaše příjmení" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Váš email" required>
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="phone" placeholder="Vaše telefonní číslo" required>
            </div>
        </div>

        <button type="submit" class="register-btn">Zaregistrovat se</button>
    </form>

    <script>
        const form = document.querySelector(".register-form");
        const password = form.querySelector("input[name='password']");
        const passwordConfirm = form.querySelector("input[name='password_confirm']");
        const errorMessage = passwordConfirm.nextElementSibling;

        form.addEventListener("submit", function(e) {
            if (password.value !== passwordConfirm.value) {
                e.preventDefault();
                errorMessage.textContent = "Hesla se musí shodovat!";
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }
        });
    </script>
</div>
