<div class="kd-auth-title text-center mb-1">Bon retour 👋</div>
<p class="kd-auth-sub text-center mb-4">Connectez-vous à votre compte</p>

<?php if ($error ?? null): ?>
<div class="alert alert-danger mb-3">
  <i class="bi bi-exclamation-triangle-fill"></i><span><?= e($error) ?></span>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Adresse email</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
      <input type="email" name="email" class="form-control" value="<?= e($email ?? '') ?>"
             placeholder="vous@universite.sn" required autofocus>
    </div>
  </div>
  <div class="mb-2">
    <label class="form-label d-flex justify-content-between align-items-center">
      <span>Mot de passe</span>
      <a href="<?= url('password/forgot') ?>" style="font-size:.8rem;font-weight:600">Oublié ?</a>
    </label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
      <input type="password" name="password" id="pwd" class="form-control" placeholder="••••••••" required>
      <button type="button" class="btn btn-ghost border-start-0" onclick="togglePwd('pwd','eye1')" style="border-radius:0 var(--r) var(--r) 0 !important;border:1.5px solid var(--border) !important;border-left:none !important">
        <i class="bi bi-eye" id="eye1" style="color:var(--muted)"></i>
      </button>
    </div>
  </div>
  <div class="form-check mb-4">
    <input type="checkbox" name="remember" class="form-check-input" id="rem">
    <label class="form-check-label" for="rem" style="font-size:.85rem;color:var(--muted)">Se souvenir de moi pendant 30 jours</label>
  </div>
  <button type="submit" class="btn kd-auth-btn w-100 btn-lg btn-pill py-3">
    <span class="kd-btn-text"><i class="bi bi-arrow-right-circle-fill me-2"></i>Se connecter</span>
    <span class="kd-btn-loader"><i class="bi bi-arrow-repeat kd-spin" style="font-size:1.2rem;color:#06080F"></i></span>
  </button>
</form>

<div class="text-center mt-4 pt-3" style="border-top:1px solid var(--border)">
  <span style="color:var(--muted);font-size:.88rem">Pas encore de compte ?</span>
  <a href="<?= url('register') ?>" class="ms-1 fw-bold" style="font-size:.88rem">Créer un compte</a>
</div>

<script>
function togglePwd(id, iconId) {
  const i = document.getElementById(id), ic = document.getElementById(iconId);
  i.type = i.type === 'password' ? 'text' : 'password';
  ic.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
