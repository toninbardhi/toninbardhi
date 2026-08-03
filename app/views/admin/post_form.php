<?php
/** @var array|null $post */
$editing = $post !== null;
$action = $editing ? url('/admin/posts/' . $post['id'] . '/edit') : url('/admin/posts/create');
$status = $post['status'] ?? 'draft';
?>
<h1><?= $editing ? 'Modifica articolo' : 'Nuovo articolo' ?></h1>

<div class="panel" style="max-width:760px">
  <form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="title">Titolo *</label>
      <input type="text" name="title" id="title" required maxlength="200"
             value="<?= e($post['title'] ?? input('title')) ?>">
    </div>
    <div class="field">
      <label for="slug">Slug (URL) — lascia vuoto per generarlo dal titolo</label>
      <input type="text" name="slug" id="slug" maxlength="220" placeholder="es. benvenuto"
             value="<?= e($post['slug'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="excerpt">Riassunto breve</label>
      <textarea name="excerpt" id="excerpt" maxlength="500" style="min-height:60px"><?= e($post['excerpt'] ?? '') ?></textarea>
      <div class="hint">Mostrato nell'elenco del blog e come descrizione SEO.</div>
    </div>
    <div class="field">
      <label for="body">Testo dell'articolo</label>
      <textarea name="body" id="body" style="min-height:280px"><?= e($post['body'] ?? '') ?></textarea>
      <div class="hint">Puoi usare l'HTML per la formattazione (paragrafi &lt;p&gt;, grassetto &lt;strong&gt;, link &lt;a&gt;, immagini &lt;img&gt;).</div>
    </div>
    <div class="field">
      <label for="status">Stato</label>
      <select name="status" id="status">
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Bozza (non visibile)</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Pubblicato</option>
      </select>
    </div>
    <div class="field">
      <label for="published_at">Data di pubblicazione (facoltativa)</label>
      <input type="datetime-local" name="published_at" id="published_at"
             value="<?= e(!empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '') ?>">
      <div class="hint">Se vuota e l'articolo è "Pubblicato", viene usata l'ora attuale.</div>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva' : 'Crea articolo' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/posts')) ?>">Annulla</a>
    </div>
  </form>
</div>
