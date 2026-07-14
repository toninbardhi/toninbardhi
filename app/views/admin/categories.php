<?php /** @var array $categories */ ?>
<div class="page-head">
  <h1 style="margin:0">Categorie</h1>
  <a class="btn" href="<?= e(url('/admin/categories/create')) ?>">+ Nuova categoria</a>
</div>

<?php if (!$categories): ?>
  <p class="muted">Nessuna categoria. Creane una per iniziare.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Nome</th><th>Percorso</th><th>Siti</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($categories as $c): ?>
        <?php $depth = $c['path'] === '' ? 0 : substr_count($c['path'], '/'); ?>
        <tr>
          <td><?= str_repeat('&nbsp;&nbsp;&nbsp;', $depth) ?>
            <?= $depth ? '↳ ' : '' ?><strong><?= e($c['name']) ?></strong></td>
          <td><a href="<?= e(url($c['path'])) ?>" target="_blank">/<?= e($c['path']) ?></a></td>
          <td><?= Category::linkCount((int)$c['id']) ?></td>
          <td class="actions-cell">
            <a class="btn small secondary" href="<?= e(url('/admin/links')) ?>?category_id=<?= (int)$c['id'] ?>">Link</a>
            <a class="btn small secondary" href="<?= e(url('/admin/categories/' . $c['id'] . '/edit')) ?>">Modifica</a>
            <form method="post" action="<?= e(url('/admin/categories/' . $c['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Eliminare «<?= e($c['name']) ?>» e tutto il suo contenuto?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Elimina</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
