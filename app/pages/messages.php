<?php
require_once __DIR__ . '/../models/message.php';

// Ensure user session exists
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 1, 'username' => 'demo'];
}

$err = null;
$success = null;

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $recipient_id = (int)($_POST['recipient_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');
    $subject = trim($_POST['subject'] ?? '');

    if ($recipient_id <= 0) {
        $err = 'Please select a recipient';
    } elseif ($body === '') {
        $err = 'Message body cannot be empty';
    } else {
        if (message_send($_SESSION['user']['id'], $recipient_id, $subject, $body)) {
            $success = 'Message sent successfully';
            header('Location: ?page=messages');
            exit;
        } else {
            $err = 'Error sending message';
        }
    }
}

// Fetch users for recipient selection
$users = message_fetch_all_users($_SESSION['user']['id']);

// Fetch inbox messages
$inbox = message_fetch_inbox($_SESSION['user']['id']);

// Get current message detail
$currentMessage = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $currentMessage = message_fetch_by_id($id, $_SESSION['user']['id']);

    // Mark as read if recipient is viewing
    if ($currentMessage && $currentMessage['recipient_id'] === $_SESSION['user']['id']) {
        message_mark_as_read($id, $_SESSION['user']['id']);
    }
}
?>

<!-- CUSTOM MESSAGES CSS -->
<link rel="stylesheet" href="public/assets/style/messages.css">

<div class="container mt-4">
    <?php if (!empty($err)): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="messages-container">
        <!-- LEFT — MESSAGE CONTENT -->
        <div class="left-panel card-section mb-3 mb-lg-0">

            <?php if (!$currentMessage): ?>

                <!-- Empty state -->
                <div class="empty-wrapper text-center py-5">
                    <i class="fa-regular fa-envelope-open fa-4x mb-3 text-dark"></i>
                    <div class="fw-semibold fs-5">Vyberte zprávu</div>
                </div>

            <?php else: ?>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if ($currentMessage['sender_avatar']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($currentMessage['sender_avatar']) ?>" class="rounded-circle" width="52" height="52" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:52px;height:52px;">
                            <?= strtoupper(substr($currentMessage['sender_username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($currentMessage['sender_username']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(date('j. n. Y H:i', strtotime($currentMessage['created_at']))) ?></div>
                    </div>
                </div>

                <?php if ($currentMessage['subject']): ?>
                    <div class="mb-2">
                        <strong>Předmět:</strong> <?= htmlspecialchars($currentMessage['subject']) ?>
                    </div>
                <?php endif; ?>

                <div class="message-body-box">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($currentMessage['body'])) ?></p>
                </div>

            <?php endif; ?>

        </div>

        <!-- RIGHT — LIST OF MESSAGES -->
        <div class="right-panel card-section">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="fa-solid fa-envelope-open-text me-2"></i>
                    Zprávy
                </h5>

                <!-- New Message Button -->
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                    <i class="fa-solid fa-pencil"></i>
                </button>
            </div>

            <div class="list-group message-list">

                <?php if (empty($inbox)): ?>
                    <div class="text-center py-5 opacity-75">
                        <i class="fa-regular fa-envelope fa-3x mb-3"></i>
                        <div>Žádné zprávy</div>
                    </div>
                <?php endif; ?>

                <?php foreach ($inbox as $msg): ?>
                    <a href="?page=messages&id=<?= $msg['id'] ?>"
                       class="js-msg-link list-group-item list-group-item-action d-flex align-items-center gap-3 <?= (isset($_GET['id']) && $_GET['id']==$msg['id']) ? 'active' : '' ?> <?= $msg['read_at'] ? '' : 'fw-bold' ?>"
                       data-msg-id="<?= $msg['id'] ?>"
                       data-msg-body="<?= htmlspecialchars($msg['body'], ENT_QUOTES) ?>"
                       data-msg-sender="<?= htmlspecialchars($msg['sender_username'], ENT_QUOTES) ?>"
                       data-msg-date="<?= htmlspecialchars(date('j. n. Y H:i', strtotime($msg['created_at'])), ENT_QUOTES) ?>"
                       data-msg-avatar="<?= $msg['sender_avatar'] ? base64_encode($msg['sender_avatar']) : '' ?>">
                        <?php if ($msg['sender_avatar']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($msg['sender_avatar']) ?>" alt="Avatar" class="rounded-circle" width="42" height="42" style="object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:42px;height:42px;">
                                <?= strtoupper(substr($msg['sender_username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= htmlspecialchars($msg['sender_username']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars(date('j. n. Y H:i', strtotime($msg['created_at']))) ?></div>
                            <?php if (!$msg['read_at']): ?>
                                <span class="badge bg-primary">Nová</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>

            </div>

        </div>
    </div>
</div>

<!-- OFFCANVAS — MESSAGE DETAIL (for mobile) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="messageDetailOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Detail zprávy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="messageDetailContent">
        <!-- Content will be injected by JavaScript -->
    </div>
</div>

<!-- MODAL — NEW MESSAGE -->
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="?page=messages" class="modal-content">

            <input type="hidden" name="action" value="send">

            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Nová zpráva</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <label class="form-label fw-semibold">Příjemce</label>
                <select name="recipient_id" class="form-select mb-3" required>
                    <option value="">-- Vyberte příjemce --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label fw-semibold">Předmět (volitelné)</label>
                <input type="text" name="subject" class="form-control mb-3" maxlength="150">

                <label class="form-label fw-semibold">Zpráva</label>
                <textarea name="body" rows="4" class="form-control" required></textarea>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button>
                <button type="submit" class="btn btn-primary">Odeslat</button>
            </div>

        </form>
    </div>
</div>

<script>
    (function(){
        var offcanvasEl = document.getElementById('messageDetailOffcanvas');
        if (!offcanvasEl) return;

        function escapeHtml(str){
            return String(str).replace(/[&<>"']/g, function(ch){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[ch];
            });
        }

        function buildContent(body, sender, date, avatar){
            var avatarHtml = avatar
                ? '<img src="data:image/jpeg;base64,'+avatar+'" alt="Avatar" class="rounded-circle me-3" width="52" height="52" style="object-fit:cover;">'
                : '<div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white me-3" style="width:52px;height:52px;">'+sender.charAt(0).toUpperCase()+'</div>';

            return '<div class="d-flex align-items-center mb-3">'+avatarHtml+
                '<div><div class="fw-bold">'+escapeHtml(sender)+'</div>'+
                '<div class="text-muted small">'+escapeHtml(date)+'</div></div></div>'+
                '<div class="message-body-box"><p class="mb-0">'+escapeHtml(body).replace(/\n/g,'<br>')+'</p></div>';
        }

        document.querySelectorAll('.js-msg-link').forEach(function(link){
            link.addEventListener('click', function(e){
                if (window.innerWidth < 992 && e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                    e.preventDefault();
                    var content = document.getElementById('messageDetailContent');
                    if (!content) return;

                    content.innerHTML = buildContent(
                        link.getAttribute('data-msg-body') || '',
                        link.getAttribute('data-msg-sender') || '',
                        link.getAttribute('data-msg-date') || '',
                        link.getAttribute('data-msg-avatar') || ''
                    );

                    try {
                        if (window.bootstrap?.Offcanvas) {
                            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
                        }
                    } catch(err) {}
                }
            });
        });
    })();
</script>