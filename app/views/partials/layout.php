<?php
/** Layout pubblico. Variabili: $content, $title, $CONFIG. */
$siteName = $CONFIG['site']['name'] ?? 'Directory';
$flash = take_flash();
?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? '') !== '' && isset($title) ? $title . ' · ' . $siteName : $siteName) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
  <header class="site-header">
    <div class="container bar">
      <a class="logo" href="<?= e(url('/')) ?>"><?= e($siteName) ?></a>
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
