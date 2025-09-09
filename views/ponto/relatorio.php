<h1 class="mb-4">Relatório de Pontos</h1>

<div class="card mb-4">
    <div class="card-header">
        Filtros
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/admin/relatorio" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="usuario_id" class="form-label">Usuário:</label>
                <select name="usuario_id" id="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>" <?= (isset($_GET['usuario_id']) && $_GET['usuario_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Data Inicial:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Data Final:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($pontos) && !empty($pontos)): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Resultados:</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/relatorio?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-success me-2">Exportar CSV</a>
        </div>
    </div>

    <?php if (isset($total_horas_formatado) && $total_horas_formatado): ?>
        <div class="alert alert-info mt-3" role="alert">
            Total de Horas Trabalhadas no Período para o usuário selecionado: <strong><?= htmlspecialchars($total_horas_formatado) ?></strong>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Ponto</th>
                    <th>Usuário</th>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pontos as $ponto): ?>
                    <tr>
                        <td><?= htmlspecialchars($ponto['id']) ?></td>
                        <td><?= htmlspecialchars($ponto['usuario_nome']) ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($ponto['data_hora'])) ?></td>
                        <td><span class="badge bg-<?= $ponto['tipo'] === 'entrada' ? 'success' : ($ponto['tipo'] === 'saida' ? 'danger' : 'warning') ?>"><?= ucfirst(htmlspecialchars($ponto['tipo'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Nenhum registro encontrado para os filtros selecionados.
    </div>
<?php endif; ?>
