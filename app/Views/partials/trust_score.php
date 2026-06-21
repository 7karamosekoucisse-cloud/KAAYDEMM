<?php
$score = (float)($score ?? 50);
$color = $score >= 75 ? '#059669' : ($score >= 50 ? '#F59E0B' : '#E8442F');
$label = $score >= 75 ? 'Très fiable' : ($score >= 50 ? 'Fiable' : 'En progression');
$icon  = $score >= 75 ? 'bi-shield-fill-check' : ($score >= 50 ? 'bi-shield-fill' : 'bi-shield-fill-exclamation');
$segments = 10;
$filled = (int)round($score / $segments);
?>
<div class="kd-trust-widget">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="d-flex align-items-center gap-2">
      <i class="bi <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem"></i>
      <span style="font-family:var(--font-d);font-weight:700;font-size:.85rem">Score de confiance</span>
    </div>
    <div style="font-family:var(--font-d);font-size:1.3rem;font-weight:900;color:<?= $color ?>"><?= number_format($score, 0) ?></div>
  </div>

  <!-- Barre segmentée -->
  <div class="d-flex gap-1 mb-2">
    <?php for ($i = 0; $i < $segments; $i++): ?>
    <div style="
      flex:1; height:6px; border-radius:3px;
      background:<?= $i < $filled ? $color : 'var(--border)' ?>;
      transition:all .4s;
      opacity:<?= $i < $filled ? (0.6 + ($i/$segments)*0.4) : '1' ?>
    "></div>
    <?php endfor; ?>
  </div>

  <div class="d-flex justify-content-between align-items-center">
    <span style="font-size:.72rem;color:var(--muted);font-weight:600"><?= $label ?></span>
    <span style="font-size:.72rem;color:var(--muted)">sur 100</span>
  </div>
</div>
