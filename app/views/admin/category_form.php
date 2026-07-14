<?php
/** @var array|null $category @var array $categories */
$editing = $category !== null;
$action = $editing
    ? url('/admin/categories/' . $category['id'] . '/edit')
    : url('/admin/categories/create');
?>
<h1><?= $editing ? 'Modifica categoria' : 'Nuova categoria' ?></h1>

<div class="panel" style="max-width:600px">
  <form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="name">Nome *</label>
      <input type="text" name="name" id="name" required maxlength="160"
             value="<?= e($category['name'] ?? input('name')) ?>">
    </div>
    <div class="field">
      <label for="parent_id">Categoria genitore</label>
      <select name="parent_id" id="parent_id">
        <option value="">— nessuna (primo livello) —</option>
        <?= category_options(
              $categories,
              $category['parent_id'] ?? null,
              $editing ? (int) $category['id'] : null
            ) ?>
      </select>
      <div class="hint">Lascia vuoto per una categoria principale.</div>
    </div>
    <div class="field">
      <label for="description">Descrizione</label>
      <textarea name="description" id="description"><?= e($category['description'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva modifiche' : 'Crea categoria' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/categories')) ?>">Annulla</a>
    </div>
  </form>
</div>
