<?php include __DIR__ . '/../includes/header.php'; ?>

<h1 class="mb-4"><?= isset($user) && $user ? 'Editar Usuário' : 'Criar Novo Usuário' ?></h1>

<div class="card">
    <div class="card-body">
        <form action="<?= isset($user) && $user ? BASE_URL . '/admin/users/update' : BASE_URL . '/admin/users/store' ?>" method="POST">
            <?php if (isset($user) && $user): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha <?= isset($user) && $user ? '(Deixe em branco para não alterar)' : '' ?></label>
                <input type="password" class="form-control" id="senha" name="senha" <?= isset($user) && $user ? '' : 'required' ?>>
            </div>
            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select name="perfil" id="perfil" class="form-select" required>
                    <option value="colaborador" <?= (isset($user) && $user['perfil'] === 'colaborador') ? 'selected' : '' ?>>Colaborador</option>
                    <option value="admin" <?= (isset($user) && $user['perfil'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"><?= isset($user) && $user ? 'Salvar Alterações' : 'Criar Usuário' ?></button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>