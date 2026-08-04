<?php
/** @var array|null $item @var array $categories */
$editing = $item !== null;
$action  = $editing ? url('/admin/spotlight/' . $item['id'] . '/edit') : url('/admin/spotlight/create');
$status  = $item['status'] ?? 'draft';
$cat     = $item['category_id'] ?? '';
$aiOn    = $item === null ? true : !empty($item['ai_generated']);
$paid    = !empty($item['is_paid']);
$aiButton = !empty($CONFIG['describe']['ai_enabled']); // pulsante solo se l'AI è configurata
?>
<h1><?= $editing ? 'Modifica scheda' : 'Nuova scheda Spotlight' ?></h1>

<div class="panel" style="max-width:820px">
  <form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>

    <div class="field">
      <label for="subject_name">Prodotto / sito *</label>
      <input type="text" name="subject_name" id="subject_name" required maxlength="200"
             placeholder="es. Nome del prodotto o del sito"
             value="<?= e($item['subject_name'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="subject_url">URL del prodotto/sito</label>
      <input type="url" name="subject_url" id="subject_url" maxlength="500" placeholder="https://…"
             value="<?= e($item['subject_url'] ?? '') ?>">
    </div>

    <?php if ($aiButton): ?>
      <div class="ai-gen">
        <button type="button" class="btn" id="ai-generate">✨ Genera bozza con l'AI</button>
        <span class="muted" id="ai-status"></span>
        <div class="hint">Legge il sito e compila le tre sezioni. Puoi poi modificarle a mano prima di pubblicare.</div>
      </div>
    <?php else: ?>
      <div class="ai-gen">
        <div class="hint" style="margin:0">✍️ Scrivi le tre sezioni qui sotto. Puoi prepararle con l'AI che
          preferisci (ChatGPT, Claude…) e <strong>incollarle</strong> nei campi. Ricorda di tenere attivo
          l'avviso «assistito da AI» qui sotto.</div>
      </div>
    <?php endif; ?>

    <div class="field">
      <label for="title">Titolo della scheda *</label>
      <input type="text" name="title" id="title" required maxlength="200"
             placeholder="es. Spotlight: Nome del sito"
             value="<?= e($item['title'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="slug">Slug (URL) — vuoto = generato dal titolo</label>
      <input type="text" name="slug" id="slug" maxlength="220" value="<?= e($item['slug'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="category_id">Categoria (stesse della directory)</label>
      <select name="category_id" id="category_id">
        <option value="">— Nessuna —</option>
        <?= category_options($categories, $cat) ?>
      </select>
    </div>

    <div class="field">
      <label for="presentation">📋 Presentazione</label>
      <textarea name="presentation" id="presentation" style="min-height:120px"><?= e($item['presentation'] ?? '') ?></textarea>
      <div class="hint">Cos'è il prodotto/sito. HTML consentito.</div>
    </div>
    <div class="field">
      <label for="web_view">🌐 Come lo vede il web</label>
      <textarea name="web_view" id="web_view" style="min-height:120px"><?= e($item['web_view'] ?? '') ?></textarea>
      <div class="hint">Come viene percepito/presentato sul web in generale.</div>
    </div>
    <div class="field">
      <label for="ai_view">🤖 Come lo vede l'AI</label>
      <textarea name="ai_view" id="ai_view" style="min-height:120px"><?= e($item['ai_view'] ?? '') ?></textarea>
      <div class="hint">Descrizione sintetica dal punto di vista di un'intelligenza artificiale.</div>
    </div>

    <div class="field">
      <label for="cover_image">Immagine di copertina (URL)</label>
      <input type="url" name="cover_image" id="cover_image" maxlength="500" placeholder="https://…/foto.jpg"
             value="<?= e($item['cover_image'] ?? '') ?>">
    </div>

    <label class="check" style="margin-bottom:14px; display:flex; align-items:center; gap:8px">
      <input type="checkbox" name="ai_generated" id="ai_generated" value="1" <?= $aiOn ? 'checked' : '' ?> style="width:auto">
      Mostra l'avviso «contenuto assistito da AI» (consigliato, obbligatorio per legge se usi l'AI)
    </label>

    <fieldset class="featured-box">
      <legend>💰 Inclusione a pagamento (facoltativa)</legend>
      <label class="check">
        <input type="checkbox" name="is_paid" id="is_paid" value="1" <?= $paid ? 'checked' : '' ?>>
        Questa scheda è stata pagata per l'inclusione
      </label>
      <div class="featured-fields" id="paid-fields" style="<?= $paid ? '' : 'display:none' ?>">
        <div class="field" style="margin-bottom:0">
          <label for="sponsor_name">Nome di chi ha pagato (mostrato come «Inclusione sponsorizzata da …»)</label>
          <input type="text" name="sponsor_name" id="sponsor_name" maxlength="160"
                 value="<?= e($item['sponsor_name'] ?? '') ?>">
        </div>
        <p class="hint" style="margin-top:8px">⚖️ Il pagamento è solo per l'inclusione: la presentazione resta redazionale e indipendente.</p>
      </div>
    </fieldset>

    <div class="field">
      <label for="status">Stato</label>
      <select name="status" id="status">
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Bozza (non visibile)</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Pubblicata</option>
      </select>
    </div>
    <div class="field">
      <label for="published_at">Data di pubblicazione (facoltativa)</label>
      <input type="datetime-local" name="published_at" id="published_at"
             value="<?= e(!empty($item['published_at']) ? date('Y-m-d\TH:i', strtotime($item['published_at'])) : '') ?>">
    </div>

    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva' : 'Crea scheda' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/spotlight')) ?>">Annulla</a>
    </div>
  </form>
</div>

<script>
(function () {
  // Mostra/nasconde i campi "a pagamento".
  var paid = document.getElementById('is_paid');
  var pf = document.getElementById('paid-fields');
  if (paid && pf) paid.addEventListener('change', function () { pf.style.display = paid.checked ? '' : 'none'; });

  // Generazione bozza con AI.
  var btn = document.getElementById('ai-generate');
  var st  = document.getElementById('ai-status');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var name = document.getElementById('subject_name').value.trim();
    var url  = document.getElementById('subject_url').value.trim();
    if (!name && !url) { st.textContent = 'Inserisci prima il nome o l\'URL.'; return; }
    btn.disabled = true; st.textContent = 'Generazione in corso…';
    var body = new URLSearchParams({ subject_name: name, subject_url: url });
    fetch(<?= json_encode(url('/admin/spotlight/generate')) ?>, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      btn.disabled = false;
      if (!d.ok) { st.textContent = d.error || 'Errore.'; return; }
      if (d.subject_name && !document.getElementById('subject_name').value) document.getElementById('subject_name').value = d.subject_name;
      if (!document.getElementById('title').value) {
        document.getElementById('title').value = 'Spotlight: ' + (d.subject_name || name || url);
      }
      if (d.presentation) document.getElementById('presentation').value = d.presentation;
      if (d.web_view)     document.getElementById('web_view').value = d.web_view;
      if (d.ai_view)      document.getElementById('ai_view').value = d.ai_view;
      st.textContent = '✓ Bozza generata. Controlla e modifica prima di pubblicare.';
    })
    .catch(function () { btn.disabled = false; st.textContent = 'Errore di rete.'; });
  });
})();
</script>
