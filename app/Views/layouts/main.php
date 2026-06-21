<?php
/* ── KaayDem – Layout principal (v6) ─────────────────────────────────── */

if (!function_exists('_kdGetLanIp')) {
    function _kdGetLanIp(): string {
        $s = @stream_socket_client('udp://8.8.8.8:53', $e, $m, 1);
        if ($s) {
            $name = stream_socket_get_name($s, false);
            fclose($s);
            $ip = $name ? explode(':', $name)[0] : '';
            if ($ip && $ip !== '127.0.0.1' && $ip !== '0.0.0.0') return $ip;
        }
        $ip = gethostbyname(gethostname());
        if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip)) return $ip;
        return '127.0.0.1';
    }
}

$_qrHost  = _kdGetLanIp();
$_qrSub   = '';
$_qrParts = explode('/', trim($_SERVER['SCRIPT_NAME'] ?? '', '/'));
foreach ($_qrParts as $_qrPart) {
    if ($_qrPart === '' || $_qrPart === 'index.php') break;
    $_qrSub .= '/' . $_qrPart;
}
$_qrUrl = 'http://' . $_qrHost . $_qrSub;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#D4A853">
<title><?= e($title ?? 'Kaay Dem !') ?></title>
<link rel="manifest" href="<?= url('/manifest.json') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=7">
</head>
<body>

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<main class="kd-main-content">
<?= $content ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<!-- QR FAB -->
<button id="kd-qr-fab" type="button"
        data-bs-toggle="modal" data-bs-target="#kdQrModal"
        title="Accès mobile QR Code" aria-label="QR Code">
  <i class="bi bi-qr-code"></i>
</button>

<!-- QR Modal -->
<div class="modal fade" id="kdQrModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"
         style="background:rgba(10,12,28,.96);border:1px solid rgba(212,168,83,.25);border-radius:20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" style="color:#D4A853;font-weight:700;">
          <i class="bi bi-qr-code me-2"></i>Accès mobile
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div id="kd-qr-container"
             style="display:inline-block;padding:16px;background:#fff;border-radius:12px;line-height:0;"></div>
        <p class="mt-3 mb-1" style="color:#94A3B8;font-size:.85rem;">
          Scannez depuis votre téléphone (même réseau Wi-Fi)
        </p>
        <code style="color:#D4A853;font-size:.8rem;"><?= htmlspecialchars($_qrUrl) ?></code>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=7"></script>

<script>
/* Service Worker */
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= url('/sw.js') ?>').catch(function(){});
}

/* Restaurer thème */
(function(){
  var t = localStorage.getItem('kd-theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
  var ic = document.getElementById('kd-theme-icon');
  if (ic) ic.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
})();

/* QR Code */
var _kdQrReady = false;
var _kdQrModal = document.getElementById('kdQrModal');
if (_kdQrModal) {
  _kdQrModal.addEventListener('shown.bs.modal', function() {
    if (_kdQrReady) return;
    _kdQrReady = true;
    var container = document.getElementById('kd-qr-container');
    container.innerHTML = '';
    try {
      new QRCode(container, {
        text: '<?= addslashes($_qrUrl) ?>',
        width: 200, height: 200,
        colorDark: '#06080F', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    } catch(err) {
      container.innerHTML = '<p style="padding:1rem;color:#D4A853;">Erreur QR: '+err.message+'</p>';
    }
  });
}

/* Scroll reveal — éléments partent visibles, animation à l'entrée viewport */
if ('IntersectionObserver' in window) {
  var _kdDelays = ['','kd-anim-d1','kd-anim-d2','kd-anim-d3'];
  var _kdObs = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      if (e.isIntersecting) {
        e.target.classList.add('kd-anim-in');
        _kdObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.06 });
  document.querySelectorAll(
    '.kd-dp-kpi,.kd-dp-card,.kd-dp-row,.kd-afr-card,.kd-kpi-soft,.kd-glass-card,.kd-glass-kpi'
  ).forEach(function(el, i) {
    if (_kdDelays[i % 4]) el.classList.add(_kdDelays[i % 4]);
    _kdObs.observe(el);
  });
}

/* Ripple sur boutons DP */
document.addEventListener('click', function(e) {
  var btn = e.target.closest('.btn-dp-gold,.btn-dp-ghost,.btn-dp-ghost-em,.kd-auth-btn');
  if (!btn) return;
  var r = document.createElement('span');
  var rect = btn.getBoundingClientRect();
  var size = Math.max(rect.width, rect.height) * 2;
  r.style.cssText = 'position:absolute;border-radius:50%;background:rgba(255,255,255,.3);'
    + 'width:'+size+'px;height:'+size+'px;'
    + 'left:'+(e.clientX-rect.left-size/2)+'px;top:'+(e.clientY-rect.top-size/2)+'px;'
    + 'pointer-events:none;animation:kd-ripple .55s ease-out forwards;';
  if (getComputedStyle(btn).position==='static') btn.style.position='relative';
  btn.style.overflow='hidden';
  btn.appendChild(r);
  r.addEventListener('animationend', function(){ r.remove(); });
});

/* Auth bouton — état chargement */
document.addEventListener('submit', function(e) {
  var btn = e.target.querySelector('.kd-auth-btn');
  if (!btn) return;
  var txt = btn.querySelector('.kd-btn-text');
  var ldr = btn.querySelector('.kd-btn-loader');
  if (txt) txt.style.display = 'none';
  if (ldr) ldr.style.display = 'inline-flex';
  btn.disabled = true;
  setTimeout(function() {
    btn.disabled = false;
    if (txt) txt.style.display = '';
    if (ldr) ldr.style.display = 'none';
  }, 8000);
});
</script>

</body>
</html>
