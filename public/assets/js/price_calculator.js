/* ============================================================
   KAAY DEM ! — Calculateur prix suggéré
   Prix = distance (km) × 15 FCFA, arrondi à 100 FCFA
   ============================================================ */

const KD_DISTANCES = {
  'Dakar-Thies':        70,  'Dakar-Thiès':         70,
  'Dakar-Saint-Louis':  270, 'Dakar-Kaolack':       195,
  'Dakar-Mbour':        80,  'Dakar-Ziguinchor':    470,
  'Dakar-Touba':        195, 'Dakar-Diourbel':      145,
  'Dakar-Tambacounda':  480, 'Dakar-Louga':         210,
  'Dakar-Fatick':       150, 'Dakar-Kolda':         430,
  'Thies-Saint-Louis':  230, 'Thiès-Saint-Louis':   230,
  'Thies-Kaolack':      130, 'Thiès-Kaolack':       130,
  'Thies-Touba':        130, 'Thiès-Touba':         130,
  'Saint-Louis-Louga':  90,  'Kaolack-Ziguinchor':  290,
  'Kaolack-Tambacounda':290, 'Mbour-Thies':         50,
  'Mbour-Thiès':        50,  'Ziguinchor-Kolda':    110,
};

const PRICE_PER_KM = 15; // FCFA par km

/**
 * Retourne la distance en km entre deux villes, ou null si inconnue.
 */
function kdGetDistance(from, to) {
  var normalize = function(s) {
    return s.trim()
      .replace(/[éèêë]/g,'e').replace(/[àâ]/g,'a')
      .replace(/[îï]/g,'i').replace(/[ôö]/g,'o')
      .replace(/[ùûü]/g,'u').replace(/ç/g,'c');
  };
  var f = normalize(from), t = normalize(to);
  var key1 = f + '-' + t, key2 = t + '-' + f;
  // Chercher avec ou sans accents
  for (var k in KD_DISTANCES) {
    var nk = normalize(k);
    if (nk === key1 || nk === key2) return KD_DISTANCES[k];
  }
  return null;
}

/**
 * Calcule et affiche le prix suggéré.
 * Retourne { distance, price } ou null.
 */
function kdSuggestPrice(from, to) {
  if (!from || !to) return null;
  var dist = kdGetDistance(from, to);
  if (!dist) return null;
  var raw   = dist * PRICE_PER_KM;
  var price = Math.ceil(raw / 100) * 100; // arrondi à 100 FCFA supérieur
  return { distance: dist, price: price };
}

/**
 * Attache les événements sur le formulaire de création de trajet.
 */
function kdInitPriceCalculator() {
  var fromEl  = document.getElementById('departure_city');
  var toEl    = document.getElementById('arrival_city');
  var priceEl = document.getElementById('price_per_seat');
  var distEl  = document.getElementById('distInput');
  var hintEl  = document.getElementById('priceHint');
  var co2El   = document.getElementById('co2Hint');

  if (!fromEl || !toEl || !priceEl) return;

  function update() {
    var from = fromEl.value.trim();
    var to   = toEl.value.trim();
    if (!from || !to) return;

    var result = kdSuggestPrice(from, to);
    if (!result) return;

    // Remplir le prix si vide ou si c'était une suggestion précédente
    if (!priceEl.dataset.userEdited) {
      priceEl.value = result.price;
    }

    // Distance
    if (distEl) distEl.value = result.distance;

    // Hint prix
    if (hintEl) {
      hintEl.innerHTML =
        '<i class="bi bi-magic me-1" style="color:var(--gold)"></i>' +
        'Prix suggéré : <strong>' + result.price.toLocaleString('fr') + ' FCFA</strong>' +
        ' · ' + result.distance + ' km · ' + PRICE_PER_KM + ' FCFA/km';
      hintEl.style.display = 'block';
    }

    // CO2
    if (co2El) {
      var co2 = (result.distance * 0.12).toFixed(1);
      co2El.innerHTML =
        '<i class="bi bi-leaf-fill me-1" style="color:var(--savane)"></i>' +
        'Économie CO₂ estimée : <strong>' + co2 + ' kg</strong> par passager';
      co2El.style.display = 'block';
    }
  }

  fromEl.addEventListener('change', update);
  fromEl.addEventListener('blur',   update);
  toEl.addEventListener('change',   update);
  toEl.addEventListener('blur',     update);

  // Marquer si l'utilisateur modifie manuellement le prix
  priceEl.addEventListener('input', function() {
    priceEl.dataset.userEdited = '1';
  });

  // Calculer si déjà rempli
  update();
}

document.addEventListener('DOMContentLoaded', kdInitPriceCalculator);
