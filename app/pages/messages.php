<?php
require_once __DIR__.'/../core/session.php';
//require_once __DIR__.'/../core/auth.php';
//require_once __DIR__.'/../core/db.php';
//require_once __DIR__.'/../core/csrf.php';
//require_once __DIR__.'/../core/crypto.php';

//auth_require();
$pageTitle = "Zprávy";
//csrf_check();

// --- Hardcodovaná data (původní DB dotazy zakomentovány) ---
// --- Načíst všechny uživatele pro seznam adresátů ---
// $users = $pdo->query("SELECT id, login, avatar_path FROM users ORDER BY login")->fetchAll();
$users = [
    ['id' => 1, 'login' => 'demo', 'avatar_path' => null],
    ['id' => 2, 'login' => 'martin', 'avatar_path' => 'martin.jpg'],
    ['id' => 3, 'login' => 'jirka', 'avatar_path' => null],
];

// --- Inbox zprávy pro přihlášeného uživatele ---
/*
$stmt = $pdo->prepare("""
    SELECT m.*, u.login AS sender_login, u.avatar_path
    FROM messages m 
    JOIN users u ON m.sender_id = u.id
    WHERE m.recipient_id = ?
    ORDER BY m.created_at DESC
""");
$stmt->execute([$_SESSION['user']['id']]);
$inbox = $stmt->fetchAll();
*/

// zajistíme existenci přihlášeného uživatele pro ukázku
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 1, 'login' => 'demo'];
}

$inbox = [
    [
        'id' => 101,
        'sender_id' => 2,
        'sender_login' => 'martin',
        'avatar_path' => null,
        'recipient_id' => 1,
        'created_at' => '2025-11-16 12:34:00',
        'body' => "Ahoj, toto je testovací zpráva od Martina."
    ],
    [
        'id' => 102,
        'sender_id' => 3,
        'sender_login' => 'jirka',
        'avatar_path' => null,
        'recipient_id' => 1,
        'created_at' => '2025-11-15 09:10:00',
        'body' => "Čau, máš chvilku?"
    ],
];

// --- Detail vybrané zprávy (GET id) ---
$currentMessage = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    foreach ($inbox as $m) {
        if ($m['id'] === $id && $m['recipient_id'] === $_SESSION['user']['id']) {
            $currentMessage = $m;
            break;
        }
    }

    // Pokud by zpráva byla šifrovaná v DB, zde by šla dešifrace (původně):
    /*
    if ($currentMessage) {
        $currentMessage['body'] = aes_gcm_decrypt(
            $currentMessage['body_enc'],
            $currentMessage['body_iv'],
            $currentMessage['body_tag']
        );
    }
    */
}

include __DIR__.'/../partials/header.php';
?>

<!-- CUSTOM MESSAGES CSS -->
<link rel="stylesheet" href="./../public/assets/style/messages.css">

