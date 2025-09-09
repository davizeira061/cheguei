<div class="jumbotron bg-light p-5 rounded-lg m-3">
    <h1 class="display-4">Bem-vindo(a), <?= htmlspecialchars($user['nome']) ?>!</h1>
    <p class="lead">Seu perfil: <?= htmlspecialchars($user['perfil']) ?>.</p>
    <hr class="my-4">
    <p>Aqui você pode registrar seu ponto e acessar suas funcionalidades.</p>

    <?php if ($user['perfil'] === 'colaborador'): ?>
        <h2 class="mt-5 mb-4 text-center">Registrar Ponto</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm text-center h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-4">Marcações Simples</h5>
                        <div class="d-grid gap-3">
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="entrada">
                                <button type="submit" class="btn btn-success btn-lg w-100">Registrar Entrada</button>
                            </form>
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="saida">
                                <button type="submit" class="btn btn-danger btn-lg w-100">Registrar Saída</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm text-center h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-4">Marcações de Almoço</h5>
                        <div class="d-grid gap-3">
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="saida_almoco">
                                <button type="submit" class="btn btn-warning btn-lg w-100">Saída Almoço</button>
                            </form>
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="retorno_almoco">
                                <button type="submit" class="btn btn-info btn-lg w-100">Retorno Almoço</button>
                            </form>
                        </div>
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

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmacaoModal" tabindex="-1" aria-labelledby="confirmacaoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmacaoModalLabel">Confirmar Registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Deseja realmente registrar este ponto?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-confirmar-registro">Confirmar</button>
      </div>
    </div>
  </div>
</div>