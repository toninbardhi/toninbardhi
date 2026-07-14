<?php
/** @var array $category @var array $children @var array $links @var array $crumbs
 *  @var int $page @var int $perPage @var int $total */
$totalPages = (int) ceil($total / $perPage);
?>
<nav class="breadcrumb">
  <a href="<?= e(url('/')) ?>">Home</a>
  <?php foreach ($crumbs as $c): ?>
    <span class="sep">›</span>
    <?php if ((int)$c['id'] === (int)$category['id']): ?>
      <span><?= e($c['name']) ?></span>
    <?php else: ?>
      <a href="<?= e(url($c['path'])) ?>"><?= e($c['name']) ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>

<div class="page-head">
  <h1><?= e($category['name']) ?></h1>
</div>
<?php if (!empty($category['description'])): ?>
  <p class="muted"><?= e($category['description']) ?></p>
<?php endif; ?>

<?php if ($children): ?>
  <div class="panel">
    <h3 style="margin-top:0">Sottocategorie</h3>
    <div class="grid">
      <?php foreach ($children as $ch): ?>
        <?php $cCount = Category::linkCount((int) $ch['id']); ?>
        <div class="cat-card">
          <h3><a href="<?= e(url($ch['path'])) ?>"><?= e($ch['name']) ?></a>
            <span class="count">(<?= $cCount ?>)</span></h3>
          <?php if (!empty($ch['description'])): ?>
            <p><?= e($ch['description']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<div class="panel">
  <h3 style="margin-top:0">Siti<?= $total ? ' (' . $total . ')' : '' ?></h3>
  <?php if (!$links): ?>
    <p class="muted">Nessun sito in questa categoria.
      <a href="<?= e(url('/suggest')) ?>">Suggeriscine uno</a>.</p>
  <?php else: ?>
    <ul class="link-list">
      <?php foreach ($links as $l): ?>
        <?php $feat = !empty($l['featured']); ?>
        <li class="<?= $feat ? 'is-featured' : '' ?>">
          <div class="title">
            <a href="<?= e($l['url']) ?>" target="_blank" rel="nofollow noopener sponsored"><?= e($l['title']) ?></a>
            <?php if ($feat): ?><span class="badge featured">Sponsorizzato</span><?php endif; ?>
          </div>
          <div class="url"><?= e($l['url']) ?></div>
          <?php if (!empty($l['description'])): ?>
            <div class="desc"><?= e($l['description']) ?></div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a class="btn secondary small" href="<?= e(url($category['path'])) ?>?page=<?= $page - 1 ?>">‹ Precedente</a>
        <?php endif; ?>
        <span class="muted">Pagina <?= $page ?> di <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
          <a class="btn secondary small" href="<?= e(url($category['path'])) ?>?page=<?= $page + 1 ?>">Successiva ›</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
