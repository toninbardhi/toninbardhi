<?php
/** @var string $q @var array $results @var int $total @var int $page @var int $perPage */
$totalPages = (int) ceil($total / $perPage);
?>
<h1>Ricerca</h1>

<div class="panel">
  <form action="<?= e(url('/search')) ?>" method="get" class="search" style="display:flex; gap:8px">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cosa cerchi?" style="flex:1; padding:9px 12px; border:1px solid var(--border); border-radius:8px">
    <button class="btn" type="submit">Cerca</button>
  </form>
</div>

<?php if ($q === ''): ?>
  <p class="muted">Scrivi una parola chiave per cercare tra i siti della directory.</p>
<?php elseif (!$results): ?>
  <p class="muted">Nessun risultato per «<?= e($q) ?>».
    Puoi <a href="<?= e(url('/suggest')) ?>">suggerire un sito</a>.</p>
<?php else: ?>
  <p class="muted"><?= $total ?> risultat<?= $total === 1 ? 'o' : 'i' ?> per «<?= e($q) ?>»</p>
  <ul class="link-list">
    <?php $showCategory = true; foreach ($results as $l): ?>
      <?php require APP_PATH . '/views/partials/link_item.php'; ?>
    <?php endforeach; ?>
  </ul>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a class="btn secondary small" href="<?= e(url('/search')) ?>?q=<?= e(urlencode($q)) ?>&page=<?= $page - 1 ?>">‹ Precedente</a>
      <?php endif; ?>
      <span class="muted">Pagina <?= $page ?> di <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="btn secondary small" href="<?= e(url('/search')) ?>?q=<?= e(urlencode($q)) ?>&page=<?= $page + 1 ?>">Successiva ›</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
