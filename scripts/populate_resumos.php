<?php
// Script para popular a tabela resumo_mensal com dados históricos.
// Execute este script a partir da linha de comando na raiz do projeto:
// php scripts/populate_resumos.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/ResumoMensal.php';
require_once __DIR__ . '/../models/HorasTrabalhadasService.php';

echo "Iniciando script para popular resumos mensais...\n";

try {
    $pdo = getDbConnection();
    $userModel = new User($pdo);
    $pontoModel = new Ponto($pdo);
    $resumoModel = new ResumoMensal($pdo);
    $horasService = new HorasTrabalhadasService();

    $users = $userModel->getAllUsers();
    if (empty($users)) {
        echo "Nenhum usuário encontrado.\n";
        exit;
    }

    echo "Encontrados " . count($users) . " usuários.\n";

    foreach ($users as $user) {
        echo "Processando usuário: " . htmlspecialchars($user['nome']) . " (ID: {$user['id']})\n";

        // Descobre o primeiro registro do usuário para saber por onde começar
        $stmt = $pdo->prepare("SELECT MIN(data_hora) as primeiro_registro FROM pontos WHERE usuario_id = ?");
        $stmt->execute([$user['id']]);
        $primeiroRegistro = $stmt->fetchColumn();

        if (!$primeiroRegistro) {
            echo " -> Nenhum registro de ponto encontrado para este usuário. Pulando.\n";
            continue;
        }

        $start = new DateTime($primeiroRegistro);
        // Itera até o mês anterior ao atual
        $end = (new DateTime())->modify('first day of last month');
        $interval = new DateInterval('P1M');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $mesAno = $dt->format('Y-m');
            $primeiroDia = $dt->format('Y-m-01');
            $ultimoDia = $dt->format('Y-m-t');

            echo " -> Calculando para o mês: $mesAno... ";

            $pontosDoMes = $pontoModel->getAllPontos($primeiroDia, $ultimoDia, $user['id'], 'ASC');
            if (empty($pontosDoMes)) {
                echo "Nenhum ponto. Pulando.\n";
                continue;
            }

            $resumoCalculado = $horasService->calcularHorasTrabalhadas($pontosDoMes);

            $totalSegundosMes = $resumoCalculado['total_segundos_periodo'];
            $bancoHorasMes = $resumoCalculado['banco_horas_saldo_periodo'];

            $resumoModel->saveResumo([
                'usuario_id' => $user['id'],
                'mes_ano' => $mesAno,
                'total_horas_trabalhadas' => $totalSegundosMes,
                'banco_horas_saldo' => $bancoHorasMes
            ]);

            echo "Salvo! Horas: " . round($totalSegundosMes/3600, 2) . ", Saldo: " . round($bancoHorasMes/3600, 2) . "\n";
        }
    }

    echo "\nProcesso concluído com sucesso!\n";

} catch (Exception $e) {
    echo "\nERRO: " . $e->getMessage() . "\n";
    exit(1);
}
