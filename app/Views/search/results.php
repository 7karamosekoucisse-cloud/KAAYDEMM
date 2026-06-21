<div class="container py-4">

  <!-- Barre de filtre premium -->
  <div class="card mb-4" style="border-radius:var(--r-xl) !important">
    <div class="card-body p-3 p-md-4">
      <form action="<?= url('search') ?>" method="GET">
        <div class="row g-2 align-items-end">
          <div class="col-6 col-md-2">
            <label class="form-label"><i class="bi bi-circle-fill me-1" style="color:var(--savane);font-size:.5rem"></i>Départ</label>
            <input type="text" name="from" class="form-control" value="<?= e($criteria['departure_city'] ?? '') ?>" placeholder="Dakar" list="cities-list">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label"><i class="bi bi-circle-fill me-1" style="color:var(--coral);font-size:.5rem"></i>Arrivée</label>
            <input type="text" name="to" class="form-control" value="<?= e($criteria['arrival_city'] ?? '') ?>" placeholder="Thiès" list="cities-list">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label"><i class="bi bi-calendar me-1"></i>Date</label>
            <input type="date" name="date" class="form-control" value="<?= e($criteria['date'] ?? '') ?>">
          </div>
          <div class="col-4 col-md-2">
            <label class="form-label"><i class="bi bi-cash me-1"></i>Prix max</label>
            <input type="number" name="max_price" class="form-control" value="<?= e($criteria['max_price'] ?? '') ?>" min="0" placeholder="FCFA">
          </div>
          <div class="col-4 col-md-2">
            <label class="form-label"><i class="bi bi-person me-1"></i>Places</label>
            <select name="seats" class="form-select">
              <?php for ($i=1;$i<=8;$i++): ?>
              <option value="<?=$i?>" <?= (($criteria['min_seats']??1)==$i)?'selected':'' ?>><?=$i?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-4 col-md-1">
            <label class="form-label">Trier</label>
            <select name="sort" class="form-select">
              <option value="date"    <?= ($criteria['sort']??'')==='date'   ?'selected':''?>>Date</option>
              <option value="price"   <?= ($criteria['sort']??'')==='price'  ?'selected':''?>>Prix ↑</option>
              <option value="price_d" <?= ($criteria['sort']??'')==='price_d'?'selected':''?>>Prix ↓</option>
              <option value="rating"  <?= ($criteria['sort']??'')==='rating' ?'selected':''?>>Note</option>
            </select>
          </div>
          <div class="col-12 col-md-1">
            <button class="btn btn-primary w-100 btn-pill" style="padding:.65rem"><i class="bi bi-search"></i></button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Résultats header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <?php if (!empty($criteria['departure_city']) || !empty($criteria['arrival_city'])): ?>
        <h5 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;margin:0">
          <?= e($criteria['departure_city'] ?? '?') ?>
          <i class="bi bi-arrow-right mx-2" style="color:var(--muted-lt)"></i>
          <?= e($criteria['arrival_city'] ?? '?') ?>
        </h5>
      <?php else: ?>
        <h5 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;margin:0">Tous les trajets disponibles</h5>
      <?php endif; ?>
      <?php if (!empty($criteria['date'])): ?>
        <div style="font-size:.82rem;color:var(--muted);margin-top:.2rem">
          <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($criteria['date'])) ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="kd-tag kd-tag-confirmed"><?= $total ?? 0 ?> résultat<?= ($total??0) > 1 ? 's' : '' ?></div>
  </div>

  <!-- Liste trajets -->
  <?php if (empty($trips)): ?>
    <div class="kd-empty">
      <div class="kd-empty-icon">🔍</div>
      <h5 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800">Aucun trajet trouvé</h5>
      <p style="color:var(--muted);font-size:.9rem">Essayez d'autres dates ou élargissez votre recherche.</p>
      <a href="<?= url('search') ?>" class="btn btn-primary btn-pill px-4 mt-2">Nouvelle recherche</a>
    </div>
  <?php else: ?>
    <div class="d-flex flex-column gap-3">
      <?php foreach ($trips as $trip): ?>
      <a href="<?= url('trips/' . $trip->getId()) ?>" class="kd-trip-row text-decoration-none kd-animate-1">
        <div class="row align-items-center g-3">

          <!-- Conducteur -->
          <div class="col-auto">
            <div class="kd-av-placeholder kd-av-48">
              <?= e($trip->getInitials ?? strtoupper(substr($trip->getDriverName() ?? 'X', 0, 1))) ?>
            </div>
          </div>

          <!-- Route -->
          <div class="col">
            <div class="kd-route-display mb-1">
              <span class="kd-city"><?= e($trip->getDepartureCity()) ?></span>
              <div class="kd-route-line"><i class="bi bi-arrow-right"></i></div>
              <span class="kd-city"><?= e($trip->getArrivalCity()) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <span style="font-size:.82rem;color:var(--muted)">
                <i class="bi bi-calendar me-1"></i><?= $trip->getDepartureAt()?->format('d/m/Y · H:i') ?>
              </span>
              <div class="kd-driver-chip">
                <div class="kd-av-placeholder" style="width:22px;height:22px;font-size:.6rem">
                  <?= strtoupper(substr($trip->getDriverName() ?? 'X', 0, 1)) ?>
                </div>
                <span style="font-weight:600;font-size:.8rem"><?= e($trip->getDriverName() ?? 'Conducteur') ?></span>
                <?php if ($trip->getDriverRating()): ?>
                  <span style="color:var(--gold);font-size:.78rem">
                    <i class="bi bi-star-fill"></i> <?= number_format((float)$trip->getDriverRating(), 1) ?>
                  </span>
                <?php endif; ?>
              </div>
              <?php if ($trip->getCo2Saved() > 0): ?>
              <span style="font-size:.75rem;color:var(--savane);font-weight:600">
                <i class="bi bi-leaf-fill"></i> <?= number_format($trip->getCo2Saved(), 1) ?> kg CO₂
              </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Prix + places -->
          <div class="col-auto text-end">
            <div class="kd-price-tag"><?= number_format($trip->getPricePerSeat(), 0, ',', ' ') ?><small> FCFA</small></div>
            <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">
              <i class="bi bi-person-fill me-1"></i><?= $trip->getSeatsAvailable() ?> place<?= $trip->getSeatsAvailable()>1?'s':'' ?> dispo.
            </div>
            <div class="mt-1">
              <?php
                $statusClass = match($trip->getStatus()->value) {
                  'OPEN'      => 'kd-tag-open',
                  'FULL'      => 'kd-tag-full',
                  'CANCELLED' => 'kd-tag-cancelled',
                  default     => 'kd-tag-completed',
                };
              ?>
              <span class="kd-tag <?= $statusClass ?>"><?= $trip->getStatus()->label() ?></span>
            </div>
          </div>

          <!-- Arrow -->
          <div class="col-auto d-none d-md-block">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--surface);display:flex;align-items:center;justify-content:center;color:var(--ocean)">
              <i class="bi bi-arrow-right"></i>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (($pages ?? 1) > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
      <ul class="pagination gap-1">
        <li class="page-item <?= ($page??1)<=1?'disabled':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>($page??1)-1])) ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <?php for ($i=max(1,($page??1)-2);$i<=min($pages??1,($page??1)+2);$i++): ?>
        <li class="page-item <?= $i===($page??1)?'active':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?=$i?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page??1)>=($pages??1)?'disabled':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>($page??1)+1])) ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<datalist id="cities-list">
  <option value="Dakar"><option value="Thiès"><option value="Saint-Louis">
  <option value="Ziguinchor"><option value="Touba"><option value="Kaolack">
  <option value="Mbour"><option value="Diourbel"><option value="Tambacounda">
</datalist>
