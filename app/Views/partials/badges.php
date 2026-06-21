<?php
// Badges système basés sur les stats de l'utilisateur
$userId = $badgeUserId ?? 0;
$allBadges = [
  ['code'=>'first_trip',     'icon'=>'🌟', 'name'=>'Premier voyage',    'desc'=>'A effectué son premier trajet',           'color'=>'#F59E0B'],
  ['code'=>'trusted_driver', 'icon'=>'🛡️', 'name'=>'Conducteur fiable', 'desc'=>'Score de confiance supérieur à 80',       'color'=>'#059669'],
  ['code'=>'early_adopter',  'icon'=>'⚡', 'name'=>'Pionnier',          'desc'=>'Parmi les premiers utilisateurs',         'color'=>'#7C3AED'],
  ['code'=>'eco_hero',       'icon'=>'🌿', 'name'=>'Éco-héros',         'desc'=>'Plus de 50kg de CO₂ économisé',           'color'=>'#059669'],
  ['code'=>'five_stars',     'icon'=>'⭐', 'name'=>'5 étoiles',         'desc'=>'Note parfaite sur 10+ avis',              'color'=>'#F59E0B'],
  ['code'=>'social',         'icon'=>'🤝', 'name'=>'Social',            'desc'=>'Plus de 20 passagers transportés',        'color'=>'#1246AA'],
  ['code'=>'loyal',          'icon'=>'💎', 'name'=>'Fidèle',            'desc'=>'Plus de 100 points de fidélité',          'color'=>'#6366F1'],
  ['code'=>'campus_hero',    'icon'=>'🏫', 'name'=>'Héros du campus',   'desc'=>'Plus de 5 universités desservies',        'color'=>'#EC4899'],
];
$earnedCodes = array_column($earnedBadges ?? [], 'code');
?>
<div class="kd-badges-grid">
  <?php foreach ($allBadges as $badge): ?>
  <?php $earned = in_array($badge['code'], $earnedCodes); ?>
  <div class="kd-badge-item <?= $earned ? 'earned' : 'locked' ?>"
       title="<?= e($badge['name']) ?> — <?= e($badge['desc']) ?>">
    <div class="kd-badge-icon" style="<?= $earned ? 'filter:none' : 'filter:grayscale(1);opacity:.35' ?>">
      <?= $badge['icon'] ?>
    </div>
    <div class="kd-badge-name"><?= e($badge['name']) ?></div>
    <?php if ($earned): ?>
    <div class="kd-badge-check"><i class="bi bi-check-circle-fill" style="color:<?= $badge['color'] ?>"></i></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<style>
.kd-badges-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:.75rem; }
.kd-badge-item { text-align:center; padding:.75rem .5rem; border-radius:12px; border:1.5px solid var(--border); background:var(--white); transition:all .2s; position:relative; cursor:default; }
.kd-badge-item.earned { border-color:rgba(240,165,0,.3); background:linear-gradient(135deg,#FFFBEB,var(--white)); }
.kd-badge-item.earned:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(240,165,0,.18); }
.kd-badge-icon { font-size:1.75rem; line-height:1; margin-bottom:.4rem; }
.kd-badge-name { font-size:.65rem; font-weight:700; color:var(--muted); line-height:1.2; }
.kd-badge-check { position:absolute; top:-5px; right:-5px; font-size:.8rem; }
</style>
