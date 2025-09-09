<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="jumbotron bg-light p-5 rounded-lg m-3">
    <h1 class="display-4">Bem-vindo(a), <?= htmlspecialchars($user['nome']) ?>!</h1>
    <p class="lead">Seu perfil: <?= htmlspecialchars($user['perfil']) ?>.</p>
    <hr class="my-4">
    <p>Aqui você pode registrar seu ponto e acessar suas funcionalidades.</p>

    <?php if ($user['perfil'] === 'colaborador'): ?>
        <h2 class="mt-5">Registrar Ponto</h2>
        <div class="d-grid gap-2 d-md-block">
            <form action="/cheguei/ponto" method="POST" class="d-inline">
                <input type="hidden" name="action" value="registrar_ponto">
                <input type="hidden" name="tipo" value="entrada">
                <button type="submit" class="btn btn-success btn-lg me-2">Registrar Entrada</button>
            </form>
            <form action="/cheguei/ponto" method="POST" class="d-inline">
                <input type="hidden" name="action" value="registrar_ponto">
                <input type="hidden" name="tipo" value="saida">
                <button type="submit" class="btn btn-danger btn-lg">Registrar Saída</button>
            </form>
        </div>

        <h3 class="mt-5">Seus Últimos Pontos</h3>
        <?php if (!empty($user_pontos)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_pontos as $ponto): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($ponto['data_hora'])) ?></td>
                                <td><span class="badge <?= $ponto['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($ponto['tipo']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="/cheguei/ponto/meu_historico" class="btn btn-info mt-3">Ver Histórico Completo</a>
        <?php else: ?>
            <p class="mt-3">Nenhum ponto registrado ainda.</p>
        <?php endif; ?>

    <?php elseif ($user['perfil'] === 'admin'): ?>
        <h2 class="mt-5">Área Administrativa</h2>
        <div class="list-group">
            <a href="/cheguei/admin/users" class="list-group-item list-group-item-action">Gerenciar Usuários</a>
            <a href="/cheguei/ponto/relatorio" class="list-group-item list-group-item-action">Ver Relatórios de Ponto</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>