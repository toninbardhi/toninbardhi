<?php /** @var array $posts */ ?>
<div class="page-head">
  <h1 style="margin:0">Blog</h1>
  <a class="btn" href="<?= e(url('/admin/posts/create')) ?>">+ Nuovo articolo</a>
</div>

<?php if (!$posts): ?>
  <p class="muted">Nessun articolo. Scrivine uno per iniziare.</p>
<?php else: ?>
  <?php
    $totRevenue = 0.0;
    foreach ($posts as $pp) { if (!empty($pp['is_sponsored'])) { $totRevenue += (float) ($pp['sponsor_price'] ?? 0); } }
  ?>
  <table class="data">
    <thead><tr><th>Titolo</th><th>Stato</th><th>Sponsor</th><th>Data</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($posts as $p): ?>
        <?php
          $spUntil = !empty($p['sponsored_until']) ? substr((string)$p['sponsored_until'], 0, 10) : null;
          $spGiorni = $spUntil ? (int) ceil((strtotime($spUntil) - time()) / 86400) : null;
        ?>
        <tr>
          <td><strong><?= e($p['title']) ?></strong></td>
          <td><span class="badge <?= $p['status'] === 'published' ? 'approved' : 'pending' ?>">
            <?= $p['status'] === 'published' ? 'pubblicato' : 'bozza' ?></span></td>
          <td>
            <?php if (!empty($p['is_sponsored'])): ?>
              <span class="badge featured">Sponsor</span>
              <?php if (!empty($p['sponsor_name'])): ?><br><span class="muted" style="font-size:.8rem"><?= e($p['sponsor_name']) ?></span><?php endif; ?>
              <?php if (isset($p['sponsor_price']) && $p['sponsor_price'] !== null): ?>
                <br><span class="muted" style="font-size:.8rem"><?= e(money((float)$p['sponsor_price'])) ?> €</span>
              <?php endif; ?>
              <?php if ($spUntil): ?>
                <br><span class="muted" style="font-size:.76rem">scad. <?= e($spUntil) ?><?= $spGiorni !== null && $spGiorni < 0 ? ' (scaduto)' : '' ?></span>
              <?php endif; ?>
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
          </td>
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
  <?php if ($totRevenue > 0): ?>
    <p class="muted" style="margin-top:12px">
      💰 Incasso totale articoli sponsorizzati:
      <strong><?= e(money($totRevenue)) ?> €</strong>
    </p>
  <?php endif; ?>
<?php endif; ?>
