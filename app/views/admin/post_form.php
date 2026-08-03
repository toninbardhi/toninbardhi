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

    <?php
      $isSponsored = !empty($post['is_sponsored']);
      $spUntil = !empty($post['sponsored_until']) ? substr((string)$post['sponsored_until'], 0, 10) : date('Y-m-d', strtotime('+1 year'));
    ?>
    <fieldset class="featured-box">
      <legend>💰 Articolo sponsorizzato (a pagamento)</legend>
      <label class="check">
        <input type="checkbox" name="is_sponsored" id="is_sponsored" value="1" <?= $isSponsored ? 'checked' : '' ?>>
        Questo è un articolo pagato da uno sponsor
      </label>
      <div class="featured-fields" id="sponsor-fields" style="<?= $isSponsored ? '' : 'display:none' ?>">
        <div class="field">
          <label for="sponsor_name">Nome dello sponsor</label>
          <input type="text" name="sponsor_name" id="sponsor_name" maxlength="160"
                 placeholder="es. Azienda S.r.l." value="<?= e($post['sponsor_name'] ?? '') ?>">
          <div class="hint">Mostrato al pubblico: «Contenuto sponsorizzato da …».</div>
        </div>
        <div class="field">
          <label for="sponsor_url">Link dello sponsor (facoltativo)</label>
          <input type="url" name="sponsor_url" id="sponsor_url" maxlength="500"
                 placeholder="https://sito-sponsor.it" value="<?= e($post['sponsor_url'] ?? '') ?>">
          <div class="hint">Il nome dello sponsor diventa un link (con <code>rel="sponsored"</code> per rispettare le regole di Google).</div>
        </div>
        <div class="field">
          <label for="sponsor_price">Prezzo pagato (€) — solo per te</label>
          <input type="text" name="sponsor_price" id="sponsor_price" inputmode="decimal"
                 placeholder="es. 30" value="<?= e(isset($post['sponsor_price']) && $post['sponsor_price'] !== null ? rtrim(rtrim((string)$post['sponsor_price'], '0'), '.') : '') ?>">
          <div class="hint">Registra quanto ha pagato lo sponsor. Non è pubblico.</div>
        </div>
        <div class="field" style="margin-bottom:0">
          <label for="sponsored_until">Scadenza sponsorizzazione</label>
          <input type="date" name="sponsored_until" id="sponsored_until" value="<?= e($spUntil) ?>">
          <div class="hint">Per il tuo controllo: quando finisce il periodo pagato.</div>
        </div>
      </div>
    </fieldset>
    <script>
      (function(){
        var cb = document.getElementById('is_sponsored');
        var box = document.getElementById('sponsor-fields');
        if (cb && box) cb.addEventListener('change', function(){ box.style.display = cb.checked ? '' : 'none'; });
      })();
    </script>
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
