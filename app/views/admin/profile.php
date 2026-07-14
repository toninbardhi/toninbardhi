<?php /** @var array $user */ ?>
<h1>Il mio profilo</h1>
<p class="muted">Aggiorna i tuoi dati. Per salvare qualsiasi modifica inserisci la password attuale.</p>

<div class="panel" style="max-width:520px">
  <form method="post" action="<?= e(url('/admin/profile')) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="name">Nome</label>
      <input type="text" name="name" id="name" required value="<?= e($user['name']) ?>">
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input type="email" name="email" id="email" required value="<?= e($user['email']) ?>">
    </div>

    <hr style="border:none; border-top:1px solid var(--border); margin:20px 0">

    <div class="field">
      <label for="current_password">Password attuale *</label>
      <input type="password" name="current_password" id="current_password" required autocomplete="current-password">
      <div class="hint">Obbligatoria per confermare le modifiche.</div>
    </div>
    <div class="field">
      <label for="new_password">Nuova password</label>
      <input type="password" name="new_password" id="new_password" autocomplete="new-password">
      <div class="hint">Lascia vuoto per non cambiarla. Minimo 6 caratteri.</div>
    </div>
    <div class="field">
      <label for="confirm_password">Conferma nuova password</label>
      <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password">
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Salva</button>
      <a class="btn secondary" href="<?= e(url('/admin')) ?>">Torna al pannello</a>
    </div>
  </form>
</div>
