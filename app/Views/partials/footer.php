<footer class="kd-footer mt-5 pt-5 pb-4">
  <div class="container">
    <div class="row g-4 pb-4" style="border-bottom:1px solid rgba(255,255,255,.1)">
      <div class="col-lg-4">
        <div class="kd-footer-brand mb-2">
          <i class="bi bi-car-front-fill"></i> Kaay Dem !
        </div>
        <p class="small mb-3" style="color:#94A3B8">
          La plateforme de covoiturage des étudiants sénégalais. Voyagez malin, économisez et réduisez votre empreinte carbone.
        </p>
        <div class="d-flex gap-2">
          <span class="badge bg-success">🇸🇳 Made in Senegal</span>
          <span class="badge" style="background:#1565C0">PHP 8.2</span>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="fw-bold text-white mb-3 small text-uppercase">Navigation</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= url() ?>"><i class="bi bi-house me-2"></i>Accueil</a></li>
          <li class="mb-2"><a href="<?= url('search') ?>"><i class="bi bi-search me-2"></i>Rechercher</a></li>
          <li class="mb-2"><a href="<?= url('register') ?>"><i class="bi bi-person-plus me-2"></i>S'inscrire</a></li>
          <li class="mb-2"><a href="<?= url('login') ?>"><i class="bi bi-box-arrow-in-right me-2"></i>Connexion</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="fw-bold text-white mb-3 small text-uppercase">Mon espace</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li class="mb-2"><a href="<?= url('bookings') ?>"><i class="bi bi-ticket-perforated me-2"></i>Réservations</a></li>
          <li class="mb-2"><a href="<?= url('messages') ?>"><i class="bi bi-chat-dots me-2"></i>Messages</a></li>
          <li class="mb-2"><a href="<?= url('profile/driver/request') ?>"><i class="bi bi-car-front me-2"></i>Devenir conducteur</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-bold text-white mb-3 small text-uppercase">Villes desservies</h6>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php foreach (['Dakar','Thiès','Saint-Louis','Kaolack','Mbour','Ziguinchor','Touba','Diourbel'] as $city): ?>
          <a href="<?= url('search?from=Dakar&to=' . urlencode($city)) ?>"
             class="badge text-decoration-none" style="background:rgba(255,255,255,.1);color:#94A3B8;font-weight:500">
            <?= $city ?>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="small" style="color:#64748B">
          <i class="bi bi-code-slash me-1"></i>API REST disponible :
          <a href="<?= url('api/trips') ?>" style="color:#60A5FA" target="_blank">GET /api/trips</a>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-4">
      <p class="small mb-0" style="color:#64748B">
        © <?= date('Y') ?> Kaay Dem ! — Projet universitaire PHP Orienté Objet
      </p>
      <p class="small mb-0" style="color:#64748B">
        Fait avec <i class="bi bi-heart-fill text-danger"></i> par l'équipe Kaay Dem
      </p>
    </div>
  </div>
</footer>
