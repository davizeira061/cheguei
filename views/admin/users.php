<?php include __DIR__ . '/../includes/header.php'; ?>

<h1 class="mb-4">Gerenciar Usuários</h1>

<a href="/cheguei/admin/users/create" class="btn btn-primary mb-3">Adicionar Novo Usuário</a>

<?php if (!empty($users)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['nome']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="badge <?= $user['perfil'] === 'admin' ? 'bg-info' : 'bg-secondary' ?>"><?= ucfirst(htmlspecialchars($user['perfil'])) ?></span></td>
                        <td>
                            <a href="/cheguei/admin/users/edit?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-warning me-2">Editar</a>
                            <a href="/cheguei/admin/users/delete?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Nenhum usuário cadastrado ainda.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>