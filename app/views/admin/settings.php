<?php /** @var array $s */
$val = fn(string $k, string $d = '') => e($s[$k] ?? $d);
?>
<h1>Impostazioni del sito</h1>
<p class="muted">Personalizza i testi del sito senza toccare il codice.</p>

<div class="panel" style="max-width:640px">
  <form method="post" action="<?= e(url('/admin/settings')) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="site_name">Nome del sito</label>
      <input type="text" name="site_name" id="site_name" value="<?= $val('site_name', 'webdirectory.link') ?>">
      <div class="hint">Mostrato nel titolo delle schede e nel footer.</div>
    </div>
    <div class="field">
      <label for="home_title">Titolo in home</label>
      <input type="text" name="home_title" id="home_title" value="<?= $val('home_title') ?>">
    </div>
    <div class="field">
      <label for="home_subtitle">Sottotitolo in home</label>
      <input type="text" name="home_subtitle" id="home_subtitle" value="<?= $val('home_subtitle') ?>">
    </div>
    <div class="field">
      <label for="footer_text">Testo del footer</label>
      <input type="text" name="footer_text" id="footer_text" value="<?= $val('footer_text') ?>">
    </div>
    <div class="field">
      <label for="contact_email">Email di contatto</label>
      <input type="email" name="contact_email" id="contact_email" value="<?= $val('contact_email') ?>">
      <div class="hint">Usata nelle pagine Contatti e Privacy.</div>
    </div>
    <div class="field">
      <label for="owner">Titolare (per la privacy)</label>
      <input type="text" name="owner" id="owner" value="<?= $val('owner') ?>">
      <div class="hint">Il tuo nome, mostrato nell'informativa privacy.</div>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">Salva impostazioni</button>
    </div>
  </form>
</div>
