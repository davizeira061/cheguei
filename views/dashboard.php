<div class="container-fluid vh-80 bg-light d-flex flex-column justify-content-center align-items-center text-center">
    <div class="mb-4">
        <h1 class="display-4">Bem-vindo(a), <?= htmlspecialchars($user['nome']) ?>!</h1>
        <p class="lead">Seu perfil: <?= htmlspecialchars($user['perfil']) ?>.</p>
        <hr>
    </div>

    <!-- Relógio -->
    <div id="relogio" class="display-1 fw-bold text-dark mb-5"></div>

    <?php if ($user['perfil'] === 'colaborador'): ?>
        <div class="row w-100 justify-content-center mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Marcações Simples</h5>
                        <div class="d-grid gap-3">
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="entrada">
                                <button type="button" class="btn btn-success btn-lg btn-abrir-modal">Registrar Entrada</button>
                            </form>
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="saida">
                                <button type="button" class="btn btn-danger btn-lg btn-abrir-modal">Registrar Saída</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Marcações de Almoço</h5>
                        <div class="d-grid gap-3">
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="saida_almoco">
                                <button type="button" class="btn btn-warning btn-lg btn-abrir-modal">Saída Almoço</button>
                            </form>
                            <form class="registro-ponto-form" action="<?= BASE_URL ?>/ponto/registrar" method="POST">
                                <input type="hidden" name="tipo" value="retorno_almoco">
                                <button type="button" class="btn btn-info btn-lg btn-abrir-modal">Retorno Almoço</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="<?= BASE_URL ?>/ponto/meu_historico" class="btn btn-primary btn-lg">Ver Meu Histórico de Pontos</a>

    <?php elseif ($user['perfil'] === 'admin'): ?>
        <h2 class="mb-4">Área Administrativa</h2>
        <div class="list-group w-50">
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
                <h5 class="modal-title">Confirmar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<!-- Scripts -->
<script>
    // Relógio
    function atualizarRelogio() {
        const agora = new Date();
        const horas = String(agora.getHours()).padStart(2, '0');
        const minutos = String(agora.getMinutes()).padStart(2, '0');
        const segundos = String(agora.getSeconds()).padStart(2, '0');
        document.getElementById('relogio').textContent = `${horas}:${minutos}:${segundos}`;
    }
    setInterval(atualizarRelogio, 1000);
    atualizarRelogio();

    // Modal
    document.addEventListener("DOMContentLoaded", function() {
        let formAtual = null;
        document.querySelectorAll(".btn-abrir-modal").forEach(botao => {
            botao.addEventListener("click", function() {
                formAtual = this.closest("form");
                let modal = new bootstrap.Modal(document.getElementById('confirmacaoModal'));
                modal.show();
            });
        });
        document.getElementById("btn-confirmar-registro").addEventListener("click", function() {
            if (formAtual) formAtual.submit();
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
