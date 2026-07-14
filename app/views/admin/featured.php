<?php /** @var array $featured */ ?>
<h1>★ Link in evidenza</h1>
<p class="muted">
  Gli sponsor attivi appaiono tra i primi <?= Link::FEATURED_SLOTS ?> risultati della loro categoria,
  con il badge <span class="badge featured">Sponsorizzato</span>.
  Per mettere un link in evidenza, aprilo in <a href="<?= e(url('/admin/links')) ?>">Link</a> e attiva l'opzione "In evidenza".
</p>

<?php if (!$featured): ?>
  <p class="muted">Nessun link in evidenza al momento.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Pos.</th><th>Titolo</th><th>Categoria</th><th>Scadenza</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach ($featured as $l): ?>
        <?php
          $scad = $l['featured_until'] ?? null;
          $giorni = $scad ? (int) ceil((strtotime($scad) - time()) / 86400) : null;
        ?>
        <tr>
          <td><strong><?= (int) $l['featured_position'] ?></strong></td>
          <td><?= e($l['title']) ?><br><span class="url"><?= e($l['url']) ?></span></td>
          <td><a href="<?= e(url($l['category_path'])) ?>" target="_blank"><?= e($l['category_name']) ?></a></td>
          <td>
            <?php if ($scad): ?>
              <?= e($scad) ?>
              <?php if ($giorni !== null): ?>
                <br><span class="muted" style="font-size:.78rem">
                  <?= $giorni >= 0 ? "tra $giorni giorni" : 'scaduto' ?>
                </span>
              <?php endif; ?>
            <?php else: ?>
              <span class="muted">senza scadenza</span>
            <?php endif; ?>
          </td>
          <td class="actions-cell">
            <a class="btn small secondary" href="<?= e(url('/admin/links/' . $l['id'] . '/edit')) ?>">Modifica</a>
            <form method="post" action="<?= e(url('/admin/featured/' . $l['id'] . '/remove')) ?>"
                  onsubmit="return confirm('Togliere l\'evidenza da questo link?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Togli evidenza</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
