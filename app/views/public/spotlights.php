<?php
/** @var array $items @var int $total @var int $page @var int $perPage
 *  @var int|null $categoryId @var array $categories */
$totalPages = (int) ceil($total / $perPage);
?>
<div class="page-head">
  <h1 style="margin:0">🔦 Spotlight</h1>
</div>
<p class="muted">Schede di presentazione: <strong>cos'è</strong> un prodotto/sito,
  <strong>come lo vede il web</strong> e <strong>come lo vede l'AI</strong>. Non sono recensioni.</p>

<form method="get" action="<?= e(url('/spotlight')) ?>" style="margin-bottom:18px">
  <select name="category_id" onchange="this.form.submit()"
          style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius)">
    <option value="">Tutte le categorie</option>
    <?= category_options($categories, $categoryId) ?>
  </select>
  <noscript><button class="btn small" type="submit">Filtra</button></noscript>
</form>

<?php if (!$items): ?>
  <p class="muted">Nessuna scheda pubblicata al momento.</p>
<?php else: ?>
  <?php foreach ($items as $it): ?>
    <div class="panel post-card">
      <?php if (!empty($it['cover_image'])): ?>
        <a href="<?= e(url('/spotlight/' . $it['slug'])) ?>" class="post-card-cover">
          <img src="<?= e($it['cover_image']) ?>" alt="" loading="lazy">
        </a>
      <?php endif; ?>
      <div class="post-card-body">
        <h2 style="margin:0 0 6px">
          <a href="<?= e(url('/spotlight/' . $it['slug'])) ?>"><?= e($it['title']) ?></a>
          <?php if (!empty($it['is_paid'])): ?>
            <span class="badge featured" style="vertical-align:middle">Sponsorizzata</span>
          <?php endif; ?>
        </h2>
        <div class="muted" style="font-size:.82rem; margin-bottom:8px">
          <?= e($it['subject_name']) ?>
          <?php if (!empty($it['category_name'])): ?>
            · in <a href="<?= e(url($it['category_path'])) ?>"><?= e($it['category_name']) ?></a>
          <?php endif; ?>
        </div>
        <?php $prev = trim(strip_tags((string)($it['presentation'] ?? ''))); ?>
        <?php if ($prev !== ''): ?>
          <p><?= e(mb_strimwidth($prev, 0, 180, '…')) ?></p>
        <?php endif; ?>
        <a class="btn small secondary" href="<?= e(url('/spotlight/' . $it['slug'])) ?>">Apri la scheda »</a>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
    <?php $q = $categoryId ? '&category_id=' . $categoryId : ''; ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a class="btn secondary small" href="<?= e(url('/spotlight')) ?>?page=<?= $page - 1 ?><?= $q ?>">‹ Precedente</a>
      <?php endif; ?>
      <span class="muted">Pagina <?= $page ?> di <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="btn secondary small" href="<?= e(url('/spotlight')) ?>?page=<?= $page + 1 ?><?= $q ?>">Successiva ›</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
