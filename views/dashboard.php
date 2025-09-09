<div class="jumbotron bg-light p-5 rounded-lg m-3">
    <h1 class="display-4">Bem-vindo(a), <?= htmlspecialchars($user['nome']) ?>!</h1>
    <p class="lead">Seu perfil: <?= htmlspecialchars($user['perfil']) ?>.</p>
    <hr class="my-4">
    <p>Aqui você pode registrar seu ponto e acessar suas funcionalidades.</p>

    <?php if ($user['perfil'] === 'colaborador'): ?>
        <h2 class="mt-5">Registrar Ponto</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Marcações Simples</h5>
                        <form action="<?= BASE_URL ?>/ponto/registrar" method="POST" class="d-inline p-1">
                            <input type="hidden" name="tipo" value="entrada">
                            <button type="submit" class="btn btn-success">Registrar Entrada</button>
                        </form>
                        <form action="<?= BASE_URL ?>/ponto/registrar" method="POST" class="d-inline p-1">
                            <input type="hidden" name="tipo" value="saida">
                            <button type="submit" class="btn btn-danger">Registrar Saída</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Marcações de Almoço</h5>
                        <form action="<?= BASE_URL ?>/ponto/registrar" method="POST" class="d-inline p-1">
                            <input type="hidden" name="tipo" value="saida_almoco">
                            <button type="submit" class="btn btn-warning">Saída Almoço</button>
                        </form>
                        <form action="<?= BASE_URL ?>/ponto/registrar" method="POST" class="d-inline p-1">
                            <input type="hidden" name="tipo" value="retorno_almoco">
                            <button type="submit" class="btn btn-info">Retorno Almoço</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
             <a href="<?= BASE_URL ?>/ponto/meu_historico" class="btn btn-primary">Ver Meu Histórico de Pontos</a>
        </div>

    <?php elseif ($user['perfil'] === 'admin'): ?>
        <h2 class="mt-5">Área Administrativa</h2>
        <div class="list-group">
            <a href="<?= BASE_URL ?>/admin/users" class="list-group-item list-group-item-action">Gerenciar Usuários</a>
            <a href="<?= BASE_URL ?>/admin/relatorio" class="list-group-item list-group-item-action">Ver Relatórios de Ponto</a>
        </div>
    <?php endif; ?>
</div>