<?php $site = setting('site_name', $CONFIG['site']['name'] ?? 'il sito'); ?>
<div class="panel page-content">
  <h1>Cookie Policy</h1>
  <p class="muted">Come <strong><?= e($site) ?></strong> usa i cookie.</p>

  <h3>Cookie tecnici (senza consenso)</h3>
  <p>Necessari al funzionamento del sito, non richiedono consenso:</p>
  <ul>
    <li><code>PHPSESSID</code> — mantiene la sessione (es. accesso all'area riservata).</li>
    <li><code>wd_consent</code> — ricorda la tua scelta sui cookie.</li>
  </ul>

  <h3>Cookie di terze parti (solo con consenso)</h3>
  <p>La pubblicità di <strong>Google AdSense</strong> può impostare cookie di
     profilazione. Vengono caricati <strong>solo dopo che premi "Accetta"</strong> nel
     banner. Se rifiuti, gli annunci non vengono caricati.</p>

  <h3>Come cambiare idea</h3>
  <p>Puoi revocare o modificare il consenso in qualsiasi momento cancellando i cookie del
     sito dal tuo browser: alla visita successiva ricomparirà il banner.</p>

  <h3>Altre risorse esterne</h3>
  <p>Il sito carica anche icone dei siti (DuckDuckGo) e, solo se apri una mappa,
     tessere di OpenStreetMap. Non impostano cookie di profilazione nostri.</p>

  <p style="margin-top:16px"><a href="<?= e(url('/privacy')) ?>">↩ Informativa sulla privacy</a></p>
</div>
