<?php /** @var array $roots */ ?>
<div class="panel">
  <h1><?= e(setting('home_title', setting('site_name', $CONFIG['site']['name'] ?? 'Directory'))) ?></h1>
  <p class="muted"><?= e(setting('home_subtitle', 'Sfoglia le categorie della directory o usa la ricerca in alto.')) ?></p>
</div>

<?php if (!$roots): ?>
  <p class="muted">Nessuna categoria ancora. Accedi all'<a href="<?= e(url('/admin')) ?>">area riservata</a> per crearne.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($roots as $cat): ?>
      <?php
        $children = Category::children((int) $cat['id']);
        $count = Category::linkCount((int) $cat['id']);
      ?>
      <div class="cat-card">
        <h3><a href="<?= e(url($cat['path'])) ?>"><?= e($cat['name']) ?></a></h3>
        <?php if (!empty($cat['description'])): ?>
          <p><?= e($cat['description']) ?></p>
        <?php endif; ?>
        <?php if ($children): ?>
          <p class="count" style="margin-top:8px">
            <?php foreach (array_slice($children, 0, 5) as $i => $ch): ?>
              <?= $i ? ' · ' : '' ?><a href="<?= e(url($ch['path'])) ?>"><?= e($ch['name']) ?></a>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>
        <p class="count"><?= $count ?> sit<?= $count === 1 ? 'o' : 'i' ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
