<?php
/** @var array|null $post @var array $categories */
$editing = $post !== null;
$action = $editing ? url('/admin/posts/' . $post['id'] . '/edit') : url('/admin/posts/create');
$status = $post['status'] ?? 'draft';
$postCat = $post['category_id'] ?? '';
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
      <label for="cover_image">Immagine di copertina (URL)</label>
      <input type="url" name="cover_image" id="cover_image" maxlength="500" placeholder="https://…/foto.jpg"
             value="<?= e($post['cover_image'] ?? '') ?>">
      <div class="hint">Mostrata in cima all'articolo, nell'elenco del blog e come anteprima sui social.</div>
      <?php if (!empty($post['cover_image'])): ?>
        <img src="<?= e($post['cover_image']) ?>" alt="" style="margin-top:8px; max-height:120px; border-radius:8px; border:1px solid var(--border)">
      <?php endif; ?>
    </div>
    <div class="field">
      <label for="tags">Tag (separati da virgola)</label>
      <input type="text" name="tags" id="tags" maxlength="255" placeholder="es. novità, guida, crypto"
             value="<?= e($post['tags'] ?? '') ?>">
      <div class="hint">Massimo 10. Diventano filtri cliccabili nel blog (es. <code>/blog?tag=guida</code>).</div>
    </div>
    <div class="field">
      <label for="category_id">Categoria collegata (facoltativa)</label>
      <select name="category_id" id="category_id">
        <option value="">— Nessuna —</option>
        <?= category_options($categories, $postCat) ?>
      </select>
      <div class="hint">Collega l'articolo a una categoria della directory: apparirà un link reciproco.</div>
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
