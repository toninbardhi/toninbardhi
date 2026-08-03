<?php
/** Layout pubblico. Variabili: $content, $title, $CONFIG, $metaDescription. */
$siteName = setting('site_name', $CONFIG['site']['name'] ?? 'Directory');
$flash = take_flash();
$pageTitle = (isset($title) && $title !== null && $title !== '')
    ? $title . ' · ' . $siteName
    : $siteName;
$desc = $metaDescription ?? 'Directory web organizzata in categorie: sfoglia e scopri i migliori siti selezionati.';
$ogImage = $metaImage ?? null;
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$canonical = abs_url($reqPath);
$ads = $CONFIG['adsense'] ?? [];
$adsOn = !empty($ads['client']);
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
  <?php if ($ogImage): ?>
    <meta property="og:image" content="<?= e(preg_match('#^https?://#i', $ogImage) ? $ogImage : abs_url($ogImage)) ?>">
    <meta name="twitter:card" content="summary_large_image">
  <?php else: ?>
    <meta name="twitter:card" content="summary">
  <?php endif; ?>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <?php /* AdSense viene caricato solo dopo il consenso (vedi banner cookie). */ ?>
</head>
<body>
  <header class="site-header">
    <div class="container bar">
      <a class="logo" href="<?= e(url('/')) ?>" aria-label="<?= e($siteName) ?>">
        <img src="<?= e(url('assets/logo.svg')) ?>" alt="<?= e($siteName) ?>" class="logo-img">
      </a>
      <a class="nav-link" href="<?= e(url('/blog')) ?>">Blog</a>
      <form class="search" action="<?= e(url('/search')) ?>" method="get" role="search">
        <input type="search" name="q" placeholder="Cerca nella directory…" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn" type="submit">Cerca</button>
      </form>
    </div>
  </header>

  <?php $showSidebar = empty($hideSidebar); ?>
  <main>
    <div class="container <?= $showSidebar ? 'layout-grid' : '' ?>">
      <div class="content-col">
        <?php if (!empty($flash['success'])): ?>
          <div class="alert success"><?= e($flash['success']) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['error'])): ?>
          <div class="alert error"><?= e($flash['error']) ?></div>
        <?php endif; ?>
        <?= $content ?>
      </div>
      <?php if ($showSidebar) { require __DIR__ . '/sidebar.php'; } ?>
    </div>

    <?php if ($adsOn): ?>
      <div class="container">
        <div class="ad-banner">
          <ins class="adsbygoogle"
               style="display:block"
               data-ad-client="<?= e($ads['client']) ?>"
               <?php if (!empty($ads['slot'])): ?>data-ad-slot="<?= e($ads['slot']) ?>"<?php endif; ?>
               data-ad-format="auto"
               data-full-width-responsive="true"></ins>
          <?php /* Il push avviene solo dopo il consenso (script in fondo). */ ?>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container">
      <a href="<?= e(url('/blog')) ?>">Blog</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/suggest')) ?>">Suggerisci un sito</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/privacy')) ?>">Privacy</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/cookie')) ?>">Cookie</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/contatti')) ?>">Contatti</a>
      &nbsp;·&nbsp;
      <a href="<?= e(url('/admin')) ?>">Area riservata</a>
      <p>&copy; <?= date('Y') ?> <?= e($siteName) ?> — <?= e(setting('footer_text', 'directory web in stile Open Directory.')) ?></p>
    </div>
  </footer>

  <?php if ($adsOn): ?>
    <div id="cookie-banner" class="cookie-banner" role="dialog" aria-live="polite" style="display:none">
      <div class="cc-text">
        Usiamo cookie tecnici e, con il tuo consenso, cookie di terze parti
        (pubblicità Google) per sostenere il sito.
        <a href="<?= e(url('/cookie')) ?>">Dettagli</a>.
      </div>
      <div class="cc-actions">
        <button type="button" class="btn secondary small cc-deny">Rifiuta</button>
        <button type="button" class="btn small cc-accept">Accetta</button>
      </div>
    </div>
    <script>
    (function () {
      var CLIENT = <?= json_encode($ads['client']) ?>;
      function consent() { var m = document.cookie.match(/(?:^|; )wd_consent=([^;]+)/); return m ? m[1] : null; }
      function setConsent(v) { document.cookie = 'wd_consent=' + v + '; max-age=15552000; path=/; SameSite=Lax'; }
      function loadAds() {
        if (window.__adsLoaded || !CLIENT) return; window.__adsLoaded = true;
        var s = document.createElement('script');
        s.async = true; s.crossOrigin = 'anonymous';
        s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(CLIENT);
        document.head.appendChild(s);
        document.querySelectorAll('ins.adsbygoogle').forEach(function () {
          (window.adsbygoogle = window.adsbygoogle || []).push({});
        });
      }
      var c = consent();
      if (c === 'accept') { loadAds(); return; }
      if (c === 'deny') { return; }
      var b = document.getElementById('cookie-banner');
      if (!b) return;
      b.style.display = 'block';
      b.querySelector('.cc-accept').addEventListener('click', function () {
        setConsent('accept'); b.style.display = 'none'; loadAds();
      });
      b.querySelector('.cc-deny').addEventListener('click', function () {
        setConsent('deny'); b.style.display = 'none';
      });
    })();
    </script>
  <?php endif; ?>

  <script>
  // Mappa OpenStreetMap: carica l'iframe solo al primo clic (leggera).
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.map-toggle');
    if (!btn) return;
    var holder = btn.parentNode.querySelector('.map-holder');
    if (!holder) return;
    if (holder.dataset.loaded) {
      var open = holder.style.display !== 'none';
      holder.style.display = open ? 'none' : 'block';
      btn.textContent = open ? '📍 Mostra mappa' : '📍 Nascondi mappa';
      return;
    }
    var f = document.createElement('iframe');
    f.src = btn.dataset.embed;
    f.loading = 'lazy';
    f.title = 'Mappa OpenStreetMap';
    holder.appendChild(f);
    holder.dataset.loaded = '1';
    holder.style.display = 'block';
    btn.textContent = '📍 Nascondi mappa';
  });
  </script>
</body>
</html>
