<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>

<div class="container pb-5">
  <div class="row justify-content-center g-4">

    <!-- Formulaire principal -->
    <div class="col-lg-7">
      <div class="card p-4 kd-a1">
        <h4 style="font-family:var(--font-d);font-weight:900;margin-bottom:.4rem">Publier un trajet</h4>
        <p style="color:var(--muted);font-size:.88rem;margin-bottom:1.75rem">Remplissez les détails. Le prix est suggéré automatiquement selon la distance.</p>

        <?php $errors = \Core\Session::getFlash('errors', []); ?>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div><ul class="mb-0 ps-3 small"><?php foreach ($errors as $em): ?><li><?= e($em) ?></li><?php endforeach; ?></ul></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('trips') ?>" id="tripForm">
          <?= csrf_field() ?>

          <!-- Départ / Arrivée -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">
                <i class="bi bi-circle-fill me-1" style="color:#0D9F6E;font-size:.4rem;vertical-align:middle"></i>
                VILLE DE DÉPART *
              </label>
              <input type="text" name="departure_city" id="departure_city"
                     class="form-control" value="<?= e(old('departure_city')) ?>"
                     placeholder="Ex : Dakar" list="cities-list" required autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label">
                <i class="bi bi-circle-fill me-1" style="color:#E8442F;font-size:.4rem;vertical-align:middle"></i>
                VILLE D'ARRIVÉE *
              </label>
              <input type="text" name="arrival_city" id="arrival_city"
                     class="form-control" value="<?= e(old('arrival_city')) ?>"
                     placeholder="Ex : Thiès" list="cities-list" required autocomplete="off">
            </div>
          </div>

          <!-- Mini carte de prévisualisation -->
          <div id="routeMapBox" style="display:none;margin-bottom:1rem">
            <div style="font-size:.75rem;font-weight:600;color:var(--muted);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px">
              <i class="bi bi-map me-1"></i>Aperçu de la route
            </div>
            <div id="routeMap" style="height:180px;border-radius:12px;overflow:hidden;border:1.5px solid var(--border);background:#1a2035"></div>
          </div>

          <!-- Date & Places -->
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label"><i class="bi bi-calendar me-1"></i>DATE ET HEURE DE DÉPART *</label>
              <input type="datetime-local" name="departure_at" class="form-control"
                     value="<?= e(old('departure_at')) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="col-md-5">
              <label class="form-label"><i class="bi bi-people me-1"></i>PLACES OFFERTES *</label>
              <select name="seats_offered" class="form-select" required>
                <?php for($i=1;$i<=8;$i++): ?>
                <option value="<?= $i ?>" <?= old('seats_offered')==$i?'selected':'' ?>><?= $i ?> place<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <!-- Champ distance caché (calculé auto) -->
          <input type="hidden" name="distance_km" id="distInput" value="<?= e(old('distance_km','0')) ?>">

          <!-- Prix par place -->
          <div class="mb-1">
            <label class="form-label"><i class="bi bi-cash-coin me-1"></i>PRIX PAR PLACE *</label>
            <div class="input-group">
              <input type="number" name="price_per_seat" id="price_per_seat"
                     class="form-control"
                     value="<?= e(old('price_per_seat','')) ?>"
                     min="0" max="50000" placeholder="Prix en FCFA" required>
              <span class="input-group-text" style="border-left:none;border-radius:0 var(--r) var(--r) 0 !important;font-weight:700;color:var(--ocean-m)">FCFA</span>
            </div>
          </div>

          <!-- Hints prix et CO2 -->
          <div id="priceHint" style="display:none;font-size:.78rem;color:var(--ink);margin-bottom:.3rem;padding:.45rem .85rem;background:rgba(240,165,0,.08);border:1px solid rgba(240,165,0,.2);border-radius:var(--r-sm)"></div>
          <div id="co2Hint"   style="display:none;font-size:.78rem;margin-bottom:1rem;padding:.45rem .85rem;background:rgba(13,159,110,.06);border:1px solid rgba(13,159,110,.15);border-radius:var(--r-sm)"></div>

          <!-- Arrêts intermédiaires -->
          <div class="mb-3 mt-3">
            <label class="form-label"><i class="bi bi-signpost-2 me-1"></i>ARRÊTS INTERMÉDIAIRES</label>
            <div id="stopsContainer"></div>
            <button type="button" class="btn btn-ghost btn-sm btn-pill mt-1" onclick="addStop()">
              <i class="bi bi-plus-circle me-1"></i>Ajouter un arrêt
            </button>
          </div>

          <!-- Description -->
          <div class="mb-4">
            <label class="form-label"><i class="bi bi-chat-quote me-1"></i>DESCRIPTION (OPTIONNEL)</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Point de rendez-vous, bagages autorisés, climatisation..."><?= e(old('description')) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-pill fw-bold btn-lg w-100">
            <i class="bi bi-send-fill me-2"></i>Publier ce trajet
          </button>
        </form>
      </div>
    </div>

    <!-- Sidebar conseils -->
    <div class="col-lg-5 d-none d-lg-block">
      <div style="background:linear-gradient(135deg,#080E1E 0%,#0D2050 100%);border-radius:20px;padding:1.75rem;position:sticky;top:90px">

        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.4rem">
          <i class="bi bi-lightbulb-fill" style="color:#F0A500;font-size:1.15rem"></i>
          <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.05rem;color:#FFFFFF">Conseils conducteur</span>
        </div>

        <!-- Conseil 1 -->
        <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1.1rem">
          <i class="bi bi-geo-alt-fill" style="color:#15C58A;font-size:1.05rem;flex-shrink:0;margin-top:.1rem"></i>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.88rem;color:#FFFFFF;margin-bottom:.25rem">Point de rendez-vous</div>
            <div style="font-size:.78rem;color:#94A3B8;line-height:1.65">Précisez un lieu connu : gare, supermarché, station Total, rond-point...</div>
          </div>
        </div>

        <!-- Conseil 2 -->
        <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1.1rem">
          <i class="bi bi-cash-coin" style="color:#FFB929;font-size:1.05rem;flex-shrink:0;margin-top:.1rem"></i>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.88rem;color:#FFFFFF;margin-bottom:.25rem">Prix juste automatique</div>
            <div style="font-size:.78rem;color:#94A3B8;line-height:1.65">Le prix se calcule dès que vous entrez les villes (15 FCFA/km). Modifiable.</div>
          </div>
        </div>

        <!-- Conseil 3 -->
        <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1.1rem">
          <i class="bi bi-clock-fill" style="color:#60A5FA;font-size:1.05rem;flex-shrink:0;margin-top:.1rem"></i>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.88rem;color:#FFFFFF;margin-bottom:.25rem">Ponctualité</div>
            <div style="font-size:.78rem;color:#94A3B8;line-height:1.65">Respectez l'heure de départ. Les passagers vous attendent — votre note en dépend.</div>
          </div>
        </div>

        <!-- Conseil 4 -->
        <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1.1rem">
          <i class="bi bi-shield-fill-check" style="color:#34D399;font-size:1.05rem;flex-shrink:0;margin-top:.1rem"></i>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.88rem;color:#FFFFFF;margin-bottom:.25rem">Sécurité</div>
            <div style="font-size:.78rem;color:#94A3B8;line-height:1.65">Respectez le code de la route. La confiance se construit trajet après trajet.</div>
          </div>
        </div>

        <!-- Conseil 5 -->
        <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1.4rem">
          <i class="bi bi-leaf-fill" style="color:#34D399;font-size:1.05rem;flex-shrink:0;margin-top:.1rem"></i>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.88rem;color:#FFFFFF;margin-bottom:.25rem">Impact CO₂</div>
            <div style="font-size:.78rem;color:#94A3B8;line-height:1.65">Chaque passager réduit l'empreinte carbone. Kaay Dem calcule votre impact.</div>
          </div>
        </div>

        <div style="border-top:1px solid rgba(255,255,255,.1);padding-top:1rem;text-align:center">
          <span style="font-size:.72rem;color:#475569">
            <i class="bi bi-eye me-1"></i>Visible par tous les passagers inscrits
          </span>
        </div>
      </div>
    </div>

  </div>