<div class="messages-container ">
    <!-- LEFT — MESSAGE CONTENT -->
    <div class=" left-panel card-section mb-3 mb-lg-0">

        <?php if (!$currentMessage): ?>

            <!-- Empty state -->
            <div class="empty-wrapper text-center py-5">
                <i class="fa-regular fa-envelope-open fa-4x mb-3 text-dark"></i>
                <div class="fw-semibold fs-5">Vyberte zprávu</div>
            </div>

        <?php else: ?>

            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($currentMessage['avatar_path']): ?>
                    <img src="/uploads/avatars/<?= htmlspecialchars($currentMessage['avatar_path']) ?>" class="rounded-circle" width="52" height="52">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:52px;height:52px;">?</div>
                <?php endif; ?>

                <div>
                    <div class="fw-bold"><?= htmlspecialchars($currentMessage['sender_login']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars(substr($currentMessage['created_at'], 0, 16)) ?></div>
                </div>
            </div>

            <div class="message-body-box">
                <p class="mb-0"><?= nl2br(htmlspecialchars($currentMessage['body'])) ?></p>
            </div>

        <?php endif; ?>

    </div>
    <!-- RIGHT — LIST OF SENDERS -->
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
                <a href="?id=<?= $msg['id'] ?>"
                   class="js-msg-link list-group-item list-group-item-action d-flex align-items-center gap-3 <?= (isset($_GET['id']) && $_GET['id']==$msg['id']) ? 'active' : '' ?>"
                   data-msg-id="<?= $msg['id'] ?>"
                   data-msg-body="<?= htmlspecialchars($msg['body'], ENT_QUOTES) ?>"
                   data-msg-sender="<?= htmlspecialchars($msg['sender_login'], ENT_QUOTES) ?>"
                   data-msg-date="<?= htmlspecialchars(substr($msg['created_at'], 0, 16), ENT_QUOTES) ?>"
                   data-msg-avatar="<?= htmlspecialchars($msg['avatar_path'] ?? '', ENT_QUOTES) ?>">
                    <?php if ($msg['avatar_path']): ?>
                        <img src="/uploads/avatars/<?= htmlspecialchars($msg['avatar_path']) ?>" alt="Avatar" class="rounded-circle" width="42" height="42" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:42px;height:42px;">?</div>
                    <?php endif; ?>

                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($msg['sender_login']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars(substr($msg['created_at'], 0, 16)) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>

        </div>

    </div>
</div>



<!-- MODAL — NEW MESSAGE -->
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">

            <input type="hidden" name="action" value="send">

            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Nová zpráva</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <label class="form-label fw-semibold">Příjemce</label>
                <select name="recipient_id" class="form-select mb-3">
                    <?php foreach ($users as $u): ?>
                        <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['login']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <label class="form-label fw-semibold">Zpráva</label>
                <textarea name="body" rows="4" class="form-control" required></textarea>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button>
                <button class="btn btn-primary">Odeslat</button>
            </div>

        </form>
    </div>
</div>


<!-- Offcanvas pro mobilní zobrazení detailu zprávy -->
<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="messageDetailOffcanvas" aria-labelledby="messageDetailLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="messageDetailLabel">Zpráva</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Zavřít"></button>
  </div>
  <div class="offcanvas-body">
    <div id="messageDetailContent">Vyberte zprávu.</div>
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
      ? '<img src="/uploads/avatars/'+escapeHtml(avatar)+'" alt="Avatar" class="rounded-circle me-3" width="52" height="52" style="object-fit:cover;">'
      : '<div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white me-3" style="width:52px;height:52px;">?</div>';

    return '<div class="d-flex align-items-center mb-3">'+avatarHtml+
           '<div><div class="fw-bold">'+escapeHtml(sender)+'</div>'+
           '<div class="text-muted small">'+escapeHtml(date)+'</div></div></div>'+
           '<div class="message-body-box"><p class="mb-0">'+escapeHtml(body).replace(/\n/g,'<br>')+'</p></div>';
  }

  function openOffcanvas(){
    document.body.classList.add('offcanvas-open');
    var backdrop = document.createElement('div');
    backdrop.className = 'offcanvas-backdrop-custom';
    backdrop.onclick = closeOffcanvas;
    document.body.appendChild(backdrop);
    offcanvasEl.classList.add('show');
    offcanvasEl.style.cssText = 'visibility:visible;transform:translateX(0);transition:transform 0.25s ease';
  }

  function closeOffcanvas(){
    document.body.classList.remove('offcanvas-open');
    var backdrop = document.querySelector('.offcanvas-backdrop-custom');
    if (backdrop) backdrop.remove();
    offcanvasEl.classList.remove('show');
    offcanvasEl.style.transform = '';
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
          } else {
            openOffcanvas();
          }
          history.replaceState(null, '', link.href);
        } catch(e) {
          openOffcanvas();
        }
      }
    });
  });

  var closeBtn = offcanvasEl.querySelector('.btn-close');
  if (closeBtn) {
    closeBtn.onclick = function(){
      try {
        if (window.bootstrap?.Offcanvas) {
          bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).hide();
        } else {
          closeOffcanvas();
        }
      } catch(e) {
        closeOffcanvas();
      }
    };
  }

  offcanvasEl.addEventListener('show.bs.offcanvas', function(ev){
    var trigger = ev.relatedTarget;
    if (!trigger) return;
    var content = document.getElementById('messageDetailContent');
    if (!content) return;

    content.innerHTML = buildContent(
      trigger.getAttribute('data-msg-body') || '',
      trigger.getAttribute('data-msg-sender') || '',
      trigger.getAttribute('data-msg-date') || '',
      trigger.getAttribute('data-msg-avatar') || ''
    );
  });
})();
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>
