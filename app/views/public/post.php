<?php /** @var array $post */ ?>
<nav class="breadcrumb">
  <a href="<?= e(url('/')) ?>">Home</a>
  <span class="sep">›</span>
  <a href="<?= e(url('/blog')) ?>">Blog</a>
  <span class="sep">›</span>
  <span><?= e($post['title']) ?></span>
</nav>

<article class="panel post-single">
  <h1 style="margin-top:0"><?= e($post['title']) ?></h1>
  <div class="muted" style="font-size:.85rem; margin-bottom:16px">
    <?= e(date('d/m/Y', strtotime((string)($post['published_at'] ?? $post['created_at'])))) ?>
    <?php if (!empty($post['author'])): ?> · di <?= e($post['author']) ?><?php endif; ?>
  </div>
  <?php /* Corpo HTML scritto da un autore fidato (admin/editor). */ ?>
  <div class="post-body"><?= $post['body'] ?? '' ?></div>

  <p style="margin-top:20px"><a class="btn secondary small" href="<?= e(url('/blog')) ?>">‹ Tutti gli articoli</a></p>
</article>
