<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>
<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Mes réservations</h3>
  <div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="<?= url('bookings') ?>" class="btn btn-sm <?= !($status??'') ? 'btn-primary' : 'btn-outline-secondary' ?>">Toutes</a>
    <a href="<?= url('bookings?status=PENDING') ?>" class="btn btn-sm <?= ($status??'')==='PENDING' ? 'btn-warning' : 'btn-outline-secondary' ?>">En attente</a>
    <a href="<?= url('bookings?status=CONFIRMED') ?>" class="btn btn-sm <?= ($status??'')==='CONFIRMED' ? 'btn-success' : 'btn-outline-secondary' ?>">Confirmées</a>
    <a href="<?= url('bookings?status=COMPLETED') ?>" class="btn btn-sm <?= ($status??'')==='COMPLETED' ? 'btn-info' : 'btn-outline-secondary' ?>">Terminées</a>
    <a href="<?= url('bookings?status=CANCELLED') ?>" class="btn btn-sm <?= ($status??'')==='CANCELLED' ? 'btn-danger' : 'btn-outline-secondary' ?>">Annulées</a>
  </div>
  <?php if (empty($bookings)): ?>
    <div class="kd-empty text-center py-5">
      <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
      <h5 class="mt-3 text-muted">Aucune réservation</h5>
      <a href="<?= url('search') ?>" class="btn btn-primary mt-2">Rechercher un trajet</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($bookings as $b): ?>
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="fw-bold mb-1">
                  <?= e($b->getTripDeparture() ?? '') ?> → <?= e($b->getTripArrival() ?? '') ?>
                  <span class="badge <?= $b->getStatus()->badgeClass() ?> ms-2"><?= $b->getStatus()->label() ?></span>
                </h6>
                <div class="text-muted small">
                  <i class="bi bi-calendar me-1"></i><?= $b->getTripDate()?->format('d/m/Y H:i') ?>
                  &nbsp;·&nbsp; <?= $b->getSeatsBooked() ?> place(s) · <?= formatPrice($b->getTotalPrice()) ?>
                  &nbsp;·&nbsp; Conducteur : <?= e($b->getDriverName() ?? '') ?>
                </div>
              </div>
              <div class="col-auto">
                <a href="<?= url('bookings/') . $b->getId() ?>" class="btn btn-sm btn-outline-primary">Voir</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
