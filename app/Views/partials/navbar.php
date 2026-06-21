<?php $user = $currentUser ?? null; ?>
<nav class="navbar navbar-expand-lg navbar-dark kd-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand kd-brand" href="<?= url() ?>">
      <i class="bi bi-car-front-fill"></i> Kaay Dem !
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= url('search') ?>"><i class="bi bi-search me-1"></i>Rechercher</a>
        </li>
        <?php if ($user && $user->hasApprovedRole(\App\Enums\Role::DRIVER)): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= url('trips/create') ?>"><i class="bi bi-plus-circle me-1"></i>Publier</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item me-1 d-flex align-items-center">
          <button id="kd-theme-toggle" type="button"
                  class="btn kd-theme-btn p-0"
                  onclick="toggleTheme();var ic=document.getElementById('kd-theme-icon');var t=document.documentElement.getAttribute('data-theme');ic.className=t==='dark'?'bi bi-sun-fill':'bi bi-moon-stars-fill';"
                  title="Mode sombre / clair" aria-label="Changer le thème">
            <i id="kd-theme-icon" class="bi bi-moon-stars-fill"></i>
          </button>
        </li>
        <?php if ($user): ?>
          <li class="nav-item me-2">
            <a class="nav-link position-relative" href="<?= url('notifications') ?>" id="notifBell">
              <i class="bi bi-bell fs-5"></i>
              <span class="kd-badge" id="notifCount" style="display:none"></span>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <?php if ($user->getAvatar()): ?>
                <img src="<?= url('uploads/' . e($user->getAvatar())) ?>" class="kd-avatar-sm rounded-circle" alt="">
              <?php else: ?>
                <span class="kd-initials-sm"><?= e($user->getInitials()) ?></span>
              <?php endif; ?>
              <span class="d-none d-lg-inline"><?= e($user->getFirstName()) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><h6 class="dropdown-header"><?= e($user->getFullName()) ?></h6></li>
              <li><a class="dropdown-item" href="<?= url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="<?= url('bookings') ?>"><i class="bi bi-ticket-perforated me-2"></i>Mes réservations</a></li>
              <li><a class="dropdown-item" href="<?= url('messages') ?>"><i class="bi bi-chat-dots me-2"></i>Messages</a></li>
              <li><a class="dropdown-item" href="<?= url('profile/' . $user->getId()) ?>"><i class="bi bi-person me-2"></i>Mon profil</a></li>
              <li><a class="dropdown-item" href="<?= url('profile/edit') ?>"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
              <?php if ($user->hasApprovedRole(\App\Enums\Role::ADMIN)): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= url('admin') ?>"><i class="bi bi-shield-fill-exclamation me-2"></i>Administration</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="<?= url('logout') ?>" class="d-inline w-100">
                  <?= csrf_field() ?>
                  <button type="submit" class="dropdown-item text-danger w-100 text-start border-0 bg-transparent">
                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                  </button>
                </form>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('login') ?>">Connexion</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm ms-2 px-3" href="<?= url('register') ?>">S'inscrire</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
