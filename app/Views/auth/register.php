<div class="kd-auth-title text-center mb-1">Rejoindre Kaay Dem !</div>
<p class="kd-auth-sub text-center mb-4">Créez votre compte étudiant gratuitement</p>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-3">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <div><ul class="mb-0 ps-3 small"><?php foreach ($errors as $em): ?><li><?= e($em) ?></li><?php endforeach; ?></ul></div>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('register') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-6">
      <label class="form-label">Prénom *</label>
      <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name')) ?>" placeholder="Mamadou" required>
    </div>
    <div class="col-6">
      <label class="form-label">Nom *</label>
      <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name')) ?>" placeholder="Diallo" required>
    </div>
    <div class="col-12">
      <label class="form-label">Email *</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
        <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" placeholder="vous@universite.sn" required>
      </div>
    </div>
    <div class="col-12">
      <label class="form-label">Téléphone *</label>
      <div class="input-group">
        <span class="input-group-text" style="font-weight:700">+221</span>
        <input type="tel" name="phone" class="form-control" value="<?= e(old('phone')) ?>" placeholder="77 000 00 00" required>
      </div>
    </div>
    <div class="col-12">
      <label class="form-label">Campus / Université *</label>
      <select name="campus" class="form-select" required>
        <option value="">Choisir votre université...</option>
        <?php foreach (['UCAD — Dakar'=>'UCAD','UGB — Saint-Louis'=>'UGB','UDZ — Ziguinchor'=>'UDZ','UADB — Bambey'=>'UADB','UTB / USSEIN — Thiès'=>'UTB','Autre'=>'Autre'] as $label => $val): ?>
        <option value="<?= $val ?>" <?= old('campus')===$val?'selected':'' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Mot de passe *</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
        <input type="password" name="password" id="p1" class="form-control" placeholder="Min. 8 car., 1 majuscule, 1 chiffre" required>
        <button type="button" class="btn btn-ghost border-start-0" onclick="togglePwd('p1','e1')" style="border-radius:0 var(--r) var(--r) 0 !important;border:1.5px solid var(--border) !important;border-left:none !important">
          <i class="bi bi-eye" id="e1" style="color:var(--muted)"></i>
        </button>
      </div>
    </div>
    <div class="col-12">
      <label class="form-label">Confirmer le mot de passe *</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
        <input type="password" name="password_confirm" id="p2" class="form-control" placeholder="••••••••" required>
        <button type="button" class="btn btn-ghost border-start-0" onclick="togglePwd('p2','e2')" style="border-radius:0 var(--r) var(--r) 0 !important;border:1.5px solid var(--border) !important;border-left:none !important">
          <i class="bi bi-eye" id="e2" style="color:var(--muted)"></i>
        </button>
      </div>
    </div>
    <div class="col-12">
      <div class="form-check">
        <input type="checkbox" class="form-check-input" id="terms" required>
        <label class="form-check-label" for="terms" style="font-size:.83rem;color:var(--muted)">
          J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a>
        </label>
      </div>
    </div>
    <div class="col-12">
      <button type="submit" class="btn kd-auth-btn w-100 btn-lg btn-pill py-3">
        <span class="kd-btn-text"><i class="bi bi-person-plus-fill me-2"></i>Creer mon compte</span>
        <span class="kd-btn-loader"><i class="bi bi-arrow-repeat kd-spin" style="font-size:1.2rem;color:#06080F"></i></span>
      </button>
    </div>
  </div>
</form>

<div class="text-center mt-4 pt-3" style="border-top:1px solid var(--border)">
  <span style="color:var(--muted);font-size:.88rem">Déjà inscrit ?</span>
  <a href="<?= url('login') ?>" class="ms-1 fw-bold" style="font-size:.88rem">Se connecter</a>
</div>
<script>
function togglePwd(id, iconId) {
  const i = document.getElementById(id), ic = document.getElementById(iconId);
  i.type = i.type === 'password' ? 'text' : 'password';
  ic.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
