<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
      <div style="font-size:6rem;line-height:1">🔍</div>
      <h1 class="display-1 fw-black" style="color:#E2E8F0;letter-spacing:-4px">404</h1>
      <h3 class="fw-bold mb-2">Page introuvable</h3>
      <p class="text-muted mb-4"><?= e($message ?? 'Cette page n\'existe pas ou a été déplacée.') ?></p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="<?= url() ?>" class="btn btn-primary rounded-pill px-4 fw-semibold">
          <i class="bi bi-house me-2"></i>Retour à l'accueil
        </a>
        <a href="<?= url('search') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
          <i class="bi bi-search me-2"></i>Rechercher un trajet
        </a>
      </div>
    </div>
  </div>
</div>