</div>

<datalist id="cities-list">
  <option value="Dakar"><option value="Thiès"><option value="Saint-Louis">
  <option value="Ziguinchor"><option value="Touba"><option value="Kaolack">
  <option value="Mbour"><option value="Diourbel"><option value="Tambacounda">
  <option value="Louga"><option value="Fatick"><option value="Kolda">
</datalist>

<!-- Scripts -->
<script src="<?= asset('js/price_calculator.js') ?>"></script>

<script>
/* Arrêts intermédiaires */
function addStop() {
  var c = document.getElementById('stopsContainer');
  var d = document.createElement('div');
  d.className = 'kd-stop-row';
  d.style.cssText = 'display:flex;gap:.5rem;margin-bottom:.5rem;align-items:center';
  d.innerHTML = '<input type="text" name="stops[]" class="form-control form-control-sm" '
    + 'placeholder="Ville intermédiaire" list="cities-list" autocomplete="off">'
    + '<button type="button" onclick="this.closest(\'.kd-stop-row\').remove()" '
    + 'style="background:none;border:none;color:var(--muted);padding:.2rem .4rem;cursor:pointer;font-size:1.1rem;flex-shrink:0">'
    + '<i class="bi bi-x-circle"></i></button>';
  c.appendChild(d);
  d.querySelector('input').focus();
}

