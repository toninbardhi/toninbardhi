<?php /** @var array $users */ $me = current_user(); ?>
<div class="page-head">
  <h1 style="margin:0">Utenti</h1>
  <a class="btn" href="<?= e(url('/admin/users/create')) ?>">+ Nuovo utente</a>
</div>

<table class="data">
  <thead><tr><th>Nome</th><th>Email</th><th>Ruolo</th><th>Azioni</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><strong><?= e($u['name']) ?></strong></td>
        <td><?= e($u['email']) ?></td>
        <td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
        <td class="actions-cell">
          <a class="btn small secondary" href="<?= e(url('/admin/users/' . $u['id'] . '/edit')) ?>">Modifica</a>
          <?php if (!$me || (int)$me['id'] !== (int)$u['id']): ?>
            <form method="post" action="<?= e(url('/admin/users/' . $u['id'] . '/delete')) ?>"
                  onsubmit="return confirm('Eliminare l\'utente <?= e($u['name']) ?>?');">
              <?= csrf_field() ?>
              <button class="btn small danger" type="submit">Elimina</button>
            </form>
          <?php else: ?>
            <span class="muted" style="font-size:.8rem">(tu)</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
