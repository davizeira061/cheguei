<?php include __DIR__ . '/../includes/header.php'; ?>

<h1 class="mb-4"><?= isset($user_to_edit) ? 'Editar Usuário' : 'Criar Novo Usuário' ?></h1>

<div class="card">
    <div class="card-body">
        <form action="/cheguei/admin/users" method="POST">
            <?php if (isset($user_to_edit)): ?>
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user_to_edit['id']) ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create_user">
            <?php endif; ?>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($user_to_edit['nome'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_to_edit['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha <?= isset($user_to_edit) ? '(Deixe em branco para não alterar)' : '' ?></label>
                <input type="password" class="form-control" id="senha" name="senha" <?= isset($user_to_edit) ? '' : 'required' ?>>
            </div>
            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select name="perfil" id="perfil" class="form-select" required>
                    <option value="colaborador" <?= (isset($user_to_edit) && $user_to_edit['perfil'] === 'colaborador') ? 'selected' : '' ?>>Colaborador</option>
                    <option value="admin" <?= (isset($user_to_edit) && $user_to_edit['perfil'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"><?= isset($user_to_edit) ? 'Salvar Alterações' : 'Criar Usuário' ?></button>
                <a href="/cheguei/admin/users" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>