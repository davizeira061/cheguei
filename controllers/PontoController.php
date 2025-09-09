<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/User.php'; // Para pegar nome do usuário no relatório

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /cheguei/login');
    exit;
}

$pdo = getDbConnection();
$pontoModel = new Ponto($pdo);
$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'registrar_ponto') {
        $tipo = $_POST['tipo'] ?? ''; // 'entrada' ou 'saida'
        if ($pontoModel->registerPonto($_SESSION['user_id'], $tipo)) {
            $_SESSION['success_message'] = 'Ponto registrado com sucesso!';
        } else {
            $_SESSION['error_message'] = 'Erro ao registrar ponto.';
        }
        header('Location: /cheguei/dashboard');
        exit;
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'meu_historico') {
        $pontos = $pontoModel->getPontosByUsuario($_SESSION['user_id']);
        include __DIR__ . '/../views/ponto/meu_historico.php';
        exit;
    }

    if ($_GET['action'] === 'relatorio') {
        // Apenas administradores podem acessar o relatório geral
        if ($_SESSION['user_perfil'] !== 'admin') {
            $_SESSION['error_message'] = 'Você não tem permissão para acessar esta página.';
            header('Location: /cheguei/dashboard');
            exit;
        }

        $users = $userModel->getAllUsers(); // Para o filtro de usuário

        $usuario_id = $_GET['usuario_id'] ?? null;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        $pontos = $pontoModel->getAllPontos($start_date, $end_date, $usuario_id);

        $total_segundos = 0;
        if ($usuario_id && $start_date && $end_date) {
             // Esta função pega os pontos já filtrados para calcular as horas
            $total_segundos = $pontoModel->calculateTotalHoursFromRecords($pontos);
        }

        $total_horas_formatado = '';
        if ($total_segundos > 0) {
            $horas = floor($total_segundos / 3600);
            $minutos = floor(($total_segundos % 3600) / 60);
            $segundos = $total_segundos % 60;
            $total_horas_formatado = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
        }

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=relatorio_pontos.csv');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID Ponto', 'ID Usuario', 'Nome Usuario', 'Data/Hora', 'Tipo'], ';');

            foreach ($pontos as $p) {
                fputcsv($output, [$p['id'], $p['usuario_id'], $p['usuario_nome'], $p['data_hora'], $p['tipo']], ';');
            }
            fclose($output);
            exit;
        }

        if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
            // Implementação de PDF seria mais complexa, exigiria uma biblioteca como FPDF ou DomPDF
            // Por simplicidade, vou deixar um placeholder e uma mensagem de que seria necessário uma lib externa.
            $_SESSION['error_message'] = 'A exportação para PDF requer uma biblioteca externa (ex: DomPDF) e não está implementada nesta versão básica.';
            header('Location: /cheguei/ponto/relatorio?usuario_id=' . $usuario_id . '&start_date=' . $start_date . '&end_date=' . $end_date);
            exit;
        }

        include __DIR__ . '/../views/ponto/relatorio.php';
        exit;
    }
}
?>