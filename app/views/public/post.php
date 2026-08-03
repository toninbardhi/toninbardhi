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
    <?php if (!empty($post['category_name'])): ?>
      · in <a href="<?= e(url($post['category_path'])) ?>"><?= e($post['category_name']) ?></a>
    <?php endif; ?>
  </div>

  <?php if (!empty($post['cover_image'])): ?>
    <img class="post-cover" src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>">
  <?php endif; ?>

  <?php /* Corpo HTML scritto da un autore fidato (admin/editor). */ ?>
  <div class="post-body"><?= $post['body'] ?? '' ?></div>

  <?php $ptags = Post::splitTags($post['tags'] ?? null); ?>
  <?php if ($ptags): ?>
    <div class="post-tags" style="margin-top:18px">
      <?php foreach ($ptags as $t): ?>
        <a class="tag-chip small" href="<?= e(url('/blog') . '?tag=' . rawurlencode($t)) ?>">#<?= e($t) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($post['category_name'])): ?>
    <p class="muted" style="margin-top:16px">
      📂 Sfoglia altri siti nella categoria
      <a href="<?= e(url($post['category_path'])) ?>"><strong><?= e($post['category_name']) ?></strong></a>.
    </p>
  <?php endif; ?>

  <p style="margin-top:20px"><a class="btn secondary small" href="<?= e(url('/blog')) ?>">‹ Tutti gli articoli</a></p>
</article>
