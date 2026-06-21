<?php $user = $currentUser ?? null; ?>
<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('bookings') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mes réservations</span>
  </a>
</div>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
              <h3 class="fw-bold mb-1">Réservation #<?= $booking->getId() ?></h3>
              <p class="text-muted mb-0">Créée le <?= $booking->getCreatedAt()?->format('d/m/Y à H:i') ?></p>
            </div>
            <span class="badge fs-5 <?= $booking->getStatus()->badgeClass() ?> rounded-pill px-3">
              <?= $booking->getStatus()->label() ?>
            </span>
          </div>

          <!-- Trajet -->
          <div class="kd-booking-detail bg-light rounded-3 p-3 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-geo-alt text-primary me-2"></i>
              <?= e($booking->getTripDeparture() ?? '') ?> → <?= e($booking->getTripArrival() ?? '') ?>
            </h5>
            <div class="text-muted small">
              <i class="bi bi-calendar me-1"></i>
              <?= $booking->getTripDate()?->format('d/m/Y à H:i') ?>
              &nbsp;·&nbsp; Conducteur : <strong><?= e($booking->getDriverName() ?? '') ?></strong>
            </div>
          </div>

          <!-- Résumé financier -->
          <div class="row g-3 mb-4">
            <div class="col-4 text-center">
              <div class="fw-bold fs-4 text-primary"><?= $booking->getSeatsBooked() ?></div>
              <div class="text-muted small">Place(s)</div>
            </div>
            <div class="col-4 text-center">
              <div class="fw-bold fs-4 text-success"><?= formatPrice($booking->getTotalPrice()) ?></div>
              <div class="text-muted small">Total</div>
            </div>
            <div class="col-4 text-center">
              <div class="fw-bold fs-4">
                <?php if ($booking->isCheckedIn()): ?>
                  <i class="bi bi-check-circle-fill text-success"></i>
                <?php else: ?>
                  <i class="bi bi-clock text-warning"></i>
                <?php endif; ?>
              </div>
              <div class="text-muted small">Check-in</div>
            </div>
          </div>

          <!-- QR Code -->
          <?php if ($booking->getStatus()->value === 'CONFIRMED' && $booking->getQrCode() && $user && $user->getId() === $booking->getPassengerId()): ?>
          <div class="text-center bg-white border rounded-3 p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-qr-code me-2"></i>QR Code d'embarquement</h6>
            <?php $qrData = urlencode($booking->getQrCode()); ?>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= $qrData ?>"
                 alt="QR Code" class="rounded mb-2" style="image-rendering:pixelated">
            <p class="text-muted small mb-1">Présentez ce QR code au conducteur lors de l'embarquement</p>
            <p class="text-muted" style="font-size:.72rem">Code : <code><?= e($booking->getQrCode()) ?></code></p>
          </div>
          <?php endif; ?>

          <!-- Actions -->
          <div class="d-flex gap-2 flex-wrap">
            <?php if ($user && $trip && $trip->getDriverId() === $user->getId() && $booking->getStatus()->value === 'PENDING'): ?>
            <form method="POST" action="<?= url('bookings/') . $booking->getId() . '/confirm' ?>">
              <?= csrf_field() ?>
              <button class="btn btn-success fw-bold"><i class="bi bi-check-circle me-2"></i>Confirmer la réservation</button>
            </form>
            <?php endif; ?>

            <?php if ($booking->isCancellable()): ?>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
              <i class="bi bi-x-circle me-2"></i>Annuler
            </button>
            <?php endif; ?>

            <?php if ($booking->getStatus()->value === 'COMPLETED' && $user && $user->getId() === $booking->getPassengerId()): ?>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reviewModal">
              <i class="bi bi-star-fill me-2"></i>Évaluer le conducteur
            </button>
            <?php endif; ?>

            <a href="<?= url('trips/') . $booking->getTripId() ?>" class="btn btn-outline-secondary">Voir le trajet</a>
          </div>
        </div>
      </div>

      <!-- Historique statuts -->
      <?php if (!empty($booking->getHistory())): ?>
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Historique</h5>
          <?php foreach ($booking->getHistory() as $h): ?>
          <div class="d-flex gap-3 mb-3">
            <div class="kd-timeline-dot"></div>
            <div>
              <div class="fw-semibold small">
                <?php if ($h->getFromStatus()): ?>
                  <span class="badge <?= $h->getFromStatus()->badgeClass() ?>"><?= $h->getFromStatus()->label() ?></span>
                  → <span class="badge <?= $h->getToStatus()->badgeClass() ?>"><?= $h->getToStatus()->label() ?></span>
                <?php else: ?>
                  <span class="badge <?= $h->getToStatus()->badgeClass() ?>"><?= $h->getToStatus()->label() ?></span>
                <?php endif; ?>
              </div>
              <?php if ($h->getReason()): ?><div class="text-muted small"><?= e($h->getReason()) ?></div><?php endif; ?>
              <div class="text-muted small"><?= $h->getChangedAt()?->format('d/m/Y H:i') ?> <?php if ($h->getChangedByName()): ?> — <?= e($h->getChangedByName()) ?><?php endif; ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title">Annuler la réservation</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Êtes-vous sûr de vouloir annuler cette réservation ? Les places seront libérées.</p>
        <form method="POST" action="<?= url('bookings/') . $booking->getId() . '/cancel' ?>">
          <?= csrf_field() ?>
          <textarea name="reason" class="form-control mb-3" placeholder="Motif d'annulation (optionnel)" rows="2"></textarea>
          <button class="btn btn-danger w-100">Confirmer l'annulation</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Évaluation -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0"><h5 class="modal-title">Évaluer le trajet</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST" action="<?= url('bookings/') . $booking->getId() . '/review' ?>">
          <?= csrf_field() ?>
          <div class="mb-3 text-center">
            <label class="form-label fw-semibold">Note globale</label>
            <div class="kd-star-rating" id="starRating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star fs-2 text-muted kd-star" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)"></i>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-4 form-check text-center">
              <input type="checkbox" name="is_punctual" class="form-check-input" id="punc" checked>
              <label for="punc" class="form-check-label small d-block"><i class="bi bi-clock text-primary"></i> Ponctuel</label>
            </div>
            <div class="col-4 form-check text-center">
              <input type="checkbox" name="is_clean" class="form-check-input" id="clean" checked>
              <label for="clean" class="form-check-label small d-block"><i class="bi bi-stars text-warning"></i> Propre</label>
            </div>
            <div class="col-4 form-check text-center">
              <input type="checkbox" name="is_friendly" class="form-check-input" id="friendly" checked>
              <label for="friendly" class="form-check-label small d-block"><i class="bi bi-emoji-smile text-success"></i> Sympa</label>
            </div>
          </div>
          <textarea name="comment" class="form-control mb-3" rows="3" placeholder="Commentaire (optionnel)..."></textarea>
          <button class="btn btn-warning w-100 fw-bold">Envoyer l'évaluation</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
function setRating(val) {
  document.getElementById('ratingInput').value = val;
  document.querySelectorAll('.kd-star').forEach((s, i) => {
    s.className = 'bi fs-2 kd-star ' + (i < val ? 'bi-star-fill text-warning' : 'bi-star text-muted');
  });
}
setRating(5);
</script>
