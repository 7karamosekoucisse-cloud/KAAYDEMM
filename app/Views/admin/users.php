<!-- BACK BUTTON -->
<div class="container pt-3 pb-1 kd-a1">
  <a href="<?= url('admin') ?>" class="kd-back-btn">
    <div class="kd-back-arrow"><i class="bi bi-arrow-left"></i></div>
    <span>Administration</span>
  </a>
</div>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Utilisateurs</h3>
    <span class="badge bg-primary fs-6"><?= $total ?> total</span>
  </div>
  <form class="mb-3 d-flex gap-2" action="<?= url('admin/users') ?>" method="GET">
    <input type="text" name="q" class="form-control" placeholder="Rechercher un utilisateur..." value="<?= e($query ?? '') ?>" style="max-width:300px">
    <button class="btn btn-primary"><i class="bi bi-search"></i></button>
  </form>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Utilisateur</th><th>Campus</th><th>Rôles</th><th>Score</th><th>Statut</th><th>Inscrit le</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= e($u->getFullName()) ?></div>
              <div class="text-muted small"><?= e($u->getEmail()) ?></div>
            </td>
            <td class="small"><?= e($u->getCampus()) ?></td>
            <td>
              <?php foreach ($u->getUserRoles() as $ur): ?>
                <span class="badge bg-secondary me-1"><?= $ur->getRole()->label() ?></span>
              <?php endforeach; ?>
            </td>
            <td>
              <div class="small"><?= number_format($u->getTrustScore(), 0) ?>/100</div>
              <div class="progress" style="height:4px">
                <div class="progress-bar bg-success" style="width:<?= $u->getTrustScore() ?>%"></div>
              </div>
            </td>
            <td>
              <?php if ($u->isBanned()): ?>
                <span class="badge bg-danger">Banni</span>
              <?php elseif ($u->isActive()): ?>
                <span class="badge bg-success">Actif</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactif</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted"><?= $u->getCreatedAt()?->format('d/m/Y') ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= url('profile/') . $u->getId() ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-person"></i></a>
                <?php if (!$u->isBanned()): ?>
                <form method="POST" action="<?= url('admin/users/') . $u->getId() . '/ban' ?>" onsubmit="return confirm('Bannir cet utilisateur ?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-ban"></i></button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?= url('admin/users/') . $u->getId() . '/unban' ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle"></i></button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>
