<?php
require_once __DIR__ . '/../models/topic.php';
require_once __DIR__ . '/../models/comment.php';
require_once __DIR__ . '/../models/like.php';

$user = $_SESSION['user'] ?? null;
$isAdmin = $user && ($user['role'] ?? '') === 'admin';

$err = null;
$success = null;

// Handle topic creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_topic') {
    if (!$user || !$isAdmin) {
        $err = 'Unauthorized - admin only';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        if ($title === '') {
            $err = 'Missing topic title';
        } elseif (strlen($title) > 200) {
            $err = 'Topic name too long (max 200 characters)';
        } else {
            if (topic_create($user['id'], $title, $body)) {
                $success = 'Topic created';
                header('Location: ?page=home');
                exit;
            } else {
                $err = 'Error creating topic';
            }
        }
    }
}

// Handle like toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    if ($user) {
        $post_id = (int)($_POST['post_id'] ?? 0);
        if ($post_id > 0) {
            like_toggle($post_id, $user['id']);
        }
    }
    header('Location: ?page=home');
    exit;
}

// Handle comment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    if ($user) {
        $post_id = (int)($_POST['post_id'] ?? 0);
        $body = trim($_POST['comment_body'] ?? '');
        if ($post_id > 0 && $body !== '') {
            comment_create($post_id, $user['id'], $body);
        }
    }
    header('Location: ?page=home#post-' . $post_id);
    exit;
}

// Fetch topics from database
$topics = topic_fetch_all(50, 0);

// For each topic, get comments and check if user has liked it
foreach ($topics as &$topic) {
    $topic['user_has_liked'] = $user ? like_exists($topic['id'], $user['id']) : false;
    $topic['comments'] = comment_fetch_by_post($topic['id']);
}
unset($topic);

?>

<link rel="stylesheet" href="public/assets/style/topics.css">

<div class="container" style="max-width:900px; margin: 0 auto; padding: 20px;">

    <?php if (!$user): ?>
        <div class="alert alert-info mb-4">
            <i class="fa-solid fa-info-circle me-2"></i>
            <a href="?page=login" class="alert-link">Přihlaste se</a> pro přístup ke všem funkcím fóra.
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <div class="card create-topic mb-4" style="padding:16px; border-radius:12px; background:#fff;">
            <form method="post">
                <input type="hidden" name="action" value="create_topic">
                <?php if (!empty($err)): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                <div class="mb-2">
                    <input name="title" class="form-control" placeholder="Váš nadpis..." required maxlength="200">
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

    <?php if (empty($topics)): ?>
        <div class="alert alert-info">
            Zatím zde nejsou žádné příspěvky. <?php if ($isAdmin): ?>Vytvořte první!<?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($topics as $t): ?>
            <div class="topic-card" id="post-<?= $t['id'] ?>" data-post-id="<?= $t['id'] ?>">
                <div class="topic-header">
                    <div class="topic-title"><?= htmlspecialchars($t['title']) ?></div>
                    <div class="topic-date"><?= htmlspecialchars(date('j. n. Y H:i', strtotime($t['created_at']))) ?></div>
                </div>

                <div class="topic-author">
                    <i class="fa-solid fa-user me-1"></i>
                    <?= htmlspecialchars($t['author_username']) ?>
                </div>

                <div class="topic-content collapsed">
                    <?= nl2br(htmlspecialchars($t['content'])) ?>
                </div>

                <div class="topic-actions">
                    <?php if ($user): ?>
                        <form method="post" style="display:inline-block; margin:0;">
                            <input type="hidden" name="action" value="toggle_like">
                            <input type="hidden" name="post_id" value="<?= $t['id'] ?>">
                            <button type="submit" class="topic-action-btn <?= $t['user_has_liked'] ? 'liked' : '' ?>">
                                <i class="fa<?= $t['user_has_liked'] ? 's' : 'r' ?> fa-heart"></i>
                                <span><?= (int)$t['likes_count'] ?></span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="topic-action-btn">
                            <i class="far fa-heart"></i>
                            <span><?= (int)$t['likes_count'] ?></span>
                        </div>
                    <?php endif; ?>

                    <button class="topic-action-btn comment-toggle" type="button">
                        <i class="far fa-comment"></i>
                        <span><?= (int)$t['comments_count'] ?></span>
                    </button>

                    <div class="expand-indicator">
                        <span>Zobrazit více</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Comments section (hidden by default) -->
                <div class="comments-section" style="display:none;">
                    <div class="comments-header">
                        <i class="far fa-comments me-2"></i>
                        Komentáře (<?= (int)$t['comments_count'] ?>)
                    </div>

                    <div class="comments-list">
                        <?php if (empty($t['comments'])): ?>
                            <div class="no-comments">Zatím žádné komentáře</div>
                        <?php else: ?>
                            <?php foreach ($t['comments'] as $comment): ?>
                                <div class="comment-item">
                                    <div class="d-flex justify-content-between">
                                        <div class="comment-author">
                                            <?= htmlspecialchars($comment['author_username']) ?>
                                        </div>
                                        <div class="comment-date">
                                            <?= htmlspecialchars(date('j. n. Y H:i', strtotime($comment['created_at']))) ?>
                                        </div>
                                    </div>
                                    <div class="comment-body">
                                        <?= nl2br(htmlspecialchars($comment['body'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($user): ?>
                        <div class="comment-form">
                            <form method="post" action="?page=home#post-<?= $t['id'] ?>">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="post_id" value="<?= $t['id'] ?>">
                                <textarea name="comment_body" placeholder="Napište komentář..." required></textarea>
                                <div class="comment-form-actions">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="far fa-paper-plane me-1"></i>
                                        Odeslat
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            <a href="?page=login">Přihlaste se</a> pro přidání komentáře.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle topic expansion
        document.querySelectorAll('.topic-card').forEach(card => {
            const content = card.querySelector('.topic-content');
            const indicator = card.querySelector('.expand-indicator');
            const commentsSection = card.querySelector('.comments-section');

            // Click on card or indicator to expand
            const expandHandler = function(e) {
                // Don't expand if clicking on buttons or forms
                if (e.target.closest('button, form, a')) return;

                card.classList.toggle('expanded');
                content.classList.toggle('collapsed');

                if (card.classList.contains('expanded')) {
                    indicator.querySelector('span').textContent = 'Zobrazit méně';
                } else {
                    indicator.querySelector('span').textContent = 'Zobrazit více';
                }
            };

            card.addEventListener('click', expandHandler);

            // Comment toggle button
            const commentToggle = card.querySelector('.comment-toggle');
            if (commentToggle) {
                commentToggle.addEventListener('click', function(e) {
                    e.stopPropagation();

                    if (commentsSection.style.display === 'none') {
                        commentsSection.style.display = 'block';
                    } else {
                        commentsSection.style.display = 'none';
                    }
                });
            }
        });

        // Auto-expand if there's a hash in URL (e.g., after adding a comment)
        if (window.location.hash) {
            const targetCard = document.querySelector(window.location.hash);
            if (targetCard && targetCard.classList.contains('topic-card')) {
                targetCard.classList.add('expanded');
                targetCard.querySelector('.topic-content')?.classList.remove('collapsed');
                targetCard.querySelector('.comments-section').style.display = 'block';
                targetCard.querySelector('.expand-indicator span').textContent = 'Zobrazit méně';
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>