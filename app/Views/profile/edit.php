<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('dashboard') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Mon espace</span>
  </a>
</div>

<?php $u = $user ?? $currentUser; ?>
<div class="container pb-5">
  <div class="row justify-content-center g-4">

    <!-- Panneau Photo (gauche) -->
    <div class="col-lg-4">
      <div class="card p-4 text-center kd-a1">
        <h5 style="font-family:var(--font-d);font-weight:800;margin-bottom:1.5rem">Photo de profil</h5>

        <!-- Zone upload interactive -->
        <div class="kd-photo-upload-zone" id="photoZone" onclick="document.getElementById('avatarInput').click()">
          <div class="kd-photo-preview" id="photoPreview">
            <?php if ($u->getAvatar()): ?>
              <img src="<?= url('uploads/' . e($u->getAvatar())) ?>"
                   id="avatarImg" style="width:130px;height:130px;object-fit:cover;border-radius:50%" alt="">
            <?php else: ?>
              <div class="kd-av-placeholder mx-auto" style="width:130px;height:130px;font-size:2.5rem;font-weight:900">
                <?= e($u->getInitials()) ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="kd-photo-overlay">
            <i class="bi bi-camera-fill" style="font-size:1.75rem;color:#fff;margin-bottom:.4rem"></i>
            <div style="color:#fff;font-weight:700;font-size:.85rem">Changer la photo</div>
            <div style="color:rgba(255,255,255,.7);font-size:.72rem">JPG, PNG, WebP · Max 5 Mo</div>
          </div>
        </div>

        <div style="font-size:.75rem;color:var(--muted);margin-top:.75rem">
          <i class="bi bi-info-circle me-1"></i>Cliquez ou glissez une photo ici
        </div>

        <!-- Trust Score -->
        <div class="mt-4 pt-3" style="border-top:1px solid var(--border)">
          <?php $score = $u->getTrustScore(); require __DIR__ . '/../../Views/partials/trust_score.php'; ?>
        </div>

        <!-- Points fidélité -->
        <div class="mt-3 p-3 rounded-3" style="background:linear-gradient(135deg,#FFFBEB,var(--white));border:1px solid rgba(240,165,0,.2)">
          <div class="d-flex justify-content-between align-items-center">
            <span style="font-size:.82rem;font-weight:600"><i class="bi bi-gift-fill me-1" style="color:var(--gold)"></i>Points fidélité</span>
            <span style="font-family:var(--font-d);font-size:1.2rem;font-weight:900;color:var(--gold)"><?= number_format($u->getLoyaltyPoints()) ?></span>
          </div>
          <div style="font-size:.68rem;color:var(--muted);margin-top:.3rem"><?= max(0, 500 - ($u->getLoyaltyPoints() % 500)) ?> pts avant le prochain badge</div>
        </div>
      </div>
    </div>

    <!-- Formulaire principal -->
    <div class="col-lg-8">
      <div class="card p-4 kd-a2">
        <h4 style="font-family:var(--font-d);font-weight:900;margin-bottom:2rem">Mes informations</h4>

        <?php $errors = \Core\Session::getFlash('errors', []); ?>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div><ul class="mb-0 ps-3 small"><?php foreach ($errors as $em): ?><li><?= e($em) ?></li><?php endforeach; ?></ul></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('profile/edit') ?>" enctype="multipart/form-data" id="profileForm">
          <?= csrf_field() ?>
          <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="d-none" onchange="previewPhoto(this)">

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Prénom</label>
              <input type="text" name="first_name" class="form-control" value="<?= e($u->getFirstName()) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input type="text" name="last_name" class="form-control" value="<?= e($u->getLastName()) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <div class="input-group">
                <span class="input-group-text" style="font-weight:700">+221</span>
                <input type="tel" name="phone" class="form-control" value="<?= e($u->getPhone()) ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Campus / Université</label>
              <select name="campus" class="form-select">
                <?php foreach (['UCAD — Dakar'=>'UCAD','UGB — Saint-Louis'=>'UGB','UDZ — Ziguinchor'=>'UDZ','UADB — Bambey'=>'UADB','UTB / USSEIN — Thiès'=>'UTB','Autre'=>'Autre'] as $lbl => $val): ?>
                <option value="<?= $val ?>" <?= $u->getCampus()===$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input type="text" class="form-control" value="<?= e($u->getEmail()) ?>" disabled
                     style="background:var(--surface);color:var(--muted)">
              <div style="font-size:.72rem;color:var(--muted);margin-top:.3rem"><i class="bi bi-lock me-1"></i>L'email ne peut pas être modifié</div>
            </div>
          </div>

          <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary btn-pill fw-bold px-5">
              <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
            </button>
            <a href="<?= url('dashboard') ?>" class="btn btn-ghost btn-pill">Annuler</a>
          </div>
        </form>
      </div>

      <!-- Section sécurité -->
      <div class="card p-4 mt-4 kd-a3">
        <h5 style="font-family:var(--font-d);font-weight:800;margin-bottom:1.25rem">
          <i class="bi bi-shield-lock-fill me-2" style="color:var(--ocean-m)"></i>Sécurité
        </h5>
        <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:var(--surface)">
          <div>
            <div style="font-weight:700;font-size:.9rem">Mot de passe</div>
            <div style="font-size:.78rem;color:var(--muted)">Dernière modification inconnue</div>
          </div>
          <a href="<?= url('password/forgot') ?>" class="btn btn-ghost btn-sm btn-pill">Modifier →</a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.kd-photo-upload-zone {
  position: relative; display: inline-block;
  cursor: pointer; border-radius: 50%;
  width: 140px; height: 140px;
  margin: 0 auto .5rem;
  overflow: hidden;
  border: 3px dashed var(--border);
  transition: all .2s;
}
.kd-photo-upload-zone:hover { border-color: var(--ocean-m); }
.kd-photo-preview {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
}
.kd-photo-overlay {
  position: absolute; inset: 0;
  background: rgba(8,14,30,.65);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  opacity: 0; transition: opacity .2s;
  border-radius: 50%;
}
.kd-photo-upload-zone:hover .kd-photo-overlay { opacity: 1; }
</style>

<script>
function previewPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 5 * 1024 * 1024) {
    alert('La photo ne doit pas dépasser 5 Mo.');
    input.value = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = e => {
    const zone = document.getElementById('photoPreview');
    zone.innerHTML = `<img src="${e.target.result}" style="width:130px;height:130px;object-fit:cover;border-radius:50%" alt="">`;
  };
  reader.readAsDataURL(file);
}

// Drag & drop
const zone = document.getElementById('photoZone');
if (zone) {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--ocean-m)'; });
  zone.addEventListener('dragleave', () => { zone.style.borderColor = 'var(--border)'; });
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor = 'var(--border)';
    const dt = e.dataTransfer;
    if (dt.files.length) {
      document.getElementById('avatarInput').files = dt.files;
      previewPhoto(document.getElementById('avatarInput'));
    }
  });
}
</script>
