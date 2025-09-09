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
            <thead class="table-light">
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Endereço Registrado</th>
                    <th>Fonte</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pontos as $ponto): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i:s', strtotime($ponto['data_hora'])) ?></td>
                        <td><span class="badge <?= getBadgeClass($ponto['tipo']) ?>"><?= formatTipo(htmlspecialchars($ponto['tipo'])) ?></span></td>
                        <td><?= htmlspecialchars($ponto['location'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge bg-<?= ($ponto['location_source'] ?? 'ip') === 'browser' ? 'primary' : 'secondary' ?>">
                                <?= htmlspecialchars(strtoupper($ponto['location_source'] ?? 'ip')) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($ponto['ip_address'] ?? 'N/A') ?></td>
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