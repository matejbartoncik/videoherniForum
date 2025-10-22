<?php
// app/pages/home.php

//require_once __DIR__ . '/../core/session.php';
//require_once __DIR__ . '/../core/auth.php';
//require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../models/topic.php';

$pageTitle = 'Novinky';
/*
csrf_check(); // safe: this will validate a POST's csrf if any

$user = auth_user(); // returns user array or null; adapt to your auth API
$isAdmin = function_exists('auth_is_admin') ? auth_is_admin() : (!empty($user) && ($user['role'] ?? '') === 'admin');
*/

//temp
$user = ["id" => 1, "role" => "admin"];
$isAdmin = true;

$err = null;
$success = null;

// Handle admin creating a topic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_topic') {
    if (!$user || !$isAdmin) {
        $err = '401 Unauthorized request';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        if ($title === '') {
            $err = '404 Missing topic name';
        } elseif (strlen($title) > 255) {
            $err = 'Topic name too long';
        } else {
            // sanitize title/body for storage; prepared statements handle SQL injection
            if (topic_create($user['id'], $title, $body)) {
                $success = 'Topic created';
                // Redirect to avoid form resubmission (optional)
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $err = 'Error creating topic';
            }
        }
    }
}



$topics = topic_fetch_all(50, 0);

include __DIR__ . '/../partials/header.php';
?>

<div class="container" style="max-width:900px; margin: 0 auto; padding: 20px;">
    <?php if ($isAdmin): ?>
        <div class="card create-topic mb-4" style="padding:12px; border-radius:12px; background:#fff;">
            <form method="post">
                <input type="hidden" name="action" value="create_topic">
                <?php if (!empty($err)): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>
                <?php if (!empty($success)): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
                <div class="mb-2">
                    <input name="title" class="form-control" placeholder="Váš nadpis..." required maxlength="255">
                </div>
                <div class="mb-2">
                    <textarea name="body" class="form-control" placeholder="Vytvořte nový příspěvek" rows="3"></textarea>
                </div>
                <div style="text-align:right">
                    <button class="btn btn-primary">Vytvořit téma</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <h2>Novinky</h2>

    <?php foreach ($topics as $t): ?>
        <div class="topic-card mb-3" style="background:#fff; padding:12px; border-radius:10px; box-shadow: 0 1px 0 rgba(0,0,0,0.05);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:600;"><?=htmlspecialchars($t['title'])?></div>
                <div style="font-size:12px; color:#666;">
                    <?=htmlspecialchars(date('j. n. Y H:i', strtotime($t['created_at'])))?>
                </div>
            </div>
            <div style="margin-top:8px; color:#444; font-size:14px;">
                <?=nl2br(htmlspecialchars(mb_strimwidth($t['body'], 0, 400, '...')))?>
            </div>
            <div style="margin-top:10px; display:flex; gap:12px; color:#777; font-size:13px;">
                <div>🗨 <?=rand(0,10) /* placeholder for comments count */?></div>
                <div>♡ <?=rand(0,100) /* placeholder for likes */?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
