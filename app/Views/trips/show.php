<?php $user = $currentUser ?? null; ?>

<div class="kd-dp-page">
<div class="container-fluid px-0">

  <!-- Back -->
  <div class="container pt-3 pb-0 kd-a1">
    <a href="<?= url('search') ?>" class="kd-back-btn" style="color:var(--dp-muted)">
      <div class="kd-back-arrow" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1)"><i class="bi bi-arrow-left" style="color:var(--dp-text)"></i></div>
      <span style="color:var(--dp-muted)">Resultats de recherche</span>
    </a>
  </div>

  <!-- HERO TRAJET Dark Pro -->
  <div class="kd-dp-hero dp-copper kd-a1">
    <div class="container" style="position:relative;z-index:1">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div class="kd-dp-badge dp-copper"><i class="bi bi-map-fill"></i> Trajet</div>
          <h1 class="kd-dp-title">
            <?= e($trip->getDepartureCity()) ?>
            <span style="color:var(--dp-gold);margin:0 .5rem">&rarr;</span>
            <?= e($trip->getArrivalCity()) ?>
          </h1>
          <div class="kd-dp-sub">
            <i class="bi bi-calendar3"></i>
            <?= $trip->getDepartureAt()?->format('d/m/Y \a H\hi') ?>
          </div>
        </div>
        <span class="badge fs-6 <?= $trip->getStatus()->badgeClass() ?> rounded-pill px-3 py-2"><?= $trip->getStatus()->label() ?></span>
      </div>
      <!-- Stats -->
      <div class="d-flex gap-2 flex-wrap mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.08)">
        <span class="kd-dp-chip gold"><i class="bi bi-person-fill"></i> <?= $trip->getSeatsAvailable() ?>/<?= $trip->getSeatsOffered() ?> places</span>
        <span class="kd-dp-chip copper"><i class="bi bi-cash-coin"></i> <?= formatPrice($trip->getPricePerSeat()) ?>/place</span>
        <span class="kd-dp-chip emerald"><i class="bi bi-leaf-fill"></i> <?= number_format($trip->getCo2Saved(),1) ?> kg CO2</span>
      </div>
    </div>
  </div>

  <div class="container py-4">
    <div class="row g-4">
      <div class="col-lg-8">

        <!-- Itineraire -->
        <div class="kd-dp-card mb-4 kd-a2">
          <div class="dp-ch"><h5><i class="bi bi-signpost-split me-2" style="color:var(--dp-gold)"></i>Itineraire</h5></div>
          <div class="dp-cb">
            <div style="position:relative;padding-left:1.75rem">
              <div style="position:absolute;left:.55rem;top:1rem;bottom:1rem;width:2px;background:linear-gradient(to bottom,#C45C2A,#15C58A)"></div>
              <!-- Depart -->
              <div class="d-flex align-items-start gap-3 mb-3" style="position:relative">
                <div style="position:absolute;left:-1.2rem;width:14px;height:14px;border-radius:50%;background:#C45C2A;border:3px solid #06080F;box-shadow:0 0 0 3px rgba(196,92,42,.3);flex-shrink:0;margin-top:.15rem"></div>
                <div>
                  <div style="font-family:var(--font-d);font-weight:800;font-size:1rem;color:var(--dp-text)"><?= e($trip->getDepartureCity()) ?></div>
                  <div style="font-size:.78rem;color:var(--dp-muted)">Depart &mdash; <?= $trip->getDepartureAt()?->format('H:i') ?></div>
                </div>
              </div>
              <?php foreach ($trip->getStops() as $stop): ?>
              <div class="d-flex align-items-start gap-3 mb-3" style="position:relative">
                <div style="position:absolute;left:-1.15rem;width:12px;height:12px;border-radius:50%;background:#D4A853;border:2px solid #06080F;flex-shrink:0;margin-top:.2rem"></div>
                <div>
                  <div style="font-family:var(--font-d);font-weight:700;font-size:.92rem;color:var(--dp-text)"><?= e($stop->getCity()) ?></div>
                  <div style="font-size:.76rem;color:var(--dp-muted)">Arret intermediaire</div>
                </div>
              </div>
              <?php endforeach; ?>
              <!-- Arrivee -->
              <div class="d-flex align-items-start gap-3" style="position:relative">
                <div style="position:absolute;left:-1.2rem;width:14px;height:14px;border-radius:50%;background:#15C58A;border:3px solid #06080F;box-shadow:0 0 0 3px rgba(21,197,138,.3);flex-shrink:0;margin-top:.15rem"></div>
                <div>
                  <div style="font-family:var(--font-d);font-weight:800;font-size:1rem;color:var(--dp-text)"><?= e($trip->getArrivalCity()) ?></div>
                  <div style="font-size:.78rem;color:var(--dp-muted)">Arrivee</div>
                </div>
              </div>
            </div>
            <?php if ($trip->getDescription()): ?>
            <div class="mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.07)">
              <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--dp-muted);margin-bottom:.5rem"><i class="bi bi-chat-quote me-1"></i>Note du conducteur</div>
              <p style="font-size:.88rem;color:var(--dp-text);margin:0;line-height:1.7;opacity:.8"><?= nl2br(e($trip->getDescription())) ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Carte -->
        <div class="kd-dp-card mb-4 kd-a3">
          <div class="dp-ch">
            <h5><i class="bi bi-map me-2" style="color:#60A5FA"></i>Carte du trajet</h5>
            <?php $isDriver = $user && $user->getId() === $trip->getDriverId(); ?>
            <?php $tripActive = in_array($trip->getStatus()->value, ['OPEN','FULL']); ?>
            <?php if ($isDriver && $tripActive): ?>
            <button id="trackBtn" class="btn btn-sm btn-dp-ghost-em btn-pill fw-semibold" onclick="toggleTracking()">
              <i class="bi bi-broadcast me-1"></i>Partager ma position
            </button>
            <?php endif; ?>
          </div>
          <div style="padding:0">
            <div id="tripMap" style="height:300px;width:100%;border-radius:0 0 20px 20px"></div>
          </div>
          <?php if ($isDriver && $tripActive): ?>
          <div class="dp-cb" style="padding-top:.75rem;padding-bottom:.75rem">
            <span id="trackDriverStatus" style="font-size:.78rem;color:var(--dp-muted)"></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Passagers (conducteur) -->
        <?php if ($user && $trip->getDriverId() === $user->getId() && !empty($bookings)): ?>
        <div class="kd-dp-card mb-4 kd-a4">
          <div class="dp-ch">
            <h5><i class="bi bi-people-fill me-2" style="color:#6EE7B7"></i>Passagers reserves</h5>
            <?php if ($tripActive): ?>
            <a href="<?= url('trips/' . $trip->getId() . '/scan') ?>" class="btn btn-sm btn-dp-ghost-em btn-pill fw-bold"><i class="bi bi-qr-code-scan me-1"></i>Scanner</a>
            <?php endif; ?>
          </div>
          <div class="dp-cb d-flex flex-column gap-2">
            <?php foreach ($bookings as $b): ?>
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
              <div>
                <div style="font-weight:700;font-size:.9rem;color:var(--dp-text)"><?= e($b->getPassengerName()??'Passager') ?></div>
                <div style="font-size:.76rem;color:var(--dp-muted)"><?= $b->getSeatsBooked() ?> place(s) &middot; <span style="color:var(--dp-gold)"><?= formatPrice($b->getTotalPrice()) ?></span></div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge <?= $b->getStatus()->badgeClass() ?>"><?= $b->getStatus()->label() ?></span>
                <?php if ($b->getStatus()->value === 'PENDING'): ?>
                <form method="POST" action="<?= url('bookings/' . $b->getId() . '/confirm') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-dp-ghost-em btn-pill">Confirmer</button></form>
                <?php endif; ?>
                <a href="<?= url('messages/' . $b->getPassengerId()) ?>" class="btn btn-sm btn-dp-ghost btn-pill"><i class="bi bi-chat-dots"></i></a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- SIDEBAR -->
      <div class="col-lg-4 d-flex flex-column gap-3">

        <!-- Conducteur -->
        <div class="kd-dp-card kd-a2" style="border-color:rgba(21,197,138,.15)">
          <div style="padding:1.5rem;text-align:center">
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(21,197,138,.1);border:2px solid rgba(21,197,138,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
              <i class="bi bi-person-fill" style="font-size:1.8rem;color:#6EE7B7"></i>
            </div>
            <h5 style="font-family:var(--font-d);font-weight:800;color:var(--dp-text);margin-bottom:.25rem"><?= e($trip->getDriverName()??'Conducteur') ?></h5>
            <?php if ($trip->getDriverRating()): ?>
            <div style="color:#D4A853;font-size:.95rem;margin-bottom:.25rem"><?= stars((float)$trip->getDriverRating()) ?></div>
            <?php endif; ?>
            <div style="font-size:.78rem;color:var(--dp-muted);margin-bottom:1rem">Score confiance : <strong style="color:#6EE7B7"><?= number_format($trip->getDriverTrustScore(),0) ?>/100</strong></div>
            <a href="<?= url('profile/' . $trip->getDriverId()) ?>" class="btn btn-dp-ghost-em btn-pill btn-sm w-100 fw-bold"><i class="bi bi-person me-1"></i>Voir le profil</a>
          </div>
        </div>

        <!-- Suivi GPS (passager) -->
        <?php if (!$isDriver): ?>
        <div class="kd-dp-card kd-a3" style="border-color:rgba(59,130,246,.15)">
          <div class="dp-ch" style="border-color:rgba(59,130,246,.1)">
            <h5><i class="bi bi-broadcast me-2" style="color:#60A5FA"></i>Suivi en direct</h5>
            <div id="trackBadge" class="kd-dp-chip sapphire">
              <span class="kd-live-dot" style="display:none;margin-right:.3rem"></span>Hors ligne
            </div>
          </div>
          <div class="dp-cb">
            <p style="font-size:.8rem;color:var(--dp-muted);margin:0" id="trackTime">Le conducteur n'a pas encore partage sa position.</p>
            <div id="trackMap" class="d-none mt-3" style="height:200px;border-radius:12px;overflow:hidden"></div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Reservation -->
        <?php if ($user && $user->getId() !== $trip->getDriverId() && $trip->getStatus()->value === 'OPEN'): ?>
        <div class="kd-dp-card kd-a4" style="border-color:rgba(212,168,83,.2);background:rgba(212,168,83,.03)">
          <div class="dp-ch" style="border-color:rgba(212,168,83,.12)">
            <h5 style="color:var(--dp-gold)"><i class="bi bi-ticket-perforated me-2"></i>Reserver ce trajet</h5>
          </div>
          <div class="dp-cb">
            <form method="POST" action="<?= url('trips/' . $trip->getId() . '/book') ?>">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label" style="font-size:.78rem;font-weight:700;color:var(--dp-muted);text-transform:uppercase;letter-spacing:.4px">Nombre de places</label>
                <select name="seats" class="form-select" id="seatsSelect" data-price="<?= $trip->getPricePerSeat() ?>" onchange="updateTotal()"
                        style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.12);color:#fff">
                  <?php for ($i=1;$i<=min($trip->getSeatsAvailable(),8);$i++): ?>
                  <option value="<?= $i ?>"><?= $i ?> place<?= $i>1?'s':'' ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3" style="background:rgba(212,168,83,.08);border:1px solid rgba(212,168,83,.2)">
                <span style="font-size:.85rem;font-weight:700;color:var(--dp-muted)">Total</span>
                <span style="font-family:var(--font-d);font-size:1.4rem;font-weight:900;color:var(--dp-gold)" id="totalPrice"><?= formatPrice($trip->getPricePerSeat()) ?></span>
              </div>
              <button class="btn btn-dp-gold w-100 fw-bold btn-pill py-2" type="submit" style="font-size:1rem">
                <i class="bi bi-ticket-perforated me-2"></i>Reserver maintenant
              </button>
            </form>
          </div>
        </div>
        <?php elseif (!$user): ?>
        <div class="kd-dp-card kd-a4">
          <div class="dp-cb text-center py-4">
            <i class="bi bi-lock-fill" style="font-size:2rem;color:var(--dp-muted);margin-bottom:.75rem;display:block"></i>
            <p style="color:var(--dp-muted);margin-bottom:1rem;font-size:.88rem">Connectez-vous pour reserver</p>
            <a href="<?= url('login') ?>" class="btn btn-dp-gold w-100 btn-pill fw-bold">Se connecter</a>
          </div>
        </div>
        <?php elseif ($trip->getStatus()->value === 'FULL'): ?>
        <div class="kd-dp-card">
          <div class="dp-cb text-center py-4">
            <i class="bi bi-x-circle-fill" style="font-size:2rem;color:#F87171;margin-bottom:.75rem;display:block"></i>
            <p style="font-weight:700;color:var(--dp-text);margin:0">Trajet complet</p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Terminer (conducteur) -->
        <?php if ($user && $user->getId() === $trip->getDriverId() && $tripActive): ?>
        <div class="kd-dp-card">
          <div class="dp-cb">
            <p style="font-size:.82rem;color:var(--dp-muted);margin-bottom:.75rem;text-align:center">Le trajet s'est deroule ?</p>
            <form method="POST" action="<?= url('trips/' . $trip->getId() . '/complete') ?>">
              <?= csrf_field() ?>
              <button class="btn btn-dp-ghost-em w-100 fw-bold btn-pill py-2" onclick="return confirm('Marquer ce trajet comme termine ?')">
                <i class="bi bi-check2-circle me-2"></i>Terminer le trajet
              </button>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <!-- Signalement -->
        <?php if ($user && $user->getId() !== $trip->getDriverId()): ?>
        <div class="text-center">
          <button class="btn btn-link small" style="color:var(--dp-muted)" data-bs-toggle="modal" data-bs-target="#reportModal">
            <i class="bi bi-flag me-1"></i>Signaler ce trajet
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Modal Signalement -->
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:20px;background:#0A0F1E;border:1px solid rgba(255,255,255,.1)">
      <div class="modal-header border-0" style="border-bottom:1px solid rgba(255,255,255,.08)!important">
        <h5 class="modal-title" style="color:var(--dp-text)">Signaler ce trajet</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="<?= url('reports') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="target_id" value="<?= $trip->getId() ?>">
          <input type="hidden" name="target_type" value="trip">
          <select name="reason" class="form-select mb-3" required style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.12);color:#fff">
            <option value="">Motif...</option>
            <option value="spam">Spam / Fausse annonce</option>
            <option value="inappropriate">Contenu inapproprie</option>
            <option value="fraud">Fraude suspectee</option>
            <option value="other">Autre</option>
          </select>
          <textarea name="details" class="form-control" rows="3" placeholder="Details..." style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.12);color:#fff"></textarea>
          <button class="btn btn-danger w-100 mt-3 btn-pill">Envoyer le signalement</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const pricePerSeat = <?= $trip->getPricePerSeat() ?>;
