<?php
$site  = $CONFIG['site']['name'] ?? 'il sito';
$email = $CONFIG['site']['email'] ?? '';
$owner = $CONFIG['site']['owner'] ?? '';
?>
<div class="panel page-content">
  <h1>Informativa sulla privacy</h1>
  <p class="muted">Ultimo aggiornamento: <?= date('d/m/Y') ?>. Questo è un modello di base:
    personalizzalo con i tuoi dati reali.</p>

  <h3>Titolare del trattamento</h3>
  <p><?= $owner !== '' ? e($owner) : '[Inserisci il tuo nome]' ?>,
     gestore di <strong><?= e($site) ?></strong>.
     <?php if ($email): ?>Contatto: <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>.<?php endif; ?></p>

  <h3>Quali dati raccogliamo</h3>
  <ul>
    <li><strong>Suggerimenti di siti</strong>: se compili il modulo "Suggerisci un sito",
        puoi lasciare facoltativamente la tua email, usata solo per eventuali contatti
        sulla proposta. Non è pubblicata.</li>
    <li><strong>Dati tecnici</strong>: il server può registrare, nei log, indirizzo IP,
        data/ora e pagina visitata, per sicurezza e diagnostica.</li>
    <li><strong>Cookie</strong>: vedi la <a href="<?= e(url('/cookie')) ?>">Cookie Policy</a>.</li>
  </ul>

  <h3>Finalità e base giuridica</h3>
  <p>Trattiamo i dati per far funzionare il sito e gestire i suggerimenti (legittimo
     interesse / tua richiesta) e, solo previo tuo consenso, per la pubblicità.</p>

  <h3>Servizi di terze parti</h3>
  <p>Alcune pagine caricano risorse da terzi:</p>
  <ul>
    <li><strong>Google AdSense</strong> (pubblicità): attivo solo dopo il tuo consenso.</li>
    <li><strong>DuckDuckGo</strong>: icone/favicon dei siti elencati.</li>
    <li><strong>OpenStreetMap</strong>: le mappe si caricano solo se apri "Mostra mappa".</li>
  </ul>
  <p>Questi soggetti hanno proprie informative privacy.</p>

  <h3>Conservazione</h3>
  <p>Le email dei suggerimenti sono conservate finché la proposta è utile, poi eliminate.
     I log seguono i tempi tecnici del provider (Netsons).</p>

  <h3>I tuoi diritti</h3>
  <p>Puoi chiedere accesso, rettifica o cancellazione dei tuoi dati, e opporti al
     trattamento, scrivendo
     <?php if ($email): ?>a <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php else: ?>al titolare<?php endif; ?>.</p>
</div>
