<?php
require_once __DIR__.'/app/core/session.php';
require_once __DIR__.'/app/core/auth.php';
require_once __DIR__.'/app/core/csrf.php';
$pageTitle='Přihlášení';// csrf_check();
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(auth_login($_POST['login']??'',$_POST['password']??'')){ header('Location:/app/pages/home.php'); exit; }
    $err='Špatné přihlašovací údaje';
}
include __DIR__.'/../partials/header.php';
?>
<form method="post" class="mx-auto bg-white p-4 rounded shadow-sm" style="max-width:420px">
    <?php if(!empty($err)):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
    <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token(),ENT_QUOTES)?>">
    <div class="mb-3"><label class="form-label">Login</label><input name="login" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Heslo</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-primary w-100">Přihlásit</button>
    <div class="mt-3 text-center"><a href="/app/pages/register.php">Nemáš účet? Registrace</a></div>
</form>
<?php include __DIR__.'/../partials/footer.php'; ?>
