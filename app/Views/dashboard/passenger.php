<?php
// Variables: $currentUser, $bookings, $totalSpent, $stats, $notifications, $unreadNotif
?>
<div class="kd-dp-page">
<div class="container-fluid px-0">

  <!-- HERO PASSAGER -->
  <div class="kd-dp-hero dp-copper">
    <div class="container" style="position:relative;z-index:1">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div class="kd-dp-badge dp-copper"><i class="bi bi-person-fill"></i> Espace Passager</div>
          <h1 class="kd-dp-title">Bienvenue, <?= e($currentUser->getFirstName()) ?> &#128075;</h1>
          <div class="kd-dp-sub">
            <i class="bi bi-calendar3"></i> <?= date('l d F Y') ?>
          </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center" style="position:relative;z-index:1">
          <div class="kd-dp-chip gold"><i class="bi bi-gift-fill"></i> <?= number_format($currentUser->getLoyaltyPoints()) ?> pts</div>
          <a href="<?= url('search') ?>" class="btn btn-dp-gold btn-pill fw-bold px-4">
            <i class="bi bi-search me-2"></i>Trouver un trajet
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="container py-4">
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-copper kd-a1">
          <div class="kpi-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
          <div class="kpi-val"><?= count($bookings) ?></div>
          <div class="kpi-lbl">Reservations</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-gold kd-a2">
          <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
          <div class="kpi-val"><?= number_format($totalSpent/1000,0) ?>k</div>
          <div class="kpi-lbl">FCFA depenses</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-sapphire kd-a3">
          <div class="kpi-icon"><i class="bi bi-star-fill"></i></div>
          <div class="kpi-val"><?= !empty($stats['avg_rating']) ? number_format((float)$stats['avg_rating'],1) : '&mdash;' ?></div>
          <div class="kpi-lbl">Ma note</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kd-dp-kpi dp-emerald kd-a4">
          <div class="kpi-icon"><i class="bi bi-shield-fill-check"></i></div>
          <div class="kpi-val"><?= number_format($currentUser->getTrustScore(),0) ?></div>
          <div class="kpi-lbl">Score confiance</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Reservations -->
      <div class="col-lg-8">
        <div class="kd-dp-card kd-a2">
          <div class="dp-ch">
            <h5><span style="display:inline-block;width:8px;height:8px;background:#C45C2A;border-radius:50%;margin-right:.6rem;box-shadow:0 0 8px rgba(196,92,42,.6)"></span>Mes reservations</h5>
            <a href="<?= url('bookings') ?>" class="btn btn-dp-ghost btn-pill btn-sm">Tout voir <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
          <div class="dp-cb">
            <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
              <div style="font-size:3.5rem;margin-bottom:1rem">&#128506;</div>
              <h6 style="font-family:var(--font-d);font-weight:800;color:var(--dp-text);margin-bottom:.4rem">Aucune reservation</h6>
              <p style="color:var(--dp-muted);font-size:.88rem;margin-bottom:1.25rem">Votre aventure commence ici</p>
              <a href="<?= url('search') ?>" class="btn btn-dp-gold btn-pill px-5 fw-bold"><i class="bi bi-search me-2"></i>Rechercher</a>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($bookings,0,5) as $b): ?>
            <a href="<?= url('bookings/' . $b->getId()) ?>" class="kd-dp-row">
              <div style="width:46px;height:46px;border-radius:13px;background:rgba(196,92,42,.15);border:1px solid rgba(196,92,42,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-geo-alt-fill" style="color:#E8930A;font-size:1rem"></i>
              </div>
              <div class="flex-grow-1" style="min-width:0">
                <div class="kd-dp-route">
                  <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?= e($b->getTripDeparture()??'') ?></span>
                  <div class="kd-dp-route-line"></div>
                  <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px"><?= e($b->getTripArrival()??'') ?></span>
                </div>
                <div style="font-size:.75rem;color:var(--dp-muted);margin-top:.25rem">
                  <i class="bi bi-calendar3 me-1"></i><?= $b->getTripDate()?->format('d/m/Y H:i') ?>
                  <span class="mx-1" style="opacity:.3">|</span><?= e($b->getDriverName()??'') ?>
                </div>
              </div>
              <div class="text-end flex-shrink-0">
                <?php $sc=match($b->getStatus()->value){'PENDING'=>'kd-tag-pending','CONFIRMED'=>'kd-tag-confirmed','COMPLETED'=>'kd-tag-completed',default=>'kd-tag-cancelled'}; ?>
                <span class="kd-tag <?= $sc ?>"><?= $b->getStatus()->label() ?></span>
                <div style="font-size:.76rem;color:var(--dp-gold);margin-top:.25rem;font-weight:800"><?= formatPrice($b->getTotalPrice()) ?></div>
              </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 d-flex flex-column gap-3">

        <!-- Actions -->
        <div class="kd-dp-card kd-a3">
          <div class="dp-ch"><h5>&#9889; Actions rapides</h5></div>
          <div class="dp-cb d-grid gap-2">
            <a href="<?= url('search') ?>" class="btn btn-dp-gold btn-pill fw-bold"><i class="bi bi-search me-2"></i>Chercher un trajet</a>
            <a href="<?= url('bookings') ?>" class="btn btn-dp-ghost btn-pill"><i class="bi bi-ticket-perforated me-2"></i>Mes reservations</a>
            <a href="<?= url('profile/driver/request') ?>" class="btn btn-dp-ghost-em btn-pill"><i class="bi bi-car-front-fill me-2"></i>Devenir conducteur</a>
            <a href="<?= url('messages') ?>" class="btn btn-pill" style="background:rgba(59,130,246,.1);color:#60A5FA;border:1px solid rgba(59,130,246,.2)!important"><i class="bi bi-chat-dots me-2"></i>Messages</a>
            <a href="<?= url('profile/' . $currentUser->getId()) ?>" class="btn btn-pill" style="background:rgba(255,255,255,.05);color:var(--dp-muted);border:1px solid rgba(255,255,255,.08)!important"><i class="bi bi-person me-2"></i>Mon profil</a>
          </div>
        </div>

        <!-- CO2 -->
        <div class="kd-dp-card kd-a4" style="border-color:rgba(21,197,138,.15)">
          <div class="dp-ch" style="border-color:rgba(21,197,138,.1)">
            <h5 style="color:#6EE7B7">&#127807; Impact ecologique</h5>
          </div>
          <div class="dp-cb">
            <div class="d-flex align-items-end gap-2 mb-2">
              <div style="font-size:2.8rem;font-weight:900;font-family:var(--font-d);line-height:1;color:#6EE7B7"><?= number_format(count($bookings)*2.4,1) ?></div>
              <div style="padding-bottom:.35rem;font-size:.78rem;color:rgba(110,231,183,.7);font-weight:700">kg CO2<br>economies</div>
            </div>
            <div style="background:rgba(255,255,255,.06);border-radius:99px;height:6px;overflow:hidden">
              <div style="width:<?= min(100,count($bookings)*8) ?>%;height:100%;background:linear-gradient(90deg,#15C58A,#6EE7B7);border-radius:99px"></div>
            </div>
            <div style="font-size:.7rem;color:var(--dp-muted);margin-top:.4rem"><?= count($bookings) ?> trajets partages</div>
          </div>
        </div>

        <!-- Notifications -->
        <?php if (!empty($notifications)): ?>
        <div class="kd-dp-card">
          <div class="dp-ch">
            <h5>&#128276; Notifications</h5>
            <?php if ($unreadNotif>0): ?><span class="kd-dp-chip copper"><?= $unreadNotif ?> nouveau<?= $unreadNotif>1?'x':'' ?></span><?php endif; ?>
          </div>
          <div class="dp-cb d-flex flex-column gap-2">
            <?php foreach (array_slice($notifications,0,3) as $n): ?>
            <div class="d-flex gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)">
              <div style="width:32px;height:32px;border-radius:9px;background:rgba(196,92,42,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-bell-fill" style="color:#E8930A;font-size:.75rem"></i>
              </div>
              <div class="overflow-hidden">
                <div style="font-size:.8rem;font-weight:700;color:var(--dp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($n->getTitle()) ?></div>
                <div style="font-size:.72rem;color:var(--dp-muted)"><?= e(truncate($n->getBody(),42)) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
            <a href="<?= url('notifications') ?>" class="btn btn-dp-ghost btn-pill btn-sm w-100 mt-1">Voir tout</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</div>
