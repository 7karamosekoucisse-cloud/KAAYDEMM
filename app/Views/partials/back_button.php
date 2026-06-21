<?php
$label = $backLabel ?? 'Retour';
$href  = $backUrl  ?? 'javascript:history.back()';
?>
<a href="<?= $href ?>" class="kd-back-btn">
  <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
  <span><?= e($label) ?></span>
</a>
