<?php
/** @var array|null $link @var array $categories */
$editing = $link !== null;
$action = $editing
    ? url('/admin/links/' . $link['id'] . '/edit')
    : url('/admin/links/create');
$selCat = $link['category_id'] ?? input('category_id');
$status = $link['status'] ?? 'approved';
$isFeatured = !empty($link['featured']);
$featPos    = (int) ($link['featured_position'] ?? 1) ?: 1;
$featUntil  = $link['featured_until'] ?? date('Y-m-d', strtotime('+1 year'));
?>
<h1><?= $editing ? 'Modifica link' : 'Nuovo link' ?></h1>

<div class="panel" style="max-width:600px">
  <form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="category_id">Categoria *</label>
      <select name="category_id" id="category_id" required>
        <option value="">— seleziona —</option>
        <?= category_options($categories, $selCat) ?>
      </select>
    </div>
    <div class="field">
      <label for="title">Titolo *</label>
      <input type="text" name="title" id="title" required maxlength="200"
             value="<?= e($link['title'] ?? input('title')) ?>">
    </div>
    <div class="field">
      <label for="url">URL *</label>
      <input type="url" name="url" id="url" required placeholder="https://esempio.it"
             value="<?= e($link['url'] ?? input('url')) ?>">
    </div>
    <div class="field">
      <label for="description">Descrizione</label>
      <textarea name="description" id="description"><?= e($link['description'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label for="status">Stato</label>
      <select name="status" id="status">
        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Pubblicato</option>
        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>In attesa</option>
      </select>
    </div>

    <fieldset class="featured-box">
      <legend>★ In evidenza (sponsorizzato)</legend>
      <label class="check">
        <input type="checkbox" name="featured" id="featured" value="1" <?= $isFeatured ? 'checked' : '' ?>>
        Mostra questo link tra i primi della categoria (a pagamento)
      </label>
      <div class="featured-fields" id="featured-fields">
        <div class="field" style="margin-bottom:12px">
          <label for="featured_position">Posizione (1 = primo, max <?= Link::FEATURED_SLOTS ?>)</label>
          <select name="featured_position" id="featured_position">
            <?php for ($i = 1; $i <= Link::FEATURED_SLOTS; $i++): ?>
              <option value="<?= $i ?>" <?= $featPos === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="field" style="margin-bottom:0">
          <label for="featured_until">Scadenza evidenza</label>
          <input type="date" name="featured_until" id="featured_until" value="<?= e($featUntil) ?>">
          <div class="hint">Precompilata a +1 anno. Alla scadenza il link torna normale automaticamente.</div>
        </div>
      </div>
    </fieldset>

    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva modifiche' : 'Crea link' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/links')) ?><?= $selCat ? '?category_id=' . (int)$selCat : '' ?>">Annulla</a>
    </div>
  </form>
</div>

<script>
  (function () {
    var cb = document.getElementById('featured');
    var box = document.getElementById('featured-fields');
    function sync() { box.style.display = cb.checked ? 'block' : 'none'; }
    cb.addEventListener('change', sync);
    sync();
  })();
</script>