const tripId       = <?= $trip->getId() ?>;
const csrfToken    = '<?= csrf_token() ?>';
const isDriver     = <?= ($user && $user->getId() === $trip->getDriverId()) ? 'true' : 'false' ?>;
const tripActive   = <?= in_array($trip->getStatus()->value, ['OPEN', 'FULL']) ? 'true' : 'false' ?>;

let map = null, driverIcon = null, driverMarker = null;

function updateTotal() {
    const seats = parseInt(document.getElementById('seatsSelect')?.value || 1);
    document.getElementById('totalPrice').textContent = new Intl.NumberFormat('fr-SN').format(seats * pricePerSeat) + ' FCFA';
}

let watchId = null, tracking = false;
function toggleTracking() { tracking ? stopTracking() : startTracking(); }

function startTracking() {
    if (!navigator.geolocation) { alert('Geolocalisation non supportee.'); return; }
    tracking = true;
    document.getElementById('trackBtn').innerHTML = '<i class="bi bi-broadcast-pin me-1"></i>Arreter le partage';
    document.getElementById('trackBtn').className = 'btn btn-sm btn-danger btn-pill fw-semibold';
    document.getElementById('trackDriverStatus').textContent = 'En attente du signal GPS...';
    watchId = navigator.geolocation.watchPosition(function(pos) {
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        if (!driverMarker) { driverMarker = L.marker([lat,lng],{icon:driverIcon}).addTo(map).bindPopup('Vous etes ici').openPopup(); }
        else { driverMarker.setLatLng([lat,lng]); }
        map.setView([lat,lng], 14);
        fetch('<?= url('trips/') ?>' + tripId + '/position', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'_csrf='+encodeURIComponent(csrfToken)+'&lat='+lat+'&lng='+lng
        }).then(r => {
            if (r.ok) { const now = new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); document.getElementById('trackDriverStatus').textContent = 'Mis a jour : '+now; }
        }).catch(() => { document.getElementById('trackDriverStatus').textContent = 'Erreur reseau...'; });
    }, err => { document.getElementById('trackDriverStatus').textContent = 'Erreur GPS : ' + err.message; },
    {enableHighAccuracy:true, maximumAge:4000, timeout:10000});
}

