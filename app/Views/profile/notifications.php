<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>
<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="bi bi-bell-fill text-primary me-2"></i>Notifications</h3>
  <?php if (empty($notifs)): ?>
    <div class="kd-empty text-center py-5 bg-light rounded-4">
      <i class="bi bi-bell fs-1 text-muted"></i>
      <h5 class="mt-3 text-muted">Aucune notification</h5>
    </div>
  <?php else: ?>
    <div class="row g-2">
      <?php foreach ($notifs as $n): ?>
      <div class="col-12">
        <?php $link = $n->getLink() ? '<a href="' . e($n->getLink()) . '" class="stretched-link"></a>' : ''; ?>
        <div class="card border-0 shadow-sm rounded-3 position-relative <?= !$n->isRead() ? 'border-start border-primary border-3' : '' ?>">
          <div class="card-body p-3 d-flex gap-3">
            <div class="kd-notif-icon flex-shrink-0">
              <?php $icons = ['booking' => 'ticket-perforated', 'trip' => 'map', 'review' => 'star', 'system' => 'gear']; ?>
              <i class="bi bi-<?= $icons[$n->getType()] ?? 'bell' ?> text-primary"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold small <?= !$n->isRead() ? 'text-dark' : 'text-muted' ?>"><?= e($n->getTitle()) ?></div>
              <div class="text-muted smaller"><?= e($n->getBody()) ?></div>
            </div>
            <div class="text-muted smaller flex-shrink-0"><?= timeAgo($n->getCreatedAt()?->format('Y-m-d H:i:s')) ?></div>
          </div>
          <?= $link ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
