<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <h3 class="fw-bold mb-2"><i class="bi bi-car-front-fill text-success me-2"></i>Devenir conducteur</h3>
          <p class="text-muted mb-4">Remplissez ce formulaire pour demander l'activation de votre compte conducteur. Un administrateur validera votre dossier sous 24h.</p>

          <?php if (!empty($errors)): ?>
          <div class="alert alert-danger"><ul class="mb-0 small">
            <?php foreach ($errors as $e_msg): ?><li><?= e($e_msg) ?></li><?php endforeach; ?>
          </ul></div>
          <?php endif; ?>

          <form method="POST" action="<?= url('profile/driver/request') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <h6 class="fw-bold mb-3 text-muted text-uppercase small">Permis de conduire</h6>
            <div class="row g-3 mb-4">
              <div class="col-12">
                <label class="form-label fw-semibold">Numéro de permis *</label>
                <input type="text" name="license_number" class="form-control" value="<?= e($old['license_number'] ?? '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Photo du permis (optionnel)</label>
                <input type="file" name="license_doc" class="form-control" accept="image/*,application/pdf">
              </div>
            </div>
            <h6 class="fw-bold mb-3 text-muted text-uppercase small">Véhicule</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Marque *</label>
                <input type="text" name="vehicle_brand" class="form-control" value="<?= e($old['vehicle_brand'] ?? '') ?>" placeholder="Toyota, Peugeot..." required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Modèle *</label>
                <input type="text" name="vehicle_model" class="form-control" value="<?= e($old['vehicle_model'] ?? '') ?>" placeholder="Yaris, 207..." required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Couleur *</label>
                <input type="text" name="vehicle_color" class="form-control" value="<?= e($old['vehicle_color'] ?? '') ?>" placeholder="Blanc, Bleu..." required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Immatriculation *</label>
                <input type="text" name="plate_number" class="form-control" value="<?= e($old['plate_number'] ?? '') ?>" placeholder="DK-1234-AB" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nombre de places habituellement proposées</label>
                <select name="default_seats" class="form-select">
                  <?php for ($i = 1; $i <= 8; $i++): ?>
                  <option value="<?= $i ?>" <?= ($old['default_seats'] ?? 3) == $i ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <div class="alert alert-info mt-4 small">
              <i class="bi bi-info-circle me-2"></i>En soumettant ce formulaire, vous certifiez que les informations fournies sont exactes et que vous possédez un permis de conduire valide.
            </div>
            <div class="d-flex gap-3 mt-3">
              <button type="submit" class="btn btn-success fw-bold px-5">
                <i class="bi bi-send me-2"></i>Soumettre ma demande
              </button>
              <a href="<?= url('dashboard') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
