<?php
$site    = setting('site_name', $CONFIG['site']['name'] ?? 'il sito');
$email   = setting('contact_email', $CONFIG['site']['email'] ?? '');
$spInfo  = setting('sponsor_info', '');
$spPrice = trim((string) setting('sponsor_price', ''));
?>
<div class="panel page-content">
  <h1>Contatti</h1>
  <p>Per informazioni, segnalazioni o richieste su <strong><?= e($site) ?></strong>:</p>
  <?php if ($email): ?>
    <p style="font-size:1.1rem">✉ <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
  <?php else: ?>
    <p class="muted">Imposta l'email di contatto in <code>app/config.php</code> (site.email).</p>
  <?php endif; ?>

  <p>Vuoi proporre un sito da inserire nella directory?
     Usa la pagina <a href="<?= e(url('/suggest')) ?>">Suggerisci un sito</a>.</p>

  <?php if ($spInfo !== '' || $spPrice !== ''): ?>
    <h3>📣 Articoli sponsorizzati</h3>
    <?php if ($spInfo !== ''): ?><p><?= e($spInfo) ?></p><?php endif; ?>
    <?php if ($spPrice !== ''): ?>
      <p>Costo: <strong><?= e($spPrice) ?> € ad articolo.</strong>
         <?php if ($email): ?>Scrivi a <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> per pubblicare.<?php endif; ?></p>
    <?php endif; ?>
  <?php endif; ?>
</div>
