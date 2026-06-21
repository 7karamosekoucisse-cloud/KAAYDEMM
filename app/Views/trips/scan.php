<?php
$user = $currentUser ?? null;
$tripId = $trip->getId();
?>
<style>
#scanner-wrap { position:relative; width:100%; max-width:480px; margin:0 auto; }
#video { width:100%; border-radius:1rem; display:block; background:#000; }
#overlay { position:absolute; top:0; left:0; width:100%; height:100%; border-radius:1rem; pointer-events:none; }
.scan-line {
  position:absolute; left:10%; width:80%; height:3px;
  background:linear-gradient(90deg, transparent, #00ff88, transparent);
  animation: scanline 2s ease-in-out infinite;
  border-radius:2px; box-shadow:0 0 8px #00ff88;
}
@keyframes scanline { 0%,100%{top:15%} 50%{top:80%} }
.scan-corners::before, .scan-corners::after {
  content:''; position:absolute; width:30px; height:30px;
  border-color:#00ff88; border-style:solid; border-radius:4px;
}
.scan-corners::before { top:10%; left:10%; border-width:3px 0 0 3px; }
.scan-corners::after  { bottom:10%; right:10%; border-width:0 3px 3px 0; }

#result-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.85);
  display:flex; align-items:center; justify-content:center;
  z-index:9999; display:none;
}
.result-card { background:#1a1f2e; border-radius:1.5rem; padding:2.5rem; text-align:center; max-width:360px; width:90%; color:#fff; }
.checkmark { width:80px; height:80px; border-radius:50%; margin:0 auto 1rem;
  display:flex; align-items:center; justify-content:center; font-size:2.5rem; }
.checkmark-success { background:#00c853; animation:pop .4s cubic-bezier(.17,.67,.83,.67); }
.checkmark-error   { background:#e53935; animation:pop .4s cubic-bezier(.17,.67,.83,.67); }
@keyframes pop { 0%{transform:scale(0)} 80%{transform:scale(1.15)} 100%{transform:scale(1)} }
</style>

<div class="container py-4">
  <div class="mb-3">
    <a href="<?= url('trips/' . $tripId) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Retour au trajet
    </a>
  </div>

  <div class="text-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-qr-code-scan text-primary me-2"></i>Scanner le billet</h3>
    <p class="text-muted">Scannez le QR code du passager pour valider son embarquement.</p>
  </div>

  <div id="scanner-wrap">
    <video id="video" autoplay playsinline muted></video>
    <canvas id="canvas" style="display:none"></canvas>
    <div id="overlay">
      <div class="scan-line"></div>
      <div class="scan-corners"></div>
    </div>
  </div>

  <p class="text-center text-muted small mt-3" id="statusMsg">
    <i class="bi bi-camera me-1"></i>Autorisation camera en cours...
  </p>

  <div id="manualEntry" class="mt-4 d-none">
    <hr>
    <p class="text-muted small text-center">Ou saisir le code manuellement :</p>
    <form id="manualForm" class="d-flex gap-2">
      <input type="text" id="manualCode" class="form-control" placeholder="Code QR du billet...">
      <button type="submit" class="btn btn-primary">OK</button>
    </form>
  </div>
</div>

<!-- Overlay resultat -->
<div id="result-overlay">
  <div class="result-card">
    <div class="checkmark" id="resultIcon"></div>
    <h4 id="resultTitle" class="fw-bold mb-2"></h4>
    <p id="resultMsg" class="text-muted mb-4"></p>
    <button class="btn btn-primary w-100" onclick="resetScanner()">
      <i class="bi bi-arrow-repeat me-1"></i>Scanner un autre billet
    </button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jsqr/1.4.0/jsQR.js"></script>
<script>
const tripId    = <?= $tripId ?>;
const csrfToken = '<?= csrf_token() ?>';
const checkinUrl = '<?= url('checkin') ?>';

const video    = document.getElementById('video');
const canvas   = document.getElementById('canvas');
const ctx      = canvas.getContext('2d');
const statusMsg = document.getElementById('statusMsg');
let scanning   = true;
let stream     = null;

// Start camera
navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}, audio: false})
  .then(s => {
    stream = s;
    video.srcObject = s;
    video.addEventListener('loadedmetadata', () => {
      statusMsg.innerHTML = '<i class="bi bi-camera-video-fill text-success me-1"></i>Pointez vers un QR code...';
      document.getElementById('manualEntry').classList.remove('d-none');
      requestAnimationFrame(scanFrame);
    });
  })
  .catch(err => {
    statusMsg.innerHTML = '<i class="bi bi-exclamation-triangle text-warning me-1"></i>Camera non disponible. Utilisez la saisie manuelle.';
    document.getElementById('manualEntry').classList.remove('d-none');
  });

// Scan loop
function scanFrame() {
  if (!scanning) return;
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(img.data, img.width, img.height, {inversionAttempts: 'dontInvert'});
    if (code && code.data) {
      scanning = false;
      stopCamera();
      doCheckIn(code.data);
      return;
    }
  }
  requestAnimationFrame(scanFrame);
}

function stopCamera() {
  if (stream) stream.getTracks().forEach(t => t.stop());
}

// Check-in
function doCheckIn(qrData) {
  statusMsg.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Verification...';
  fetch(checkinUrl, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrfToken)
        + '&qr_code=' + encodeURIComponent(qrData),
    redirect: 'follow'
  })
  .then(r => {
    // success = redirected to a booking page
    const ok = r.ok && (r.url.includes('booking') || r.redirected);
    showResult(ok, qrData);
  })
  .catch(() => showResult(false, qrData));
}

function showResult(success, code) {
  const overlay = document.getElementById('result-overlay');
  const icon    = document.getElementById('resultIcon');
  const title   = document.getElementById('resultTitle');
  const msg     = document.getElementById('resultMsg');

  if (success) {
    icon.className  = 'checkmark checkmark-success';
    icon.textContent = 'OK';
    title.textContent = 'Embarquement valide !';
    msg.textContent  = 'Le passager a ete enregistre a bord.';
    // Vibrate on success
    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
  } else {
    icon.className  = 'checkmark checkmark-error';
    icon.textContent = '!';
    title.textContent = 'QR code invalide';
    msg.textContent  = 'Ce billet est inconnu ou deja utilise.';
    if (navigator.vibrate) navigator.vibrate(500);
  }
  overlay.style.display = 'flex';
}

function resetScanner() {
  document.getElementById('result-overlay').style.display = 'none';
  scanning = true;
  navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}})
    .then(s => { stream = s; video.srcObject = s; requestAnimationFrame(scanFrame); })
    .catch(() => {});
}

// Manuel
document.getElementById('manualForm').addEventListener('submit', e => {
  e.preventDefault();
  const code = document.getElementById('manualCode').value.trim();
  if (code) { scanning = false; stopCamera(); doCheckIn(code); }
});
</script>
