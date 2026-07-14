<?php /** @var array $pending */ ?>
<h1>Suggerimenti in attesa</h1>

<?php if (!$pending): ?>
  <p class="muted">Nessun suggerimento da revisionare. 🎉</p>
<?php else: ?>
  <?php foreach ($pending as $s): ?>
    <div class="panel">
      <div class="page-head" style="margin-bottom:8px">
        <h3 style="margin:0"><?= e($s['title']) ?></h3>
        <span class="badge pending">in attesa</span>
      </div>
      <div class="url" style="color:var(--ok); word-break:break-all">
        <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener nofollow"><?= e($s['url']) ?></a>
      </div>
      <?php if (!empty($s['description'])): ?>
        <p><?= e($s['description']) ?></p>
      <?php endif; ?>
      <p class="muted" style="font-size:.85rem">
        Categoria proposta: <a href="<?= e(url($s['category_path'])) ?>" target="_blank"><?= e($s['category_name']) ?></a>
        <?php if (!empty($s['submitted_by'])): ?>
          · da <?= e($s['submitted_by']) ?>
        <?php endif; ?>
        · <?= e($s['created_at']) ?>
      </p>
      <div class="form-actions">
        <form method="post" action="<?= e(url('/admin/suggestions/' . $s['id'] . '/approve')) ?>">
          <?= csrf_field() ?>
          <button class="btn ok" type="submit">✓ Approva e pubblica</button>
        </form>
        <form method="post" action="<?= e(url('/admin/suggestions/' . $s['id'] . '/reject')) ?>"
              onsubmit="return confirm('Rifiutare ed eliminare questo suggerimento?');">
          <?= csrf_field() ?>
          <button class="btn danger" type="submit">✗ Rifiuta</button>
        </form>
        <a class="btn secondary" href="<?= e(url('/admin/links/' . $s['id'] . '/edit')) ?>">Modifica prima</a>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
