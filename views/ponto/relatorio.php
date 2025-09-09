<?php include __DIR__ . '/../includes/header.php'; ?>

<h1 class="mb-4">Relatório de Pontos</h1>

<div class="card mb-4">
    <div class="card-header">
        Filtros
    </div>
    <div class="card-body">
        <form action="/cheguei/ponto/relatorio" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="usuario_id" class="form-label">Usuário:</label>
                <select name="usuario_id" id="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>" <?= (isset($usuario_id) && $usuario_id == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Data Inicial:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($start_date ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Data Final:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($end_date ?? '') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($pontos)): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Resultados:</h3>
        <div>
            <a href="/cheguei/ponto/relatorio?usuario_id=<?= htmlspecialchars($usuario_id ?? '') ?>&start_date=<?= htmlspecialchars($start_date ?? '') ?>&end_date=<?= htmlspecialchars($end_date ?? '') ?>&export=csv" class="btn btn-success me-2">Exportar CSV</a>
            <a href="/cheguei/ponto/relatorio?usuario_id=<?= htmlspecialchars($usuario_id ?? '') ?>&start_date=<?= htmlspecialchars($start_date ?? '') ?>&end_date=<?= htmlspecialchars($end_date ?? '') ?>&export=pdf" class="btn btn-warning">Exportar PDF (Não implementado)</a>
        </div>
    </div>

    <?php if ($total_horas_formatado): ?>
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
                        <td><span class="badge <?= $ponto['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst(htmlspecialchars($ponto['tipo'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: 
