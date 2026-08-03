<?php
/** @var array $posts @var int $total @var int $page @var int $perPage */
$totalPages = (int) ceil($total / $perPage);
?>
<h1>Blog</h1>

<?php if (!$posts): ?>
  <p class="muted">Nessun articolo pubblicato al momento.</p>
<?php else: ?>
  <?php foreach ($posts as $p): ?>
    <div class="panel">
      <h2 style="margin:0 0 6px">
        <a href="<?= e(url('/blog/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
      </h2>
      <div class="muted" style="font-size:.82rem; margin-bottom:8px">
        <?= e(date('d/m/Y', strtotime((string)($p['published_at'] ?? $p['created_at'])))) ?>
        <?php if (!empty($p['author'])): ?> · di <?= e($p['author']) ?><?php endif; ?>
      </div>
      <?php if (!empty($p['excerpt'])): ?>
        <p><?= e($p['excerpt']) ?></p>
      <?php endif; ?>
      <a class="btn small secondary" href="<?= e(url('/blog/' . $p['slug'])) ?>">Leggi »</a>
    </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a class="btn secondary small" href="<?= e(url('/blog')) ?>?page=<?= $page - 1 ?>">‹ Precedente</a>
      <?php endif; ?>
      <span class="muted">Pagina <?= $page ?> di <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="btn secondary small" href="<?= e(url('/blog')) ?>?page=<?= $page + 1 ?>">Successiva ›</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
