<?php /** @var array $posts */ ?>
<div class="page-head">
  <h1 style="margin:0">Blog</h1>
  <a class="btn" href="<?= e(url('/admin/posts/create')) ?>">+ Nuovo articolo</a>
</div>

<?php if (!$posts): ?>
  <p class="muted">Nessun articolo. Scrivine uno per iniziare.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Titolo</th><th>Stato</th><th>Data</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($posts as $p): ?>
        <tr>
          <td><strong><?= e($p['title']) ?></strong></td>
          <td><span class="badge <?= $p['status'] === 'published' ? 'approved' : 'pending' ?>">
            <?= $p['status'] === 'published' ? 'pubblicato' : 'bozza' ?></span></td>
          <td class="muted"><?= e(substr((string)($p['published_at'] ?? $p['created_at']), 0, 10)) ?></td>
          <td class="actions-cell">
            <?php if ($p['status'] === 'published'): ?>
              <a class="btn small secondary" href="<?= e(url('/blog/' . $p['slug'])) ?>" target="_blank">Vedi</a>
            <?php endif; ?>
            <a class="btn small secondary" href="<?= e(url('/admin/posts/' . $p['id'] . '/edit')) ?>">Modifica</a>
            <form method="post" action="<?= e(url('/admin/posts/' . $p['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Eliminare l\'articolo?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Elimina</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