function stopTracking() {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    watchId = null; tracking = false;
    document.getElementById('trackBtn').innerHTML = '<i class="bi bi-broadcast me-1"></i>Partager ma position';
    document.getElementById('trackBtn').className = 'btn btn-sm btn-dp-ghost-em btn-pill fw-semibold';
    document.getElementById('trackDriverStatus').textContent = 'Partage arrete.';
}

let trackMap = null, trackMarker = null;
function ensureTrackMap() {
    if (trackMap) return;
    const el = document.getElementById('trackMap');
    if (!el) return;
    el.classList.remove('d-none');
    trackMap = L.map('trackMap').setView([14.6937,-17.4441],7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap'}).addTo(trackMap);
}

function pollDriverPosition() {
    fetch('<?= url('api/trips/') ?>' + tripId + '/position')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('trackBadge');
            const ttime = document.getElementById('trackTime');
            if (!badge) return;
            if (data.active) {
                ensureTrackMap();
                const latlng = [data.lat, data.lng];
                if (!trackMarker) { trackMarker = L.marker(latlng,{icon:driverIcon}).addTo(trackMap).bindPopup('<strong>Conducteur en route</strong>'); }
                else { trackMarker.setLatLng(latlng); }
                trackMap.setView(latlng, 14);
                badge.innerHTML = '<span style="display:inline-block;width:6px;height:6px;background:#6EE7B7;border-radius:50%;margin-right:.3rem;animation:kd-pulse-green 1.4s infinite"></span>En direct';
                const ago = Math.round((Date.now()/1000) - data.ts);
                if (ttime) ttime.textContent = 'Position mise a jour il y a ' + ago + 's';
            } else {
                badge.innerHTML = '<i class="bi bi-circle me-1"></i>Hors ligne';
                if (ttime) ttime.textContent = 'Le conducteur n\'a pas encore partage sa position.';
            }
        }).catch(()=>{});
}

window.addEventListener('load', function() {
    map = L.map('tripMap').setView([14.6937, -17.4441], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    driverIcon = L.divIcon({
        html: '<div style="background:#C45C2A;border:3px solid #fff;border-radius:50%;width:18px;height:18px;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>',
        iconSize:[18,18], iconAnchor:[9,9], className:''
    });
    if (!isDriver) {
        pollDriverPosition();
        setInterval(pollDriverPosition, 4000);
    }
}); // end window.addEventListener('load', ...)
</script>
