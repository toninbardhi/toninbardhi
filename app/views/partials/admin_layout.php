<?php
/** Layout area riservata. Variabili: $content, $title, $pending_badge. */
$siteName = setting('site_name', $CONFIG['site']['name'] ?? 'Directory');
$flash = take_flash();
$u = current_user();
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
$nav = function (string $path) use ($uri): string {
    return str_contains($uri, $path) ? 'active' : '';
};
$badge = $pending_badge ?? 0;
?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? 'Admin') . ' · ' . $siteName) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="admin-shell">
  <nav class="admin-nav">
    <a class="brand" href="<?= e(url('/admin')) ?>"><?= e($siteName) ?></a>
    <a class="<?= $uri === url('/admin') || $uri === '/admin' ? 'active' : '' ?>" href="<?= e(url('/admin')) ?>">Pannello</a>
    <a class="<?= $nav('/admin/categories') ?>" href="<?= e(url('/admin/categories')) ?>">Categorie</a>
    <a class="<?= $nav('/admin/links') ?>" href="<?= e(url('/admin/links')) ?>">Link</a>
    <a class="<?= $nav('/admin/featured') ?>" href="<?= e(url('/admin/featured')) ?>">★ In evidenza</a>
    <a class="<?= $nav('/admin/suggestions') ?>" href="<?= e(url('/admin/suggestions')) ?>">
      Suggerimenti
      <?php if ($badge > 0): ?><span class="count-pill"><?= (int)$badge ?></span><?php endif; ?>
    </a>
    <a class="<?= $nav('/admin/posts') ?>" href="<?= e(url('/admin/posts')) ?>">Blog</a>
    <a class="<?= $nav('/admin/spotlight') ?>" href="<?= e(url('/admin/spotlight')) ?>">🔦 Spotlight</a>
    <?php if (is_admin()): ?>
      <a class="<?= $nav('/admin/users') ?>" href="<?= e(url('/admin/users')) ?>">Utenti</a>
      <a class="<?= $nav('/admin/settings') ?>" href="<?= e(url('/admin/settings')) ?>">Impostazioni</a>
    <?php endif; ?>
    <a class="<?= $nav('/admin/profile') ?>" href="<?= e(url('/admin/profile')) ?>">Il mio profilo</a>
    <a href="<?= e(url('/')) ?>">↗ Vedi il sito</a>
    <a href="<?= e(url('/logout')) ?>">Esci</a>
  </nav>

  <div class="admin-main">
    <div class="admin-topbar">
      <strong><?= e($title ?? '') ?></strong>
      <span class="muted">
        <?= e($u['name'] ?? '') ?>
        <span class="badge <?= e($u['role'] ?? 'editor') ?>"><?= e($u['role'] ?? '') ?></span>
      </span>
    </div>

    <?php if (!empty($flash['success'])): ?>
      <div class="alert success"><?= e($flash['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
      <div class="alert error"><?= e($flash['error']) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </div>
</div>
</body>
</html>
