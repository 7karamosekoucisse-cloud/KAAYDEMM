<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-map-fill text-primary me-2"></i>Mes trajets</h3>
    <a href="<?= url('trips/create') ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Publier un trajet</a>
  </div>
  <?php if (empty($trips)): ?>
    <div class="kd-empty text-center py-5">
      <i class="bi bi-map fs-1 text-muted"></i>
      <h5 class="mt-3 text-muted">Aucun trajet publié</h5>
      <a href="<?= url('trips/create') ?>" class="btn btn-primary mt-2">Publier mon premier trajet</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($trips as $trip): ?>
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col">
                <h5 class="fw-bold mb-1">
                  <?= e($trip->getDepartureCity()) ?> → <?= e($trip->getArrivalCity()) ?>
                  <span class="badge <?= $trip->getStatus()->badgeClass() ?> ms-2 fs-6"><?= $trip->getStatus()->label() ?></span>
                </h5>
                <div class="text-muted small">
                  <i class="bi bi-calendar me-1"></i><?= $trip->getDepartureAt()?->format('d/m/Y H:i') ?>
                  &nbsp;·&nbsp;
                  <i class="bi bi-person-fill me-1"></i><?= $trip->getSeatsAvailable() ?>/<?= $trip->getSeatsOffered() ?> places
                  &nbsp;·&nbsp;
                  <?= formatPrice($trip->getPricePerSeat()) ?>/place
                </div>
              </div>
              <div class="col-auto d-flex gap-2">
                <a href="<?= url('trips/') . $trip->getId() ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                <?php if (in_array($trip->getStatus()->value, ['OPEN','FULL'])): ?>
                <a href="<?= url('trips/') . $trip->getId() . '/edit' ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('trips/') . $trip->getId() . '/cancel' ?>" class="d-inline" onsubmit="return confirm('Annuler ce trajet ?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
