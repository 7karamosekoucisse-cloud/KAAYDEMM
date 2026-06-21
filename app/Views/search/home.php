<!-- ══════════ HERO ══════════ -->
<section class="kd-hero">
  <div class="kd-orb kd-orb-1"></div>
  <div class="kd-orb kd-orb-2"></div>
  <div class="kd-orb kd-orb-3"></div>
  <div class="kd-grid-pattern"></div>
  <div class="kd-hero-readability"></div>

  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center g-5">

      <!-- LEFT -->
      <div class="col-lg-6">
        <div style="position:relative;z-index:3">
        <div class="kd-hero-pill kd-a1">
          <span class="kd-hero-pill-dot"></span>
          🇸🇳 Plateforme étudiante · Sénégal
        </div>

        <h1 class="kd-hero-h1 kd-a2">
          Voyagez<br>
          <span class="kd-text-gradient">ensemble</span>,<br>
          vivez <span class="kd-text-gradient-green">mieux.</span>
        </h1>

        <p class="kd-hero-sub kd-a3">
          Kaay Dem connecte les étudiants sénégalais pour partager les frais de route entre campus. Plus économique, plus écologique, plus convivial.
        </p>

        <div class="d-flex gap-3 flex-wrap kd-a4">
          <a href="<?= url('search') ?>" class="btn btn-gold btn-lg btn-pill px-5">
            <i class="bi bi-search me-2"></i>Trouver un trajet
          </a>
          <a href="<?= url('register') ?>" class="btn btn-lg btn-pill px-4"
             style="background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.18) !important;color:#fff">
            Rejoindre gratuitement <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>

        <!-- Stats animées -->
        <div class="kd-hero-stats kd-a5">
          <div>
            <div class="kd-stat-num" data-count="1200">0</div>
            <div class="kd-stat-lbl">Étudiants inscrits</div>
          </div>
          <div>
            <div class="kd-stat-num" data-count="3400">0</div>
            <div class="kd-stat-lbl">Trajets publiés</div>
          </div>
          <div>
            <div class="kd-stat-num"><em>8,2</em>T</div>
            <div class="kd-stat-lbl">CO₂ économisé</div>
          </div>
          <div>
            <div class="kd-stat-num"><em>4,8</em>/5</div>
            <div class="kd-stat-lbl">Satisfaction</div>
          </div>
        </div>
        </div><!-- /z-index wrapper -->
      </div>

      <!-- RIGHT : Glassmorphism card -->
      <div class="col-lg-6 kd-a3">
        <div class="kd-glass p-4">
          <!-- Onglets -->
          <div class="d-flex gap-1 mb-4 p-1 rounded-pill" style="background:rgba(255,255,255,.07)">
            <button class="btn btn-pill flex-fill py-2 active" id="tabPassenger"
                    onclick="switchTab('passenger')"
                    style="background:rgba(255,255,255,.12);color:#fff;font-size:.84rem">
              <i class="bi bi-person me-1"></i>Passager
            </button>
            <button class="btn btn-pill flex-fill py-2" id="tabDriver"
                    onclick="switchTab('driver')"
                    style="background:transparent;color:rgba(255,255,255,.5);font-size:.84rem">
              <i class="bi bi-car-front me-1"></i>Conducteur
            </button>
          </div>

          <!-- Form passager -->
          <div id="formPassenger">
            <form action="<?= url('search') ?>" method="GET">
              <div class="mb-3">
                <label class="kd-glass-label"><i class="bi bi-circle-fill me-1" style="color:#15C58A;font-size:.4rem;vertical-align:middle"></i>Ville de départ</label>
                <div class="input-group">
                  <span class="input-group-text kd-glass-input-icon"><i class="bi bi-geo-alt"></i></span>
                  <input type="text" name="from" class="form-control kd-glass-input border-start-0" placeholder="Ex : Dakar" list="cities-list">
                </div>
              </div>
              <div class="mb-3">
                <label class="kd-glass-label"><i class="bi bi-circle-fill me-1" style="color:#E8442F;font-size:.4rem;vertical-align:middle"></i>Destination</label>
                <div class="input-group">
                  <span class="input-group-text kd-glass-input-icon"><i class="bi bi-geo-alt-fill"></i></span>
                  <input type="text" name="to" class="form-control kd-glass-input border-start-0" placeholder="Ex : Thiès" list="cities-list">
                </div>
              </div>
              <div class="row g-2 mb-4">
                <div class="col-7">
                  <label class="kd-glass-label"><i class="bi bi-calendar me-1"></i>Date</label>
                  <input type="date" name="date" class="form-control kd-glass-input" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-5">
                  <label class="kd-glass-label"><i class="bi bi-people me-1"></i>Places</label>
                  <select name="seats" class="form-select kd-glass-select">
                    <?php for($i=1;$i<=8;$i++): ?>
                    <option value="<?=$i?>"><?=$i?> place<?=$i>1?'s':''?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn btn-gold w-100 btn-lg btn-pill fw-bold">
                <i class="bi bi-search me-2"></i>Rechercher un trajet
              </button>
            </form>
          </div>

          <!-- Form conducteur -->
          <div id="formDriver" style="display:none">
            <div class="text-center py-3">
              <i class="bi bi-car-front-fill" style="font-size:2.5rem;color:var(--gold-lt);display:block;margin-bottom:1rem"></i>
              <h6 style="color:#fff;font-family:var(--font-d);font-weight:800;margin-bottom:.5rem">Devenez conducteur</h6>
              <p style="color:rgba(255,255,255,.55);font-size:.88rem;margin-bottom:1.5rem">Partagez vos frais de route et aidez vos camarades</p>
              <?php if (($currentUser ?? null) && $currentUser->hasApprovedRole(\App\Enums\Role::DRIVER)): ?>
                <a href="<?= url('trips/create') ?>" class="btn btn-gold w-100 btn-lg btn-pill fw-bold">
                  <i class="bi bi-plus-circle me-2"></i>Publier un trajet
                </a>
              <?php elseif ($currentUser ?? null): ?>
                <a href="<?= url('profile/driver/request') ?>" class="btn btn-gold w-100 btn-lg btn-pill fw-bold">
                  <i class="bi bi-car-front me-2"></i>Demander le statut conducteur
                </a>
              <?php else: ?>
                <a href="<?= url('register') ?>" class="btn btn-gold w-100 btn-lg btn-pill fw-bold">
                  <i class="bi bi-person-plus me-2"></i>Créer un compte conducteur
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Trust indicators -->
          <div class="d-flex justify-content-center gap-3 mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.08)">
            <div class="d-flex align-items-center gap-1" style="color:rgba(255,255,255,.45);font-size:.75rem">
              <i class="bi bi-shield-fill-check" style="color:var(--savane-lt)"></i> Vérification admin
            </div>
            <div class="d-flex align-items-center gap-1" style="color:rgba(255,255,255,.45);font-size:.75rem">
              <i class="bi bi-star-fill" style="color:var(--gold-lt)"></i> Avis vérifiés
            </div>
            <div class="d-flex align-items-center gap-1" style="color:rgba(255,255,255,.45);font-size:.75rem">
              <i class="bi bi-qr-code" style="color:rgba(255,255,255,.6)"></i> QR Code
            </div>
          </div>
        </div>

        <!-- Floating badge -->
        <div class="d-flex justify-content-end mt-3">
          <div class="kd-tag kd-tag-confirmed px-3 py-2" style="font-size:.78rem;box-shadow:0 4px 16px rgba(13,159,110,.2)">
            <i class="bi bi-lightning-fill"></i> Réservation en 30 secondes
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<datalist id="cities-list">
  <option value="Dakar"><option value="Thiès"><option value="Saint-Louis">
  <option value="Ziguinchor"><option value="Touba"><option value="Kaolack">
  <option value="Mbour"><option value="Diourbel"><option value="Tambacounda">
  <option value="Louga"><option value="Fatick"><option value="Kolda">
