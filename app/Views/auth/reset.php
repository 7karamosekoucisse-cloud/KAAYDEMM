<div class="card border-0 shadow rounded-4">
  <div class="card-body p-4">
    <h4 class="fw-bold text-center mb-4">Nouveau mot de passe</h4>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger small"><?= e(array_values($errors)[0]) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= url('password/reset/' . e($token ?? '')) ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Confirmer</label>
        <input type="password" name="password_confirm" class="form-control" required>
      </div>
      <button class="btn btn-success w-100 fw-bold">
        <i class="bi bi-check-circle me-2"></i>Enregistrer
      </button>
    </form>
  </div>
</div>
