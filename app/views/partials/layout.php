<?php
/** Layout pubblico. Variabili: $content, $title, $CONFIG, $metaDescription. */
$siteName = $CONFIG['site']['name'] ?? 'Directory';
$flash = take_flash();
$pageTitle = (isset($title) && $title !== null && $title !== '')
    ? $title . ' · ' . $siteName
    : $siteName;
$desc = $metaDescription ?? 'Directory web organizzata in categorie: sfoglia e scopri i migliori siti selezionati.';
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$canonical = abs_url($reqPath);
?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/favicon.svg')) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e($siteName) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta name="twitter:card" content="summary">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
  <header class="site-header">
    <div class="container bar">
      <a class="logo" href="<?= e(url('/')) ?>">
        <img src="<?= e(url('assets/favicon.svg')) ?>" alt="" width="28" height="28" class="logo-icon">
        <?= e($siteName) ?>
      </a>
      <form class="search" action="<?= e(url('/search')) ?>" method="get" role="search">
        <input type="search" name="q" placeholder="Cerca nella directory…" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn" type="submit">Cerca</button>
      </form>
    </div>
  </header>

  <main>
    <div class="container">
      <?php if (!empty($flash['success'])): ?>
        <div class="alert success"><?= e($flash['success']) ?></div>
      <?php endif; ?>
      <?php if (!empty($flash['error'])): ?>
        <div class="alert error"><?= e($flash['error']) ?></div>
      <?php endif; ?>
      <?= $content ?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <a href="<?= e(url('/suggest')) ?>">Suggerisci un sito</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/admin')) ?>">Area riservata</a>
      <p>&copy; <?= date('Y') ?> <?= e($siteName) ?> — directory web in stile Open Directory.</p>
    </div>
  </footer>
</body>
</html>
