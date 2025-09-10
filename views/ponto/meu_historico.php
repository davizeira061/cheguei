<?php
// Helpers para formatação
function formatarSegundos($total_segundos) {
    if ($total_segundos < 0) {
        $sinal = '-';
        $total_segundos = abs($total_segundos);
    } else {
        $sinal = '';
    }
    $horas = floor($total_segundos / 3600);
    $minutos = floor(($total_segundos % 3600) / 60);
    return sprintf('%s%02d:%02d', $sinal, $horas, $minutos);
}

function getStatusBadge($status) {
    switch ($status) {
        case 'completo':
            return '<span class="badge bg-success">Completo</span>';
        case 'incompleto':
            return '<span class="badge bg-warning text-dark">Incompleto</span>';
        default:
            return '<span class="badge bg-secondary">N/A</span>';
    }
}

function getSaldoClass($segundos) {
    if ($segundos > 0) return 'text-success';
    if ($segundos < 0) return 'text-danger';
    return 'text-muted';
}
?>

<h1 class="mb-4">Meu Histórico de Pontos</h1>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">Filtros</div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/ponto/meu_historico" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Data Inicial</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($filtros['start_date']) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Data Final</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($filtros['end_date']) ?>">
            </div>
            <div class="col-md-3">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select">
                    <option value="todos" <?= $filtros['situacao'] == 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="completo" <?= $filtros['situacao'] == 'completo' ? 'selected' : '' ?>>Completo</option>
                    <option value="incompleto" <?= $filtros['situacao'] == 'incompleto' ? 'selected' : '' ?>>Incompleto</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Resumo do Período -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Total de Horas Trabalhadas</h5>
                <p class="card-text fs-2"><?= formatarSegundos($resumo['total_segundos_periodo']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Saldo do Banco de Horas</h5>
                <p class="card-text fs-2 <?= getSaldoClass($resumo['banco_horas_saldo_periodo']) ?>">
                    <?= formatarSegundos($resumo['banco_horas_saldo_periodo']) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Resultados -->
<div class="card">
    <div class="card-header">Meus Resultados Detalhados</div>
    <div class="card-body">
        <table id="historicoTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Horas Trabalhadas</th>
                    <th>Saldo do Dia</th>
                    <th>Registros</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resumo['resumo_diario'])): ?>
                    <?php foreach ($resumo['resumo_diario'] as $data => $dia): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($data)) ?></td>
                        <td><?= getStatusBadge($dia['status']) ?></td>
                        <td><?= formatarSegundos($dia['total_segundos_trabalhados']) ?></td>
                        <td class="<?= getSaldoClass($dia['banco_horas_saldo']) ?>"><?= formatarSegundos($dia['banco_horas_saldo']) ?></td>
                        <td>
                            <?php foreach ($dia['registros'] as $r): ?>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $r['tipo']))) ?>
                                    <?= date('H:i', strtotime($r['data_hora'])) ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum registro encontrado para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Adiciona o script do DataTables -->
<script>
$(document).ready(function() {
    new DataTable('#historicoTable', {
        layout: {
            topStart: {
                buttons: ['csv', 'pdf']
            }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/2.0.3/i18n/pt-BR.json',
        },
        order: [[0, 'desc']]
    });
});
</script>