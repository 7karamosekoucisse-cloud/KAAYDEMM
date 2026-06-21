/* ============================================================
   KAAY DEM ! — JS Premium v4
   ============================================================ */
'use strict';

/* ── Dark Mode ──────────────────────────────────────────── */
(function initTheme() {
  const saved = localStorage.getItem('kd-theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

window.toggleTheme = function() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('kd-theme', next);
};

/* ── Flash auto-dismiss ─────────────────────────────────── */
document.querySelectorAll('.alert-dismissible').forEach(el => {
  setTimeout(() => {
    try { bootstrap.Alert.getOrCreateInstance(el)?.close(); } catch {}
  }, 5500);
});

/* ── Notif badge ────────────────────────────────────────── */
(function() {
  const badge = document.getElementById('notifBadge');
  if (!badge) return;
  const scripts = document.querySelectorAll('script[src]');
  let base = '';
  scripts.forEach(s => {
    if (s.src.includes('app.js')) {
      base = s.src.replace(/\/assets\/js\/app\.js.*/, '');
    }
  });
  fetch(base + '/notifications', { headers: { 'Accept': 'application/json' } })
    .then(r => r.ok ? r.json() : [])
    .then(data => {
      const n = Array.isArray(data) ? data.filter(x => !x.read).length : 0;
      if (n > 0) { badge.textContent = n > 9 ? '9+' : n; badge.style.display = 'flex'; }
    }).catch(() => {});
})();

/* ── Intersection Observer animations ───────────────────── */
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('kd-visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.kd-a1,.kd-a2,.kd-a3,.kd-a4,.kd-a5,.kd-kpi,.kd-card-lift').forEach(el => {
    io.observe(el);
  });
}

/* ── Compteurs animés ───────────────────────────────────── */
function animateCount(el) {
  const target = parseInt(el.dataset.count);
  if (!target) return;
  const dur = 1800, step = 16;
  const inc = target / (dur / step);
  let cur = 0;
  const t = setInterval(() => {
    cur = Math.min(cur + inc, target);
    el.textContent = Math.floor(cur).toLocaleString('fr');
    if (cur >= target) clearInterval(t);
  }, step);
}
document.querySelectorAll('[data-count]').forEach(el => {
  const io2 = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) { animateCount(el); io2.disconnect(); }
  });
  io2.observe(el);
});

/* ── Price updater ──────────────────────────────────────── */
const seatsEl = document.getElementById('seatsSelect');
const totalEl = document.getElementById('totalPrice');
if (seatsEl && totalEl) {
  const price = parseFloat(seatsEl.dataset.price || 0);
  const update = () => {
    const n = parseInt(seatsEl.value) || 1;
    totalEl.textContent = new Intl.NumberFormat('fr-SN').format(n * price) + ' FCFA';
  };
  seatsEl.addEventListener('change', update);
}

/* ── Trip create price suggestion ───────────────────────── */
const distEl = document.getElementById('distInput');
const priceEl = document.getElementById('priceInput');
const co2El = document.getElementById('co2Hint');
if (distEl && priceEl) {
  distEl.addEventListener('input', () => {
    const d = parseFloat(distEl.value);
    if (!d || d <= 0) return;
    priceEl.value = Math.round(d * 15 / 100) * 100;
    if (co2El) co2El.textContent = `≈ ${(d * 0.12).toFixed(1)} kg CO₂ économisé par passager`;
  });
}

/* ── Star rating ────────────────────────────────────────── */
window.setRating = function(val) {
  const inp = document.getElementById('ratingInput');
  if (inp) inp.value = val;
  document.querySelectorAll('.kd-star').forEach((s, i) => {
    s.className = 'bi kd-star ' + (i < val ? 'bi-star-fill' : 'bi-star');
    s.style.color = i < val ? 'var(--gold)' : 'var(--border-dk)';
    s.style.fontSize = '1.6rem';
    s.style.cursor = 'pointer';
    s.style.transition = 'all .15s';
  });
};
document.querySelectorAll('.kd-star').forEach((s, i, arr) => {
  s.style.fontSize = '1.6rem'; s.style.cursor = 'pointer'; s.style.transition = 'all .15s';
  s.addEventListener('click', () => setRating(i + 1));
  s.addEventListener('mouseover', () => arr.forEach((st, j) => { st.style.color = j <= i ? 'var(--gold-lt)' : 'var(--border-dk)'; }));
});
document.getElementById('starContainer')?.addEventListener('mouseleave', () => {
  const val = parseInt(document.getElementById('ratingInput')?.value || 5);
  setRating(val);
});
if (document.querySelector('.kd-star')) setRating(5);

/* ── Trip row hover ─────────────────────────────────────── */
document.querySelectorAll('.kd-trip-row').forEach(el => {
  el.addEventListener('mouseenter', () => el.style.boxShadow = 'var(--sh-lg)');
  el.addEventListener('mouseleave', () => el.style.boxShadow = '');
});

/* ── Back button bouton retour ──────────────────────────── */
document.querySelectorAll('.kd-back-btn').forEach(btn => {
  if (btn.getAttribute('href') === 'javascript:history.back()') {
    btn.addEventListener('click', e => { e.preventDefault(); history.back(); });
  }
});

/* ── Confirm forms ──────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('submit', e => { if (!confirm(el.dataset.confirm || 'Confirmer ?')) e.preventDefault(); });
});

/* ── Chat auto scroll ───────────────────────────────────── */
const chatBox = document.getElementById('chatMessages');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

/* ── Leaflet ────────────────────────────────────────────── */
window.initMap = function(id, lat=14.693, lng=-17.444, zoom=7) {
  const el = document.getElementById(id);
  if (!el || typeof L === 'undefined') return null;
  const map = L.map(el).setView([lat, lng], zoom);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap', maxZoom:18 }).addTo(map);
  return map;
};

/* ── Double submit protection ───────────────────────────── */
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function() {
    const btn = this.querySelector('[type=submit]:not([data-no-protect])');
    if (btn) setTimeout(() => {
      btn.disabled = true;
      const orig = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" style="width:14px;height:14px"></span>Traitement...';
      setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 8000);
    }, 0);
  });
});

/* ── API helper ─────────────────────────────────────────── */
const BASE = (() => {
  const s = document.querySelector('script[src*="app.js"]');
  return s ? s.src.replace(/\/assets\/js\/app\.js.*/, '') : '';
})();
window.KaayDemApi = {
  async search(p) {
    const r = await fetch(`${BASE}/api/trips?${new URLSearchParams(p)}`, { headers:{'Accept':'application/json'} });
    return r.json();
  }
};
