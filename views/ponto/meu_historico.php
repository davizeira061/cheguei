<?php
function getBadgeClass($tipo) {
    switch ($tipo) {
        case 'entrada':
            return 'bg-success';
        case 'retorno_almoco':
            return 'bg-info';
        case 'saida_almoco':
            return 'bg-warning';
        case 'saida':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function formatTipo($tipo) {
    return ucfirst(str_replace('_', ' ', $tipo));
}
?>

<h1 class="mb-4">Meu Histórico de Pontos</h1>

<?php if (!empty($pontos)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Ponto</th>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pontos as $ponto): ?>
                    <tr>
                        <td><?= htmlspecialchars($ponto['id']) ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($ponto['data_hora'])) ?></td>
                        <td><span class="badge <?= getBadgeClass($ponto['tipo']) ?>"><?= formatTipo(htmlspecialchars($ponto['tipo'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Nenhum ponto registrado no seu histórico ainda.
    </div>
<?php endif; ?>