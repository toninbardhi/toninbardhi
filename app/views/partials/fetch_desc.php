<?php /** Script condiviso: pulsante "Recupera dal sito" per titolo/descrizione. */ ?>
<script>
(function () {
  var btn = document.getElementById('fetch-desc');
  if (!btn) return;
  var msg = document.getElementById('fetch-msg');
  var endpoint = <?= json_encode(url('/fetch-description')) ?>;

  btn.addEventListener('click', function () {
    var url = (document.getElementById('url') || {}).value || '';
    if (!url) { msg.textContent = 'Inserisci prima l\'URL.'; return; }
    btn.disabled = true;
    var original = btn.textContent;
    btn.textContent = 'Recupero…';
    msg.textContent = '';

    fetch(endpoint + '?url=' + encodeURIComponent(url), { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.ok) { msg.textContent = d.error || 'Impossibile recuperare i dati.'; return; }
        var t = document.getElementById('title');
        var desc = document.getElementById('description');
        if (t && d.title && !t.value) t.value = d.title;
        if (desc && d.description && !desc.value) desc.value = d.description;
        msg.textContent = d.description
          ? (d.used_ai ? 'Descrizione generata con AI ✓' : 'Recuperato dal sito ✓')
          : 'Il sito non fornisce una descrizione: scrivila a mano.';
      })
      .catch(function () { msg.textContent = 'Errore di rete durante il recupero.'; })
      .finally(function () { btn.disabled = false; btn.textContent = original; });
  });
})();
</script>
