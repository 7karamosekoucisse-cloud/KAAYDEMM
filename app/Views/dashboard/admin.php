<?php $g = $global ?? []; ?>

<div class="kd-admin-wrapper">

  <!-- Header Admin Dark Pro -->
  <div class="kd-dp-hero dp-amethyst" style="border-radius:var(--radius-xl) var(--radius-xl) 0 0;margin:-2rem -2rem 2rem;padding-left:2rem;padding-right:2rem">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1">
      <div>
        <div class="kd-dp-badge dp-amethyst"><i class="bi bi-shield-fill-check"></i> Super Admin</div>
        <h2 class="kd-dp-title">Tableau de bord</h2>
        <div class="kd-dp-sub"><i class="bi bi-calendar3"></i> <?= date('l d F Y') ?> &middot; <?= date('H:i') ?></div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/drivers') ?>" class="btn btn-pill btn-sm fw-bold btn-dp-ghost"><i class="bi bi-car-front me-1"></i>Conducteurs en attente</a>
        <a href="<?= url('admin/export/csv') ?>" class="btn btn-pill btn-sm fw-bold btn-dp-ghost-em"><i class="bi bi-download me-1"></i>Export CSV</a>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
      <div class="kd-dp-kpi dp-gold kd-a1">
        <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
        <div class="kpi-val"><?= number_format($g['total_users']??0) ?></div>
        <div class="kpi-lbl">Utilisateurs</div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="kd-dp-kpi dp-emerald kd-a2">
        <div class="kpi-icon"><i class="bi bi-map-fill"></i></div>
        <div class="kpi-val"><?= number_format($g['total_trips']??0) ?></div>
        <div class="kpi-lbl">Trajets publies</div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="kd-dp-kpi dp-copper kd-a3">
        <div class="kpi-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
        <div class="kpi-val"><?= number_format($g['total_bookings']??0) ?></div>
        <div class="kpi-lbl">Reservations</div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="kd-dp-kpi dp-amethyst kd-a4">
        <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="kpi-val"><?= number_format($g['occupancy_rate']??0,1) ?>%</div>
        <div class="kpi-lbl">Taux d'occupation</div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-lg-8">
      <div class="kd-dp-card h-100 kd-a2">
        <div class="dp-ch">
          <h5><i class="bi bi-bar-chart-line me-2" style="color:var(--dp-gold)"></i>Reservations mensuelles</h5>
          <span class="kd-dp-chip emerald pulse">Live</span>
        </div>
        <div class="dp-cb"><canvas id="bookingsChart" style="width:100%;height:220px;display:block"></canvas></div>
      </div>
    </div>

    <!-- Top conducteurs -->
    <div class="col-lg-4">
      <div class="kd-dp-card h-100 kd-a3">
        <div class="dp-ch"><h5><i class="bi bi-trophy-fill me-2" style="color:var(--dp-gold)"></i>Top conducteurs</h5></div>
        <div class="dp-cb">
          <?php $drivers = $top_drivers ?? []; ?>
          <?php if (empty($drivers)): ?>
          <div class="text-center py-4">
            <i class="bi bi-car-front" style="font-size:2rem;color:rgba(255,255,255,.2);display:block;margin-bottom:.5rem"></i>
            <div style="font-size:.85rem;color:var(--dp-muted)">Aucun conducteur actif</div>
          </div>
          <?php else: ?>
          <div class="d-flex flex-column gap-1">
          <?php foreach ($drivers as $i => $d):
            $medals = ['linear-gradient(135deg,#D4A853,#B8882A)','linear-gradient(135deg,#94A3B8,#64748B)','linear-gradient(135deg,#C45C2A,#7F3010)'];
          ?>
          <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid rgba(255,255,255,.06)">
            <div style="width:26px;height:26px;border-radius:50%;flex-shrink:0;background:<?= $medals[$i]??'rgba(255,255,255,.1)' ?>;color:#fff;font-weight:900;font-size:.75rem;display:flex;align-items:center;justify-content:center;font-family:var(--font-d)"><?= $i+1 ?></div>
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.8);flex-shrink:0"><?= strtoupper(substr($d['first_name']??'X',0,1).substr($d['last_name']??'X',0,1)) ?></div>
            <div class="flex-grow-1" style="min-width:0">
              <div style="font-weight:700;font-size:.87rem;color:var(--dp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e(($d['first_name']??'').' '.($d['last_name']??'')) ?></div>
              <div style="font-size:.72rem;color:var(--dp-muted)"><?= (int)($d['trip_count']??0) ?> trajets</div>
            </div>
            <?php if (($d['avg_rating']??0)>0): ?>
            <div style="color:var(--dp-gold);font-size:.88rem;font-weight:800;flex-shrink:0"><?= number_format((float)($d['avg_rating']??0),1) ?>&#9733;</div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation rapide -->
  <div class="mb-3">
    <h6 style="font-family:var(--font-d);font-weight:800;color:rgba(255,255,255,.35);font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;margin-bottom:1rem">Gestion de la plateforme</h6>
  </div>
  <div class="row g-3 mb-4">
    <?php
    $links = [
      [url('admin/users'),   'people-fill',        'Utilisateurs',  'Gerer les comptes',    'rgba(212,168,83,.15)', '#D4A853'],
      [url('admin/drivers'), 'car-front-fill',      'Conducteurs',   'Valider les dossiers', 'rgba(21,197,138,.12)','#6EE7B7'],
      [url('admin/trips'),   'map-fill',            'Trajets',       "Superviser l'activite",'rgba(59,130,246,.12)','#60A5FA'],
      [url('admin/reports'), 'flag-fill',           'Signalements',  'Moderer',              'rgba(196,92,42,.15)', '#E8930A'],
      [url('admin/stats'),   'bar-chart-line-fill', 'Statistiques',  'Analyser',             'rgba(139,92,246,.12)','#A78BFA'],
      [url('admin/export/csv'),'download',          'Export CSV',    'Exporter',             'rgba(100,116,139,.12)','#94A3B8'],
    ];
    foreach ($links as $l):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
      <a href="<?= $l[0] ?>" class="kd-glass-link kd-a2">
        <div class="kd-glass-link-icon" style="background:<?= $l[4] ?>;color:<?= $l[5] ?>"><i class="bi bi-<?= $l[1] ?>"></i></div>
        <div class="kd-glass-link-title"><?= $l[2] ?></div>
        <div class="kd-glass-link-sub"><?= $l[3] ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
