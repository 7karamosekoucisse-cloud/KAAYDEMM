<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('admin') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Administration</span>
  </a>
</div>
<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="bi bi-car-front-fill text-success me-2"></i>Demandes conducteur en attente</h3>
  <?php if (empty($pending)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check-circle-fill text-success fs-1"></i>
      <h5 class="mt-3">Aucune demande en attente</h5>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($pending as $u): ?>
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col">
                <h5 class="fw-bold mb-1"><?= e($u->getFullName()) ?></h5>
                <div class="text-muted small"><?= e($u->getEmail()) ?> · <?= e($u->getCampus()) ?></div>
                <?php $dp = $u->getDriverProfile(); ?>
                <?php if ($dp): ?>
                <div class="mt-2 small">
                  <span class="me-3"><i class="bi bi-credit-card me-1"></i>Permis : <strong><?= e($dp->getLicenseNumber()) ?></strong></span>
                  <span class="me-3"><i class="bi bi-car-front me-1"></i><?= e($dp->getVehicleBrand()) ?> <?= e($dp->getVehicleModel()) ?> <?= e($dp->getVehicleColor()) ?></span>
                  <span><i class="bi bi-signpost me-1"></i><?= e($dp->getPlateNumber()) ?></span>
                </div>
                <?php endif; ?>
              </div>
              <div class="col-auto d-flex gap-2">
                <form method="POST" action="<?= url('admin/drivers/') . $u->getId() . '/approve' ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-success fw-bold"><i class="bi bi-check-circle me-1"></i>Approuver</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $u->getId() ?>">
                  <i class="bi bi-x-circle me-1"></i>Refuser
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal refus -->
        <div class="modal fade" id="rejectModal<?= $u->getId() ?>" tabindex="-1">
          <div class="modal-dialog"><div class="modal-content rounded-4">
            <div class="modal-header border-0"><h5 class="modal-title">Refuser la demande</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <form method="POST" action="<?= url('admin/drivers/') . $u->getId() . '/reject' ?>">
                <?= csrf_field() ?>
                <label class="form-label fw-semibold">Motif du refus</label>
                <textarea name="reason" class="form-control mb-3" rows="3" placeholder="Expliquez la raison..." required></textarea>
                <button class="btn btn-danger w-100">Refuser la demande</button>
              </form>
            </div>
          </div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
