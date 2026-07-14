<?php
/** @var array|null $user */
$editing = $user !== null;
$action = $editing
    ? url('/admin/users/' . $user['id'] . '/edit')
    : url('/admin/users/create');
$role = $user['role'] ?? 'editor';
?>
<h1><?= $editing ? 'Modifica utente' : 'Nuovo utente' ?></h1>

<div class="panel" style="max-width:520px">
  <form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label for="name">Nome *</label>
      <input type="text" name="name" id="name" required value="<?= e($user['name'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="email">Email *</label>
      <input type="email" name="email" id="email" required value="<?= e($user['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="password">Password <?= $editing ? '(lascia vuoto per non cambiarla)' : '*' ?></label>
      <input type="password" name="password" id="password" <?= $editing ? '' : 'required' ?> autocomplete="new-password">
      <div class="hint">Almeno 6 caratteri.</div>
    </div>
    <div class="field">
      <label for="role">Ruolo</label>
      <select name="role" id="role">
        <option value="editor" <?= $role === 'editor' ? 'selected' : '' ?>>Editor (gestisce categorie e link)</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Amministratore (anche gli utenti)</option>
      </select>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit"><?= $editing ? 'Salva' : 'Crea utente' ?></button>
      <a class="btn secondary" href="<?= e(url('/admin/users')) ?>">Annulla</a>
    </div>
  </form>
</div>
