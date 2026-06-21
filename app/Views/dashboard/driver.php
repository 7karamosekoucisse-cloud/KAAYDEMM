<?php
// Variables: $currentUser, $trips, $upcoming, $totalPassengers, $totalEarnings, $details, $reviews
?>
<div class="kd-dp-page">
<div class="container-fluid px-0">

  <!-- HERO CONDUCTEUR -->
  <div class="kd-dp-hero dp-emerald">
    <div class="container" style="position:relative;z-index:1">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div class="kd-dp-badge dp-emerald"><i class="bi bi-car-front-fill"></i> Conducteur verifie</div>
          <h1 class="kd-dp-title"><?= e($currentUser->getFullName()) ?></h1>
          <div class="kd-dp-sub">
            <span style="color:#E8930A"><?= str_repeat('&#9733;',min(5,(int)round((float)($details['avg_rating']??0)))) ?></span>
            <span><?= number_format((float)($details['avg_rating']??0),1) ?>/5 &middot; <?= $details['total']??0 ?> avis</span>
          </div>
        </div>
        <a href="<?= url('trips/create') ?>" class="btn btn-dp-gold btn-pill fw-bold px-4" style="position:relative;z-index:1">
          <i class="bi bi-plus-circle-fill me-2"></i>Nouveau trajet
        </a>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="container py-4">
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-emerald kd-a1">
          <div class="kpi-icon"><i class="bi bi-map-fill"></i></div>
          <div class="kpi-val"><?= count($trips) ?></div>
          <div class="kpi-lbl">Trajets publies</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-copper kd-a2">
          <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
          <div class="kpi-val"><?= $totalPassengers ?></div>
          <div class="kpi-lbl">Passagers transportes</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-gold kd-a3">
          <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
          <div class="kpi-val"><?= number_format($totalEarnings/1000,0) ?>k</div>
          <div class="kpi-lbl">FCFA gagnes</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-sapphire kd-a4">
          <div class="kpi-icon"><i class="bi bi-star-fill"></i></div>
          <div class="kpi-val"><?= !empty($details['avg_rating']) ? number_format((float)$details['avg_rating'],1) : '&mdash;' ?></div>
          <div class="kpi-lbl">Note moyenne</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Main -->
      <div class="col-lg-8 d-flex flex-column gap-4">

        <!-- Prochains trajets -->
        <div class="kd-dp-card kd-a2">
          <div class="dp-ch">
            <h5><span style="display:inline-block;width:8px;height:8px;background:#15C58A;border-radius:50%;margin-right:.6rem;box-shadow:0 0 8px rgba(21,197,138,.6)"></span>Prochains trajets</h5>
            <a href="<?= url('trips') ?>" class="btn btn-dp-ghost-em btn-pill btn-sm">Tous <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
          <div class="dp-cb">
            <?php if (empty($upcoming)): ?>
            <div class="text-center py-5">
              <div style="font-size:3.5rem;margin-bottom:1rem">&#128197;</div>
              <h6 style="font-family:var(--font-d);font-weight:800;color:var(--dp-text);margin-bottom:.4rem">Aucun trajet a venir</h6>
              <a href="<?= url('trips/create') ?>" class="btn btn-dp-gold btn-pill px-4 mt-2 fw-bold"><i class="bi bi-plus-circle me-1"></i>Publier maintenant</a>
            </div>
            <?php else: ?>
            <?php foreach ($upcoming as $t): ?>
            <a href="<?= url('trips/' . $t->getId()) ?>" class="kd-dp-row">
              <div style="background:rgba(21,197,138,.15);border:1px solid rgba(21,197,138,.2);border-radius:12px;padding:.55rem .75rem;text-align:center;min-width:52px;flex-shrink:0">
                <div style="font-size:1.1rem;font-weight:900;font-family:var(--font-d);color:#6EE7B7;line-height:1"><?= $t->getDepartureAt()?->format('d') ?></div>
                <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:rgba(110,231,183,.6)"><?= $t->getDepartureAt()?->format('M') ?></div>
              </div>
              <div class="flex-grow-1" style="min-width:0">
                <div class="kd-dp-route" style="--dp-gold:var(--dp-emerald)">
                  <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?= e($t->getDepartureCity()) ?></span>
                  <div class="kd-dp-route-line" style="background:repeating-linear-gradient(90deg,rgba(21,197,138,.5) 0,rgba(21,197,138,.5) 4px,transparent 4px,transparent 8px)"></div>
                  <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?= e($t->getArrivalCity()) ?></span>
                </div>
                <div style="font-size:.75rem;color:var(--dp-muted)"><?= $t->getDepartureAt()?->format('H:i') ?> &middot; <?= formatPrice($t->getPricePerSeat()) ?>/place</div>
              </div>
              <div class="flex-shrink-0">
                <span class="kd-dp-chip emerald"><?= $t->getSeatsAvailable() ?>/<?= $t->getSeatsOffered() ?> places</span>
              </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Check-in QR -->
        <div class="kd-dp-card kd-a3" style="border-color:rgba(21,197,138,.15);background:rgba(21,197,138,.04)">
          <div class="dp-ch" style="border-color:rgba(21,197,138,.1)">
            <div class="d-flex align-items-center gap-3">
              <div style="width:44px;height:44px;border-radius:12px;background:rgba(21,197,138,.12);border:1px solid rgba(21,197,138,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-qr-code-scan" style="color:#6EE7B7;font-size:1.25rem"></i>
              </div>
              <div>
                <h5 style="margin:0">Valider l'embarquement</h5>
                <div style="font-size:.75rem;color:var(--dp-muted)">Saisissez ou scannez le QR du passager</div>
              </div>
            </div>
          </div>
          <div class="dp-cb">
            <form method="POST" action="<?= url('checkin') ?>" class="d-flex gap-2">
              <?= csrf_field() ?>
              <input type="text" name="qr_code" class="form-control" placeholder="Code QR du passager..." required
                     style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.12);color:#fff">
              <button class="btn btn-dp-ghost-em btn-pill px-4 fw-bold flex-shrink-0"><i class="bi bi-check-lg me-1"></i>Valider</button>
            </form>
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 d-flex flex-column gap-3">

        <!-- Actions -->
        <div class="kd-dp-card kd-a3">
          <div class="dp-ch"><h5>&#9889; Actions rapides</h5></div>
          <div class="dp-cb d-grid gap-2">
            <a href="<?= url('trips/create') ?>" class="btn btn-dp-gold btn-pill fw-bold"><i class="bi bi-plus-circle me-2"></i>Publier un trajet</a>
            <a href="<?= url('trips') ?>" class="btn btn-dp-ghost-em btn-pill"><i class="bi bi-list-ul me-2"></i>Mes trajets</a>
            <a href="<?= url('messages') ?>" class="btn btn-dp-ghost btn-pill"><i class="bi bi-chat-dots me-2"></i>Messages passagers</a>
            <a href="<?= url('profile/' . $currentUser->getId()) ?>" class="btn btn-pill" style="background:rgba(255,255,255,.05);color:var(--dp-muted);border:1px solid rgba(255,255,255,.08)!important"><i class="bi bi-person me-2"></i>Mon profil public</a>
          </div>
        </div>

        <!-- Avis -->
        <?php if (!empty($details) && ($details['total']??0) > 0): ?>
        <div class="kd-dp-card kd-a4">
          <div class="dp-ch"><h5>&#11088; Avis passagers</h5></div>
          <div class="dp-cb">
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:rgba(21,197,138,.07);border:1px solid rgba(21,197,138,.12)">
              <div style="font-size:3rem;font-weight:900;font-family:var(--font-d);color:#6EE7B7;line-height:1"><?= number_format((float)($details['avg_rating']??0),1) ?></div>
              <div>
                <div style="color:#E8930A;font-size:1.1rem"><?= str_repeat('&#9733;',min(5,(int)round((float)($details['avg_rating']??0)))) ?></div>
                <div style="font-size:.74rem;color:var(--dp-muted);margin-top:.2rem"><?= $details['total']??0 ?> avis verifies</div>
              </div>
            </div>
            <?php foreach (array_slice($reviews??[],0,2) as $r): ?>
            <div class="kd-review-card mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-weight:700;font-size:.85rem;color:var(--dp-text)"><?= e($r->getAuthorName()??'Passager') ?></span>
                <span style="color:#E8930A;font-size:.9rem;white-space:nowrap"><?= str_repeat('&#9733;',$r->getRating()) ?></span>
              </div>
              <?php if ($r->getComment()): ?>
              <p style="font-size:.8rem;font-style:italic;margin:.25rem 0 0;line-height:1.5;overflow-wrap:break-word">"<?= e(truncate($r->getComment(),80)) ?>"</p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <a href="<?= url('profile/' . $currentUser->getId()) ?>" class="btn btn-dp-ghost-em btn-pill btn-sm w-100">Voir tous les avis</a>
          </div>
        </div>
        <?php endif; ?>

        <!-- Revenus -->
        <div class="kd-dp-card" style="border-color:rgba(212,168,83,.15);background:rgba(212,168,83,.04)">
          <div class="dp-ch" style="border-color:rgba(212,168,83,.1)">
            <h5 style="color:var(--dp-gold)">&#128176; Revenus cumules</h5>
          </div>
          <div class="dp-cb">
            <div style="font-size:2.6rem;font-weight:900;font-family:var(--font-d);line-height:1;color:var(--dp-gold)"><?= number_format($totalEarnings/1000,0) ?>k</div>
            <div style="font-size:.75rem;color:var(--dp-muted);margin-top:.3rem">FCFA &middot; <?= count($trips) ?> trajet<?= count($trips)>1?'s':'' ?> publies</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