</datalist>

<!-- ══════════ TRAJETS POPULAIRES ══════════ -->
<?php if (!empty($popular)): ?>
<section class="kd-section-sm">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--ocean-m);margin-bottom:.3rem">Tendances</div>
        <h2 style="font-size:1.7rem;margin:0">Destinations populaires</h2>
      </div>
      <a href="<?= url('search') ?>" class="btn btn-ghost btn-sm btn-pill">Tout voir <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
      <?php foreach ($popular as $route): ?>
      <div class="col-6 col-md-3">
        <a href="<?= url('search?from=' . urlencode($route['departure_city']) . '&to=' . urlencode($route['arrival_city'])) ?>"
           class="kd-popular-card">
          <div class="d-flex align-items-start gap-2 mb-3">
            <div style="display:flex;flex-direction:column;align-items:center;padding-top:3px">
              <div style="width:8px;height:8px;border-radius:50%;background:var(--savane);flex-shrink:0"></div>
              <div style="width:1.5px;height:20px;background:var(--border);margin:3px 0"></div>
              <div style="width:8px;height:8px;border-radius:50%;background:var(--coral);flex-shrink:0"></div>
            </div>
            <div>
              <div class="kd-popular-city"><?= e($route['departure_city']) ?></div>
              <div class="kd-popular-city" style="color:var(--ocean-m);margin-top:8px"><?= e($route['arrival_city']) ?></div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="kd-popular-sub"><?= $route['trip_count'] ?> trajet<?= $route['trip_count']>1?'s':'' ?></div>
            <?php if (!empty($route['avg_price'])): ?>
            <div style="font-size:.78rem;font-weight:700;color:var(--ocean-m)">
              ~<?= formatPrice((float)$route['avg_price']) ?>
            </div>
            <?php endif; ?>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════ COMMENT ÇA MARCHE ══════════ -->
