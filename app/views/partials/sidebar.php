<?php
/** Sidebar pubblica: box "In evidenza" (top/sponsor) e "Aggiunti di recente". */
$topLinks    = Link::topFeatured(5);
$recentLinks = Link::recent(8);
?>
<aside class="sidebar">

  <div class="side-box">
    <h3 class="side-title">★ In evidenza</h3>
    <?php if ($topLinks): ?>
      <ul class="side-list">
        <?php foreach ($topLinks as $l): ?>
          <li>
            <a class="side-link" href="<?= e($l['url']) ?>" target="_blank" rel="nofollow noopener sponsored"><?= e($l['title']) ?></a>
            <span class="side-cat">in <a href="<?= e(url($l['category_path'])) ?>"><?= e($l['category_name']) ?></a></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="side-empty">
        Vuoi il tuo sito qui, in cima?
        <a href="<?= e(url('/suggest')) ?>">Proponilo</a> e chiedi la promozione.
      </p>
    <?php endif; ?>
  </div>

  <div class="side-box">
    <h3 class="side-title">🆕 Aggiunti di recente</h3>
    <?php if ($recentLinks): ?>
      <ul class="side-list">
        <?php foreach ($recentLinks as $l): ?>
          <li>
            <a class="side-link" href="<?= e($l['url']) ?>" target="_blank" rel="nofollow noopener"><?= e($l['title']) ?></a>
            <span class="side-cat">in <a href="<?= e(url($l['category_path'])) ?>"><?= e($l['category_name']) ?></a></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="side-empty">Ancora nessun sito. <a href="<?= e(url('/suggest')) ?>">Suggeriscine uno</a>.</p>
    <?php endif; ?>
  </div>

</aside>
