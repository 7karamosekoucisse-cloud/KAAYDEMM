<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('admin') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Administration</span>
  </a>
</div>
<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="bi bi-flag-fill text-danger me-2"></i>Signalements en attente</h3>
  <?php if (empty($reports)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check-circle-fill text-success fs-1"></i>
      <h5 class="mt-3">Aucun signalement en attente</h5>
    </div>
  <?php else: ?>
    <?php foreach ($reports as $r): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-3">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col">
            <div class="fw-bold"><?= e($r->getReporterName() ?? 'Inconnu') ?> signale un <?= e($r->getTargetType()) ?></div>
            <div class="small text-muted">Motif : <strong><?= e($r->getReason()) ?></strong></div>
            <?php if ($r->getDetails()): ?><div class="small text-muted mt-1"><?= e($r->getDetails()) ?></div><?php endif; ?>
            <div class="small text-muted"><?= $r->getCreatedAt()?->format('d/m/Y H:i') ?></div>
          </div>
          <div class="col-auto">
            <form method="POST" action="<?= url('admin/reports/') . $r->getId() . '/resolve' ?>">
              <?= csrf_field() ?>
              <div class="d-flex gap-2">
                <input type="text" name="resolution" class="form-control form-control-sm" placeholder="Résolution..." required>
                <button class="btn btn-sm btn-success">Traiter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
