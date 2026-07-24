<?php
/** @var array $categories @var array $old @var array|null $errors */
$old = $old ?? [];
?>
<h1>Suggerisci un sito</h1>
<p class="muted">Proponi un sito da aggiungere alla directory. Verrà pubblicato dopo la revisione di un editor.</p>

<?php if (!empty($errors)): ?>
  <div class="alert error">
    <strong>Correggi questi errori:</strong>
    <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="panel">
  <form action="<?= e(url('/suggest')) ?>" method="post">
    <?= csrf_field() ?>
    <?php $cats = $old['cats'] ?? [null, null, null]; ?>
    <div class="field">
      <label for="category_id_1">Categoria principale *</label>
      <select name="category_id_1" id="category_id_1" required>
        <option value="">— seleziona —</option>
        <?= category_options($categories, $cats[0] ?? null) ?>
      </select>
    </div>
    <div class="field">
      <label for="category_id_2">Seconda categoria (facoltativa)</label>
      <select name="category_id_2" id="category_id_2">
        <option value="">— nessuna —</option>
        <?= category_options($categories, $cats[1] ?? null) ?>
      </select>
    </div>
    <div class="field">
      <label for="category_id_3">Terza categoria (facoltativa)</label>
      <select name="category_id_3" id="category_id_3">
        <option value="">— nessuna —</option>
        <?= category_options($categories, $cats[2] ?? null) ?>
      </select>
      <div class="hint">Puoi proporre il sito in massimo 3 categorie. La promozione a pagamento riguarda invece una sola categoria.</div>
    </div>
    <div class="field">
      <label for="title">Titolo del sito *</label>
      <input type="text" name="title" id="title" maxlength="200" required value="<?= e($old['title'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="url">URL *</label>
      <div style="display:flex; gap:8px">
        <input type="url" name="url" id="url" placeholder="https://esempio.it" required style="flex:1" value="<?= e($old['url'] ?? '') ?>">
        <button type="button" class="btn secondary" id="fetch-desc">↓ Compila dal sito</button>
      </div>
      <div class="hint" id="fetch-msg"></div>
    </div>
    <div class="field">
      <label for="description">Descrizione</label>
      <textarea name="description" id="description" maxlength="1000"><?= e($old['description'] ?? '') ?></textarea>
      <div class="hint">Breve descrizione del contenuto del sito.</div>
    </div>
    <div class="field">
      <label for="email">La tua email (facoltativa)</label>
      <input type="email" name="email" id="email" value="<?= e($old['email'] ?? '') ?>">
      <div class="hint">Non verrà pubblicata: serve solo agli editor per eventuali contatti.</div>
    </div>

    <?php /* Honeypot: campo invisibile agli umani, i bot tendono a compilarlo. */ ?>
    <div class="hp" aria-hidden="true">
      <label for="website">Lascia vuoto questo campo</label>
      <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
    </div>

    <?php if (!empty($captcha)): ?>
      <div class="field">
        <label for="captcha">Verifica antispam: quanto fa <strong><?= e($captcha) ?></strong>?</label>
        <input type="text" name="captcha" id="captcha" inputmode="numeric" required
               autocomplete="off" style="max-width:140px">
      </div>
    <?php endif; ?>

    <div class="form-actions">
      <button class="btn" type="submit">Invia suggerimento</button>
      <a class="btn secondary" href="<?= e(url('/')) ?>">Annulla</a>
    </div>
  </form>
</div>
<?php require APP_PATH . '/views/partials/fetch_desc.php'; ?>
