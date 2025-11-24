<?php
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../core/csrf.php';

$pageTitle='Přihlášení';

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(auth_login($_POST['login']??'',$_POST['password']??''))
    {
        header('Location:/');
        exit;
    }
    $err='Špatné přihlašovací údaje';
}
?>

<link rel="stylesheet" href="../public/assets/style/login.css">

<div class="login-wrapper">
    <form class="login-form" method="post">
        <h1 class="mb-4">Vítejte!</h1>

        <?php if (!empty($err)): ?>
            <div class="alert alert-danger"><?= $err ?></div>
        <?php endif; ?>

        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token(), ENT_QUOTES)?>">

        <input type="text" name="login" class="form-control" placeholder="Přihlašovací jméno" required>
        <input type="password" name="password" class="form-control" placeholder="Heslo" required>

        <small class="small-text">Nemáte účet? <a href="?page=register">Zaregistrujte se!</a></small>

        <button type="submit">Přihlásit se</button>
    </form>
</div>
