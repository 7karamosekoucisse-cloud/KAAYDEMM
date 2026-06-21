<?php $myId = $currentUser?->getId(); ?>
<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('messages') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Messagerie</span>
  </a>
</div>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4">
        <!-- Header -->
        <div class="card-header bg-white border-0 p-3 d-flex align-items-center gap-3 rounded-top-4">
          <a href="<?= url('messages') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
          <div class="kd-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
            <i class="bi bi-person-fill text-muted"></i>
          </div>
          <div>
            <div class="fw-bold"><?= e($other->getFullName()) ?></div>
            <div class="text-muted smaller"><i class="bi bi-mortarboard me-1"></i><?= e($other->getCampus()) ?></div>
          </div>
          <div class="ms-auto">
            <a href="<?= url('profile/') . $other->getId() ?>" class="btn btn-sm btn-outline-primary">Profil</a>
          </div>
        </div>
        <!-- Messages -->
        <div class="card-body p-4" id="chatBox" style="height:400px;overflow-y:auto">
          <?php foreach ($messages as $msg): ?>
          <div class="d-flex <?= $msg->getSenderId() === $myId ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
            <div class="kd-chat-bubble <?= $msg->getSenderId() === $myId ? 'kd-chat-mine' : 'kd-chat-other' ?>">
              <div><?= nl2br(e($msg->getContent())) ?></div>
              <div class="text-end mt-1">
                <small class="opacity-75"><?= $msg->getCreatedAt()?->format('H:i') ?></small>
                <?php if ($msg->getSenderId() === $myId && $msg->isRead()): ?>
                  <i class="bi bi-check-all ms-1 text-info small"></i>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- Saisie -->
        <div class="card-footer bg-white border-0 p-3 rounded-bottom-4">
          <form method="POST" action="<?= url('messages/') . $other->getId() ?>" id="msgForm">
            <?= csrf_field() ?>
            <div class="input-group">
              <textarea name="content" class="form-control" placeholder="Votre message..." rows="2" id="msgContent" required maxlength="1000" style="resize:none"></textarea>
              <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send-fill"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Auto-scroll vers le bas
const cb = document.getElementById('chatBox');
cb.scrollTop = cb.scrollHeight;
// Ctrl+Enter pour envoyer
document.getElementById('msgContent').addEventListener('keydown', e => {
  if (e.ctrlKey && e.key === 'Enter') document.getElementById('msgForm').submit();
});
</script>
