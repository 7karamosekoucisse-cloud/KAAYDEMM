<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>
<div class="container py-4">
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
      <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <h3 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Messagerie</h3>
  </div>

  <?php if (empty($inbox)): ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
      <i class="bi bi-chat-dots fs-1 text-muted"></i>
      <h5 class="mt-3 text-muted">Aucun message pour l'instant</h5>
      <p class="text-muted small">Réservez un trajet pour contacter un conducteur.</p>
      <a href="<?= url('search') ?>" class="btn btn-primary mt-2">Trouver un trajet</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($inbox as $conv): ?>
      <?php
        $myId    = $currentUser?->getId();
        $otherId = ((int)($conv['sender_id'] ?? 0) === $myId)
                   ? (int)($conv['recipient_id'] ?? 0)
                   : (int)($conv['sender_id'] ?? 0);
        $convUrl = url('messages/' . $otherId);
        $unread  = (int)($conv['unread_count'] ?? 0);
      ?>
      <div class="col-12">
        <a href="<?= $convUrl ?>"
           class="card border-0 shadow-sm rounded-4 text-decoration-none hover-lift <?= $unread > 0 ? 'border-start border-4 border-primary' : '' ?>">
          <div class="card-body p-3 d-flex align-items-center gap-3">
            <div class="kd-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10">
              <i class="bi bi-person-fill text-primary fs-5"></i>
            </div>
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-semibold <?= $unread > 0 ? 'text-dark' : 'text-muted' ?>">
                <?= e($conv['sender_name'] ?? 'Utilisateur') ?>
              </div>
              <div class="text-muted small text-truncate"><?= e($conv['content'] ?? '') ?></div>
            </div>
            <div class="text-end flex-shrink-0">
              <?php if ($unread > 0): ?>
                <span class="badge bg-primary rounded-pill mb-1"><?= $unread ?></span><br>
              <?php endif; ?>
              <span class="text-muted" style="font-size:.75rem"><?= timeAgo($conv['created_at'] ?? '') ?></span>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
