<?php
/** @var array $posts @var int $total @var int $page @var int $perPage
 *  @var string|null $tag @var array $tagCloud */
$totalPages = (int) ceil($total / $perPage);
?>
<div class="page-head">
  <h1 style="margin:0">Blog</h1>
</div>

<?php if (!empty($tagCloud)): ?>
  <div class="tag-cloud">
    <a class="tag-chip<?= $tag === null ? ' active' : '' ?>" href="<?= e(url('/blog')) ?>">Tutti</a>
    <?php foreach ($tagCloud as $tc): ?>
      <a class="tag-chip<?= ($tag !== null && mb_strtolower($tag) === mb_strtolower($tc['tag'])) ? ' active' : '' ?>"
         href="<?= e(url('/blog') . '?tag=' . rawurlencode($tc['tag'])) ?>">
        #<?= e($tc['tag']) ?> <span class="tc-n"><?= (int) $tc['count'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($tag !== null): ?>
  <p class="muted">Articoli con il tag <strong>#<?= e($tag) ?></strong>
    · <a href="<?= e(url('/blog')) ?>">rimuovi filtro</a></p>
<?php endif; ?>

<?php if (!$posts): ?>
  <p class="muted"><?= $tag !== null ? 'Nessun articolo con questo tag.' : 'Nessun articolo pubblicato al momento.' ?></p>
<?php else: ?>
  <?php foreach ($posts as $p): ?>
    <div class="panel post-card">
      <?php if (!empty($p['cover_image'])): ?>
        <a href="<?= e(url('/blog/' . $p['slug'])) ?>" class="post-card-cover">
          <img src="<?= e($p['cover_image']) ?>" alt="" loading="lazy">
        </a>
      <?php endif; ?>
      <div class="post-card-body">
        <h2 style="margin:0 0 6px">
          <a href="<?= e(url('/blog/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
          <?php if (!empty($p['is_sponsored'])): ?>
            <span class="badge featured" style="vertical-align:middle">Sponsorizzato</span>
          <?php endif; ?>
        </h2>
        <div class="muted" style="font-size:.82rem; margin-bottom:8px">
          <?= e(date('d/m/Y', strtotime((string)($p['published_at'] ?? $p['created_at'])))) ?>
          <?php if (!empty($p['author'])): ?> · di <?= e($p['author']) ?><?php endif; ?>
          <?php if (!empty($p['category_name'])): ?>
            · in <a href="<?= e(url($p['category_path'])) ?>"><?= e($p['category_name']) ?></a>
          <?php endif; ?>
        </div>
        <?php if (!empty($p['excerpt'])): ?>
          <p><?= e($p['excerpt']) ?></p>
        <?php endif; ?>
        <?php $ptags = Post::splitTags($p['tags'] ?? null); ?>
        <?php if ($ptags): ?>
          <div class="post-tags">
            <?php foreach ($ptags as $t): ?>
              <a class="tag-chip small" href="<?= e(url('/blog') . '?tag=' . rawurlencode($t)) ?>">#<?= e($t) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <a class="btn small secondary" href="<?= e(url('/blog/' . $p['slug'])) ?>">Leggi »</a>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
    <?php $q = $tag !== null ? '&tag=' . rawurlencode($tag) : ''; ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a class="btn secondary small" href="<?= e(url('/blog')) ?>?page=<?= $page - 1 ?><?= $q ?>">‹ Precedente</a>
      <?php endif; ?>
      <span class="muted">Pagina <?= $page ?> di <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="btn secondary small" href="<?= e(url('/blog')) ?>?page=<?= $page + 1 ?><?= $q ?>">Successiva ›</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php
  $spInfo  = setting('sponsor_info', '');
  $spPrice = trim((string) setting('sponsor_price', ''));
?>
<?php if ($spInfo !== '' || $spPrice !== ''): ?>
  <div class="panel sponsor-cta">
    <h3 style="margin:0 0 6px">📣 Vuoi un articolo sul nostro blog?</h3>
    <?php if ($spInfo !== ''): ?><p style="margin:0 0 8px"><?= e($spInfo) ?></p><?php endif; ?>
    <?php if ($spPrice !== ''): ?>
      <p class="sponsor-price">Costo: <strong><?= e($spPrice) ?> € ad articolo</strong></p>
    <?php endif; ?>
    <a class="btn small" href="<?= e(url('/contatti')) ?>">Contattaci</a>
  </div>
<?php endif; ?>