(function(){
  var chartData = <?= json_encode($monthly_revenue ?? []) ?>;
  function initAdminChart(){
    var el = document.getElementById('bookingsChart');
    if(!el||typeof Chart==='undefined'){setTimeout(initAdminChart,200);return;}
    if(el._chartInstance) return;
    var labels = chartData.length?chartData.map(r=>r.month||''):['Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aou','Sep','Oct','Nov','Dec'];
    var counts  = chartData.length?chartData.map(r=>parseInt(r.count||0)):[2,5,3,8,6,12,9,7,11,4,6,8];
    el._chartInstance = new Chart(el,{
      type:'bar',
      data:{labels,datasets:[{
        label:'Reservations',data:counts,
        backgroundColor:function(ctx){var g=ctx.chart.ctx.createLinearGradient(0,0,0,200);g.addColorStop(0,'rgba(212,168,83,.85)');g.addColorStop(1,'rgba(196,92,42,.3)');return g;},
        borderRadius:8,borderSkipped:false,borderWidth:0
      }]},
      options:{responsive:true,maintainAspectRatio:true,
        plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(6,8,15,.95)',titleColor:'#D4A853',bodyColor:'rgba(255,255,255,.85)',padding:12,cornerRadius:10}},
        scales:{
          x:{grid:{display:false},ticks:{color:'rgba(255,255,255,.3)',font:{family:'Space Grotesk',size:11}}},
          y:{beginAtZero:true,grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'rgba(255,255,255,.3)',font:{family:'Space Grotesk',size:11},precision:0}}
        }
      }
    });
  }
  document.readyState==='complete'?initAdminChart():window.addEventListener('load',initAdminChart);
  setTimeout(initAdminChart,400);
})();
</script>

<div class="container pb-4" style="padding-left:0;padding-right:0">
  <div class="kd-dp-card" style="margin-top:1rem">
    <div class="dp-ch">
      <h5><i class="bi bi-map-fill me-2" style="color:#60A5FA"></i>Carte des trajets actifs</h5>
    </div>
    <div class="dp-cb">
      <?php require __DIR__ . '/../../Views/partials/heatmap.php'; ?>
    </div>
 