/* Coordonnées GPS des villes sénégalaises */
var KD_COORDS = {
  'Dakar':       [14.6937, -17.4441],
  'Thies':       [14.7910, -16.9359],
  'Thiès':       [14.7910, -16.9359],
  'Saint-Louis': [16.0179, -16.4896],
  'Ziguinchor':  [12.5584, -16.2733],
  'Touba':       [14.8600, -15.8833],
  'Kaolack':     [14.1490, -16.0726],
  'Mbour':       [14.3974, -16.9672],
  'Diourbel':    [14.6558, -16.2341],
  'Tambacounda': [13.7707, -13.6673],
  'Louga':       [15.6173, -16.2247],
  'Fatick':      [14.3390, -16.4110],
  'Kolda':       [12.8967, -14.9403]
};

var _map = null, _layer = null;

function getCoords(name) {
  if (!name) return null;
  if (KD_COORDS[name]) return KD_COORDS[name];
  // Normaliser les accents
  var n = name.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  for (var k in KD_COORDS) {
    var kn = k.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    if (kn === n) return KD_COORDS[k];
  }
  return null;
}

function updateMap() {
  var from = (document.getElementById('departure_city').value || '').trim();
  var to   = (document.getElementById('arrival_city').value || '').trim();
  var box  = document.getElementById('routeMapBox');

  if (!from || !to) return;
  var fc = getCoords(from);
  var tc = getCoords(to);
  if (!fc || !tc) return;

  // Afficher la boîte carte
  box.style.display = 'block';

  // Initialiser la carte si pas encore fait
  if (!_map) {
    _map = L.map('routeMap', {
      zoomControl: false,
      scrollWheelZoom: false,
      attributionControl: false
    }).setView([(fc[0]+tc[0])/2, (fc[1]+tc[1])/2], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 16
    }).addTo(_map);
  }

  // Supprimer l'ancienne couche
  if (_layer) { _map.removeLayer(_layer); }

  // Icônes personnalisées
  var iconDep = L.divIcon({
    html: '<div style="width:14px;height:14px;border-radius:50%;background:#0D9F6E;border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>',
    iconSize:[14,14], iconAnchor:[7,7], className:''
  });
  var iconArr = L.divIcon({
    html: '<div style="width:14px;height:14px;border-radius:50%;background:#E8442F;border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>',
    iconSize:[14,14], iconAnchor:[7,7], className:''
  });

  _layer = L.layerGroup([
    L.polyline([fc, tc], {color:'#2E7DEB', weight:5, opacity:.9}),
    L.marker(fc, {icon: iconDep}).bindTooltip(from, {permanent:true, direction:'top', offset:[0,-8]}),
    L.marker(tc, {icon: iconArr}).bindTooltip(to,   {permanent:true, direction:'top', offset:[0,-8]})
  ]).addTo(_map);

  _map.fitBounds([fc, tc], {padding:[30,30]});
  _map.invalidateSize();
}

// Attacher les événements
document.addEventListener('DOMContentLoaded', function() {
  var dep = document.getElementById('departure_city');
  var arr = document.getElementById('arrival_city');
  if (dep) { dep.addEventListener('change', updateMap); dep.addEventListener('blur', updateMap); }
  if (arr) { arr.addEventListener('change', updateMap); arr.addEventListener('blur', updateMap); }
});
</script>

<style>
.leaflet-tooltip {
  background: rgba(8,14,30,.9) !important;
  border: 1px solid rgba(255,255,255,.2) !important;
  color: #fff !important;
  font-weight: 700 !important;
  font-size: .75rem !important;
  border-radius: 6px !important;
  box-shadow: 0 2px 8px rgba(0,0,0,.3) !important;
}
.leaflet-tooltip::before { display: none !important; }
</style>