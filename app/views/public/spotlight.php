<?php
/** @var array $item */
$hasWeb = trim((string)($item['web_view'] ?? '')) !== '';
$hasAi  = trim((string)($item['ai_view'] ?? '')) !== '';
$hasPre = trim((string)($item['presentation'] ?? '')) !== '';
?>
<nav class="breadcrumb">
  <a href="<?= e(url('/')) ?>">Home</a>
  <span class="sep">›</span>
  <a href="<?= e(url('/spotlight')) ?>">Spotlight</a>
  <span class="sep">›</span>
  <span><?= e($item['title']) ?></span>
</nav>

<article class="panel post-single spotlight-single">
  <?php if (!empty($item['ai_generated'])): ?>
    <div class="sponsor-disclosure">
      🤖 <strong>Contenuto assistito da AI.</strong>
      Questa è una scheda di presentazione, non una recensione: non contiene voti o giudizi.
    </div>
  <?php endif; ?>
  <?php if (!empty($item['is_paid'])): ?>
    <div class="sponsor-disclosure">
      <span class="badge featured">Inclusione sponsorizzata</span>
      Inclusione a pagamento<?php if (!empty($item['sponsor_name'])): ?> a cura di <strong><?= e($item['sponsor_name']) ?></strong><?php endif; ?>.
      La presentazione resta redazionale e indipendente.
    </div>
  <?php endif; ?>

  <h1 style="margin-top:0"><?= e($item['title']) ?></h1>
  <div class="muted" style="font-size:.85rem; margin-bottom:16px">
    <?php if (!empty($item['subject_url'])): ?>
      🔗 <a href="<?= e($item['subject_url']) ?>" rel="nofollow noopener" target="_blank"><?= e($item['subject_name']) ?></a>
    <?php else: ?>
      <?= e($item['subject_name']) ?>
    <?php endif; ?>
    <?php if (!empty($item['category_name'])): ?>
      · in <a href="<?= e(url($item['category_path'])) ?>"><?= e($item['category_name']) ?></a>
    <?php endif; ?>
  </div>

  <?php if (!empty($item['cover_image'])): ?>
    <img class="post-cover" src="<?= e($item['cover_image']) ?>" alt="<?= e($item['subject_name']) ?>">
  <?php endif; ?>

  <?php if ($hasPre): ?>
    <section class="sl-section">
      <h2>📋 Presentazione</h2>
      <div class="post-body"><?= $item['presentation'] ?></div>
    </section>
  <?php endif; ?>

  <?php if ($hasWeb): ?>
    <section class="sl-section sl-web">
      <h2>🌐 Come lo vede il web</h2>
      <div class="post-body"><?= $item['web_view'] ?></div>
    </section>
  <?php endif; ?>

  <?php if ($hasAi): ?>
    <section class="sl-section sl-ai">
      <h2>🤖 Come lo vede l'AI</h2>
      <div class="post-body"><?= $item['ai_view'] ?></div>
    </section>
  <?php endif; ?>

  <?php if (!empty($item['category_name'])): ?>
    <p class="muted" style="margin-top:16px">
      📂 Altri siti nella categoria
      <a href="<?= e(url($item['category_path'])) ?>"><strong><?= e($item['category_name']) ?></strong></a>.
    </p>
  <?php endif; ?>

  <p style="margin-top:20px"><a class="btn secondary small" href="<?= e(url('/spotlight')) ?>">‹ Tutte le schede</a></p>
</article>

<?php
// Dati strutturati schema.org: aiuta motori di ricerca e AI a capire la scheda.
$ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'Article',
    'headline' => $item['title'],
    'about'    => array_filter([
        '@type' => 'Thing',
        'name'  => $item['subject_name'],
        'url'   => $item['subject_url'] ?: null,
    ]),
    'datePublished' => !empty($item['published_at']) ? date('c', strtotime($item['published_at'])) : null,
    'author'   => ['@type' => 'Organization', 'name' => setting('site_name', 'webdirectory.link')],
];
$ld = array_filter($ld);
?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
