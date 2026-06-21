<?php if (($pages ?? 1) > 1): ?>
<nav aria-label="Pagination" class="mt-4">
  <ul class="pagination justify-content-center">
    <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($page ?? 1) - 1])) ?>">
        <i class="bi bi-chevron-left"></i>
      </a>
    </li>
    <?php for ($i = max(1, ($page ?? 1) - 2); $i <= min($pages ?? 1, ($page ?? 1) + 2); $i++): ?>
    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= ($page ?? 1) >= ($pages ?? 1) ? 'disabled' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($page ?? 1) + 1])) ?>">
        <i class="bi bi-chevron-right"></i>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>
