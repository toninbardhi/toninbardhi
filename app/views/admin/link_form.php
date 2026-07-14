<?php
/** @var array|null $link @var array $categories */
$editing = $link !== null;
$action = $editing
    ? url('/admin/links/' . $link['id'] . '/edit')
    : url('/admin/links/create');
$selCat = $link['category_id'] ?? input('category_id');
$status = $link['status'] ?? 'approved';
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
    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva modifiche' : 'Crea link' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/links')) ?><?= $selCat ? '?category_id=' . (int)$selCat : '' ?>">Annulla</a>
    </div>
  </form>
</div>
