<?php /** @var array $items */ ?>
<div class="page-head">
  <h1 style="margin:0">🔦 Spotlight</h1>
  <a class="btn" href="<?= e(url('/admin/spotlight/create')) ?>">+ Nuova scheda</a>
</div>
<p class="muted">Schede di presentazione di prodotti/siti: cosa sono, come li vede il web e come li vede l'AI.
  Non sono recensioni: nessun voto o giudizio.</p>

<?php if (!$items): ?>
  <p class="muted">Nessuna scheda. Creane una per iniziare.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Titolo</th><th>Soggetto</th><th>Categoria</th><th>Stato</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><strong><?= e($it['title']) ?></strong>
            <?php if (!empty($it['is_paid'])): ?><span class="badge featured">a pagamento</span><?php endif; ?>
          </td>
          <td><?= e($it['subject_name']) ?></td>
          <td class="muted"><?= e($it['category_name'] ?? '—') ?></td>
          <td><span class="badge <?= $it['status'] === 'published' ? 'approved' : 'pending' ?>">
            <?= $it['status'] === 'published' ? 'pubblicata' : 'bozza' ?></span></td>
          <td class="actions-cell">
            <?php if ($it['status'] === 'published'): ?>
              <a class="btn small secondary" href="<?= e(url('/spotlight/' . $it['slug'])) ?>" target="_blank">Vedi</a>
            <?php endif; ?>
            <a class="btn small secondary" href="<?= e(url('/admin/spotlight/' . $it['id'] . '/edit')) ?>">Modifica</a>
            <form method="post" action="<?= e(url('/admin/spotlight/' . $it['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Eliminare la scheda?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Elimina</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
