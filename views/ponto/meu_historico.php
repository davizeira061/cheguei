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
                        <td><span class="badge <?= $ponto['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst(htmlspecialchars($ponto['tipo'])) ?></span></td>
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