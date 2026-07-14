<?php /** @var array $stats */ ?>
<div class="stat-grid">
  <div class="stat"><div class="n"><?= (int)$stats['categories'] ?></div><div class="l">Categorie</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['links'] ?></div><div class="l">Siti pubblicati</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['pending'] ?></div><div class="l">In attesa</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['users'] ?></div><div class="l">Utenti</div></div>
</div>

<div class="panel" style="margin-top:22px">
  <h3 style="margin-top:0">Azioni rapide</h3>
  <p>
    <a class="btn" href="<?= e(url('/admin/categories/create')) ?>">+ Nuova categoria</a>
    <a class="btn" href="<?= e(url('/admin/links/create')) ?>">+ Nuovo link</a>
    <a class="btn secondary" href="<?= e(url('/admin/suggestions')) ?>">Rivedi suggerimenti<?= $stats['pending'] ? ' (' . (int)$stats['pending'] . ')' : '' ?></a>
  </p>
</div>
