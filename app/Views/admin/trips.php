<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('admin') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Administration</span>
  </a>
</div>
<div class="container-fluid py-4">
  <h3 class="fw-bold mb-4"><i class="bi bi-map-fill text-primary me-2"></i>Gestion des trajets</h3>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Trajet</th><th>Conducteur</th><th>Date</th><th>Places</th><th>Prix</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($trips as $t): ?>
          <tr>
            <td><strong><?= e($t->getDepartureCity()) ?> → <?= e($t->getArrivalCity()) ?></strong></td>
            <td class="small text-muted">ID #<?= $t->getDriverId() ?></td>
            <td class="small"><?= $t->getDepartureAt()?->format('d/m/Y H:i') ?></td>
            <td><?= $t->getSeatsAvailable() ?>/<?= $t->getSeatsOffered() ?></td>
            <td><?= formatPrice($t->getPricePerSeat()) ?></td>
            <td><span class="badge <?= $t->getStatus()->badgeClass() ?>"><?= $t->getStatus()->label() ?></span></td>
            <td><a href="<?= url('trips/') . $t->getId() ?>" class="btn btn-sm btn-outline-primary">Voir</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>
