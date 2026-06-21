<div class="card border-0 shadow rounded-4">
  <div class="card-body p-4">
    <h4 class="fw-bold text-center mb-2">Mot de passe oublié</h4>
    <p class="text-muted text-center small mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>
    <?php if ($success ?? null): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= url('password/forgot') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-bold">
        <i class="bi bi-send me-2"></i>Envoyer le lien
      </button>
    </form>
  </div>
</div>
<p class="text-center mt-3"><a href="<?= url('login') ?>" class="text-decoration-none small">← Retour connexion</a></p>
