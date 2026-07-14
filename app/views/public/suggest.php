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
    <div class="field">
      <label for="category_id">Categoria *</label>
      <select name="category_id" id="category_id" required>
        <option value="">— seleziona —</option>
        <?= category_options($categories, $old['categoryId'] ?? null) ?>
      </select>
    </div>
    <div class="field">
      <label for="title">Titolo del sito *</label>
      <input type="text" name="title" id="title" maxlength="200" required value="<?= e($old['title'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="url">URL *</label>
      <input type="url" name="url" id="url" placeholder="https://esempio.it" required value="<?= e($old['url'] ?? '') ?>">
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
