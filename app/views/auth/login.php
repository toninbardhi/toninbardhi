<div class="panel" style="max-width:420px; margin:40px auto">
  <h1 style="margin-top:0">Accesso</h1>
  <p class="muted">Area riservata a editor e amministratori.</p>
  <form action="<?= e(url('/login')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="field">
      <label for="email">Email</label>
      <input type="email" name="email" id="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">Entra</button>
      <a class="btn secondary" href="<?= e(url('/')) ?>">Al sito</a>
    </div>
  </form>
</div>
