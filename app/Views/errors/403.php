<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
      <div style="font-size:6rem;line-height:1">🔐</div>
      <h1 class="display-1 fw-black" style="color:#E2E8F0;letter-spacing:-4px">403</h1>
      <h3 class="fw-bold mb-2">Accès refusé</h3>
      <p class="text-muted mb-4"><?= e($message ?? 'Vous n\'avez pas les permissions pour accéder à cette page.') ?></p>
      <a href="<?= url('dashboard') ?>" class="btn btn-primary rounded-pill px-5 fw-semibold">
        <i class="bi bi-speedometer2 me-2"></i>Mon tableau de bord
      </a>
    </div>
  </div>
</div>
