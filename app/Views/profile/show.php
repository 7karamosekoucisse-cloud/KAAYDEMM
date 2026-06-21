<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="javascript:history.back()" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Retour</span>
  </a>
</div>

<?php $user = $currentUser ?? null; ?>
<div class="container pb-5">

  <!-- Bannière profil -->
  <div class="kd-profile-banner mb-0 kd-a1" style="margin-bottom:0!important">
    <div style="position:relative;z-index:1">
      <!-- Rôles -->
      <div class="d-flex gap-2 mb-3">
        <?php foreach ($profile->getUserRoles() as $ur): ?>
          <?php if ($ur->getStatus()->value === 'APPROVED'): ?>
          <span class="badge px-3 py-1 rounded-pill" style="background:rgba(255,255,255,.15);color:#fff;font-size:.75rem;font-weight:700;border:1px solid rgba(255,255,255,.2)">
            <?= $ur->getRole()->label() ?>
          </span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <h2 style="color:#fff;font-size:1.85rem;font-weight:900;margin-bottom:.25rem"><?= e($profile->getFullName()) ?></h2>
      <div style="color:rgba(255,255,255,.6);font-size:.88rem"><i class="bi bi-mortarboard me-1"></i><?= e($profile->getCampus()) ?></div>
    </div>
  </div>

  <!-- Corps profil -->
  <div class="row g-4 kd-profile-body">

    <!-- Colonne gauche -->
    <div class="col-lg-4">
      <div class="card text-center p-4 kd-a1" style="margin-top:0">
        <!-- Avatar -->
        <div class="position-relative d-inline-block mx-auto mb-3">
          <?php if ($profile->getAvatar()): ?>
            <img src="<?= url('uploads/' . e($profile->getAvatar())) ?>"
                 class="kd-av" style="width:110px;height:110px;border:4px solid var(--white);box-shadow:var(--sh-lg)"
                 alt="<?= e($profile->getFullName()) ?>">
          <?php else: ?>
            <div class="kd-av-placeholder" style="width:110px;height:110px;font-size:2rem;font-weight:900;border:4px solid var(--white);box-shadow:var(--sh-lg)">
              <?= e($profile->getInitials()) ?>
            </div>
          <?php endif; ?>
          <?php if ($profile->getTrustScore() >= 75): ?>
          <div title="Conducteur vérifié" style="position:absolute;bottom:4px;right:4px;width:26px;height:26px;border-radius:50%;background:#059669;border:2px solid var(--white);display:flex;align-items:center;justify-content:center">
            <i class="bi bi-check-lg text-white" style="font-size:.65rem"></i>
          </div>
          <?php endif; ?>
        </div>

        <h4 style="font-family:var(--font-d);font-weight:900;margin-bottom:.2rem"><?= e($profile->getFullName()) ?></h4>
        <div style="color:var(--muted);font-size:.85rem;margin-bottom:1rem"><?= e($profile->getCampus()) ?></div>

        <!-- Stats -->
        <div class="row g-0 text-center mb-4 pt-3" style="border-top:1px solid var(--border)">
          <div class="col-4">
            <div style="font-family:var(--font-d);font-size:1.4rem;font-weight:900"><?= $globalStats['trips_as_driver'] ?? 0 ?></div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;font-weight:600">Trajets</div>
          </div>
          <div class="col-4" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
            <div style="font-family:var(--font-d);font-size:1.4rem;font-weight:900"><?= $globalStats['trips_as_passenger'] ?? 0 ?></div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;font-weight:600">Passager</div>
          </div>
          <div class="col-4">
            <div style="font-family:var(--font-d);font-size:1.4rem;font-weight:900"><?= $globalStats['review_count'] ?? 0 ?></div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;font-weight:600">Avis</div>
          </div>
        </div>

        <!-- Trust Score widget -->
        <?php
        $score = $profile->getTrustScore();
        require __DIR__ . '/../../Views/partials/trust_score.php';
        ?>

        <!-- Actions -->
        <div class="mt-4 d-grid gap-2">
          <?php if ($user && $user->getId() !== $profile->getId()): ?>
            <a href="<?= url('messages/' . $profile->getId()) ?>" class="btn btn-primary btn-pill fw-bold">
              <i class="bi bi-chat-dots me-2"></i>Envoyer un message
            </a>
            <button class="btn btn-ghost btn-pill btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal">
              <i class="bi bi-flag me-1"></i>Signaler
            </button>
          <?php elseif ($user && $user->getId() === $profile->getId()): ?>
            <a href="<?= url('profile/edit') ?>" class="btn btn-primary btn-pill fw-bold">
              <i class="bi bi-pencil me-2"></i>Modifier mon profil
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Véhicule -->
      <?php if ($profile->getDriverProfile()): ?>
      <?php $dp = $profile->getDriverProfile(); ?>
      <div class="card p-4 mt-3 kd-a2">
        <h6 style="font-family:var(--font-d);font-weight:800;margin-bottom:1rem">
          <i class="bi bi-car-front-fill me-2" style="color:var(--ocean-m)"></i>Véhicule
        </h6>
        <div class="small">
          <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
            <span style="color:var(--muted)">Marque / Modèle</span>
            <strong><?= e($dp->getVehicleBrand()) ?> <?= e($dp->getVehicleModel()) ?></strong>
          </div>
          <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
            <span style="color:var(--muted)">Couleur</span>
            <span><?= e($dp->getVehicleColor()) ?></span>
          </div>
          <div class="d-flex justify-content-between py-2">
            <span style="color:var(--muted)">Immatriculation</span>
            <code style="background:var(--surface);padding:.15rem .5rem;border-radius:5px;font-size:.82rem"><?= e($dp->getPlateNumber()) ?></code>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Stats avis détaillées -->
      <?php if (!empty($stats) && ($stats['total'] ?? 0) > 0): ?>
      <div class="card p-4 mt-3 kd-a3">
        <h6 style="font-family:var(--font-d);font-weight:800;margin-bottom:1rem">
          <i class="bi bi-star-fill me-2" style="color:var(--gold)"></i>Qualité de service
        </h6>
        <div class="text-center mb-3">
          <div style="font-size:3rem;font-weight:900;font-family:var(--font-d);color:var(--gold);line-height:1">
            <?= number_format((float)($stats['avg_rating']??0), 1) ?>
          </div>
          <div style="color:#F59E0B;font-size:1.1rem;margin:.3rem 0">
            <?php for ($i=1;$i<=5;$i++): ?>
              <?php $r=(float)($stats['avg_rating']??0); ?>
              <i class="bi bi-star<?= $i<=$r?'-fill':($i-$r<1&&$i-$r>0?'-half':'') ?>" style="color:#F59E0B"></i>
            <?php endfor; ?>
          </div>
          <div style="font-size:.78rem;color:var(--muted)"><?= $stats['total'] ?> avis vérifiés</div>
        </div>
        <?php foreach ([['Ponctualité','pct_punctual','#1246AA'],['Propreté','pct_clean','#F59E0B'],['Sympathie','pct_friendly','#059669']] as [$lbl,$key,$col]): ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="font-size:.82rem;color:var(--muted)"><?= $lbl ?></span>
          <div class="d-flex align-items-center gap-2">
            <div style="width:90px;height:5px;background:var(--border);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:<?= $stats[$key]??0 ?>%;background:<?= $col ?>;border-radius:99px"></div>
            </div>
            <strong style="font-size:.8rem;min-width:2.5rem;text-align:right"><?= number_format((float)($stats[$key]??0)) ?>%</strong>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Colonne droite : Badges + Avis -->
    <div class="col-lg-8">

      <!-- Badges -->
      <div class="card p-4 mb-4 kd-a2">
        <h5 style="font-family:var(--font-d);font-weight:800;margin-bottom:1.25rem">
          <i class="bi bi-award-fill me-2" style="color:var(--gold)"></i>Badges & Récompenses
        </h5>
        <?php
        $earnedBadges = []; // Sera chargé depuis la BDD
        try {
            global $container;
            if ($container) {
                $db = \Core\Database::getInstance()->pdo();
                $stmt = $db->prepare('SELECT code FROM badges WHERE user_id = ?');
                $stmt->execute([$profile->getId()]);
                $earnedBadges = $stmt->fetchAll();
            }
        } catch (\Throwable) {}
        $badgeUserId = $profile->getId();
        require __DIR__ . '/../../Views/partials/badges.php';
        ?>
      </div>

      <!-- Avis -->
      <div class="card p-4 kd-a3">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 style="font-family:var(--font-d);font-weight:800;margin:0">
            Avis reçus <span style="background:var(--surface);color:var(--ocean-m);font-size:.8rem;font-weight:700;padding:.2rem .6rem;border-radius:99px;margin-left:.5rem"><?= count($reviews) ?></span>
          </h5>
        </div>

        <?php if (empty($reviews)): ?>
        <div class="kd-empty py-4">
          <span class="kd-empty-icon">⭐</span>
          <p style="color:var(--muted)">Aucun avis pour l'instant</p>
        </div>
        <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($reviews as $r): ?>
          <div class="kd-review-card">
            <div class="d-flex align-items-start gap-3">
              <div class="kd-av-placeholder" style="width:40px;height:40px;font-size:.82rem;flex-shrink:0">
                <?= strtoupper(substr($r->getAuthorName() ?? 'A', 0, 1)) ?>
              </div>
              <div class="flex-grow-1 min-width-0" style="min-width:0">
                <div class="d-flex justify-content-between align-items-center mb-1 gap-2">
                  <span class="kd-review-author"><?= e($r->getAuthorName() ?? 'Anonyme') ?></span>
                  <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <span class="kd-review-stars"><?= str_repeat('★', $r->getRating()) ?><?= str_repeat('☆', 5-$r->getRating()) ?></span>
                  </div>
                </div>
                <div class="kd-review-date mb-2"><?= $r->getCreatedAt()?->format('d/m/Y') ?></div>
                <?php if ($r->getComment()): ?>
                <p class="kd-review-text">"<?= e($r->getComment()) ?>"</p>
                <?php endif; ?>
                <?php if ($r->isPunctual() || $r->isClean() || $r->isFriendly()): ?>
                <div class="d-flex gap-2 flex-wrap mt-2">
                  <?php if ($r->isPunctual()): ?><span class="kd-tag kd-tag-confirmed" style="font-size:.68rem"><i class="bi bi-clock me-1"></i>Ponctuel</span><?php endif; ?>
                  <?php if ($r->isClean()): ?><span class="kd-tag kd-tag-completed" style="font-size:.68rem"><i class="bi bi-stars me-1"></i>Propre</span><?php endif; ?>
                  <?php if ($r->isFriendly()): ?><span class="kd-tag kd-tag-open" style="font-size:.68rem"><i class="bi bi-emoji-smile me-1"></i>Sympa</span><?php endif; ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal signalement -->
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content" style="border-radius:var(--r-xl)!important">
    <div class="modal-header border-0 pb-0">
      <h5 class="modal-title" style="font-family:var(--font-d);font-weight:800"><i class="bi bi-flag-fill me-2" style="color:var(--coral)"></i>Signaler</h5>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form method="POST" action="<?= url('reports') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="target_id" value="<?= $profile->getId() ?>">
        <input type="hidden" name="target_type" value="user">
        <select name="reason" class="form-select mb-3" required>
          <option value="">Motif...</option>
          <option>Harcèlement</option><option>Fraude</option>
          <option>No-show répété</option><option>Comportement inapproprié</option><option>Autre</option>
        </select>
        <textarea name="details" class="form-control mb-3" rows="3" placeholder="Détails..."></textarea>
        <button class="btn btn-danger btn-pill w-100 fw-bold"><i class="bi bi-send me-2"></i>Envoyer le signalement</button>
      </form>
    </div>
  </div></div>
</div>
