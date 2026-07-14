<?php /** @var array $categories @var int|null $categoryId @var array $links */ ?>
<div class="page-head">
  <h1 style="margin:0">Link</h1>
  <a class="btn" href="<?= e(url('/admin/links/create')) ?>">+ Nuovo link</a>
</div>

<div class="panel">
  <form method="get" action="<?= e(url('/admin/links')) ?>" style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap">
    <div class="field" style="margin:0; flex:1; min-width:220px">
      <label for="category_id">Mostra i link della categoria</label>
      <select name="category_id" id="category_id" onchange="this.form.submit()">
        <option value="">— seleziona una categoria —</option>
        <?= category_options($categories, $categoryId) ?>
      </select>
    </div>
    <button class="btn secondary" type="submit">Filtra</button>
  </form>
</div>

<?php if ($categoryId === null): ?>
  <p class="muted">Seleziona una categoria per vederne i link.</p>
<?php elseif (!$links): ?>
  <p class="muted">Nessun link in questa categoria.
    <a href="<?= e(url('/admin/links/create')) ?>">Aggiungine uno</a>.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Titolo</th><th>URL</th><th>Stato</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($links as $l): ?>
        <tr>
          <td><strong><?= e($l['title']) ?></strong></td>
          <td><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['url']) ?></a></td>
          <td><span class="badge <?= e($l['status']) ?>"><?= $l['status'] === 'approved' ? 'pubblicato' : 'in attesa' ?></span></td>
          <td class="actions-cell">
            <a class="btn small secondary" href="<?= e(url('/admin/links/' . $l['id'] . '/edit')) ?>">Modifica</a>
            <form method="post" action="<?= e(url('/admin/links/' . $l['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Eliminare questo link?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Elimina</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
