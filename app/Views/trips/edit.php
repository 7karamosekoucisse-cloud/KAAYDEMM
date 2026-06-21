<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="javascript:history.back()" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Retour au trajet</span>
  </a>
</div>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <h3 class="fw-bold mb-4"><i class="bi bi-pencil-fill text-primary me-2"></i>Modifier le trajet</h3>
          <form method="POST" action="<?= url('trips/') . $trip->getId() . '/edit' ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Ville de départ</label>
                <input type="text" name="departure_city" class="form-control" value="<?= e($trip->getDepartureCity()) ?>" list="cities-list">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Ville d'arrivée</label>
                <input type="text" name="arrival_city" class="form-control" value="<?= e($trip->getArrivalCity()) ?>" list="cities-list">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Date et heure</label>
                <input type="datetime-local" name="departure_at" class="form-control" value="<?= $trip->getDepartureAt()?->format('Y-m-d\TH:i') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Prix par place</label>
                <input type="number" name="price_per_seat" class="form-control" value="<?= $trip->getPricePerSeat() ?>">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($trip->getDescription()) ?></textarea>
              </div>
            </div>
            <div class="d-flex gap-3 mt-4">
              <button type="submit" class="btn btn-primary fw-bold px-5">Enregistrer</button>
              <a href="<?= url('trips/') . $trip->getId() ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<datalist id="cities-list"><option value="Dakar"><option value="Thiès"><option value="Saint-Louis"><option value="Ziguinchor"><option value="Touba"></datalist>