<section class="kd-section" style="background:linear-gradient(160deg,var(--ink) 0%,#0C1A40 100%)">
  <div class="container">
    <div class="text-center mb-5">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--gold-lt);margin-bottom:.4rem">Simple & Rapide</div>
      <h2 style="color:#fff;font-size:2rem">Comment ça marche ?</h2>
    </div>
    <div class="row g-4">
      <?php
      $steps = [
        ['01','bi-person-plus-fill','Créez votre compte','Inscription gratuite en 2 min avec votre email universitaire. Vérification des documents conducteur sous 24h.'],
        ['02','bi-search','Trouvez ou publiez','Passager : cherchez parmi des centaines de trajets. Conducteur : publiez en 30 secondes.'],
        ['03','bi-ticket-perforated-fill','Réservez & confirmez','Un clic pour réserver. Recevez un QR code unique. Le conducteur confirme la réservation.'],
        ['04','bi-star-fill','Voyagez & évaluez','Embarquez via QR code. Après le trajet, évaluez-vous mutuellement pour bâtir la confiance.'],
      ];
      foreach ($steps as [$num, $icon, $title, $desc]):
      ?>
      <div class="col-6 col-lg-3">
        <div class="kd-how-card">
          <div class="kd-how-num"><?= $num ?></div>
          <i class="bi <?= $icon ?>" style="font-size:1.8rem;color:var(--gold);display:block;margin-bottom:1rem"></i>
          <h5 style="color:#fff;font-family:var(--font-d);font-weight:800;margin-bottom:.5rem;font-size:1rem"><?= $title ?></h5>
          <p style="color:rgba(255,255,255,.45);font-size:.84rem;margin:0;line-height:1.7"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="row mt-5 pt-2">
      <div class="col-md-8 mx-auto text-center">
        <a href="<?= url('register') ?>" class="btn btn-gold btn-lg btn-pill px-5 fw-bold">
          <i class="bi bi-person-plus me-2"></i>Rejoindre Kaay Dem ! — Gratuit
        </a>
        <p style="color:rgba(255,255,255,.3);font-size:.8rem;margin-top:1rem;margin-bottom:0">
          Déjà +1 200 étudiants · 0 commission · 100% sécurisé
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ IMPACT CO2 ══════════ -->
<section class="kd-section-sm" style="background:var(--white)">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--savane);margin-bottom:.4rem">Planète</div>
        <h2 style="font-size:1.8rem;margin-bottom:1rem">Chaque trajet partagé réduit l'empreinte carbone 🌿</h2>
        <p style="color:var(--muted);line-height:1.8;margin-bottom:1.5rem">
          Dakar → Thiès à 3 passagers = <strong>3× moins de CO₂</strong> qu'un trajet solo. Kaay Dem calcule et affiche votre impact à chaque voyage.
        </p>
        <div class="d-flex gap-2">
          <div class="kd-tag kd-tag-confirmed px-3 py-2">
            <i class="bi bi-leaf-fill me-1"></i>8,2 tonnes CO₂ économisées
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="row g-3">
          <?php
          $eco = [
            ['ocean', 'bi-people-fill',       '1 200+', 'Étudiants actifs'],
            ['savane','bi-leaf-fill',          '8,2 T',  'CO₂ économisé'],
            ['gold',  'bi-cash-coin',          '3,4M',   'FCFA économisés'],
            ['coral', 'bi-star-fill',          '4,8/5',  'Satisfaction'],
          ];
          foreach ($eco as [$c, $icon, $val, $lbl]):
          ?>
          <div class="col-6">
            <div class="kd-kpi <?= $c ?> kd-card-lift">
              <div class="kd-kpi-icon"><i class="bi <?= $icon ?>"></i></div>
              <div class="kd-kpi-val"><?= $val ?></div>
              <div class="kd-kpi-lbl"><?= $lbl ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Tab switch hero
function switchTab(tab) {
  const isDriver = tab === 'driver';
  document.getElementById('formPassenger').style.display = isDriver ? 'none' : '';
  document.getElementById('formDriver').style.display   = isDriver ? '' : 'none';
  document.getElementById('tabPassenger').style.background = isDriver ? 'transparent' : 'rgba(255,255,255,.12)';
  document.getElementById('tabDriver').style.background    = isDriver ? 'rgba(255,255,255,.12)' : 'transparent';
  document.getElementById('tabPassenger').style.color = isDriver ? 'rgba(255,255,255,.5)' : '#fff';
  document.getElementById('tabDriver').style.color    = isDriver ? '#fff' : 'rgba(255,255,255,.5)';
}

// Compteurs animés
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  const duration = 1800;
  const step = 16;
  const inc = target / (duration / step);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + inc, target);
    el.textContent = Math.floor(current).toLocaleString('fr');
    if (current >= target) clearInterval(timer);
  }, step);
});
</script>
