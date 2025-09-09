<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class PontoController extends BaseController {
    private $pontoModel;
    private $userModel;

    public function __construct() {
        $this->ensureAuthenticated();
        $pdo = getDbConnection();
        $this->pontoModel = new Ponto($pdo);
        $this->userModel = new User($pdo);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $tipo = $_POST['tipo'] ?? '';

            if (in_array($tipo, ['entrada', 'saida_almoco', 'retorno_almoco', 'saida'])) {
                if ($this->pontoModel->registerPonto($userId, $tipo)) {
                    $_SESSION['success_message'] = "Ponto de '{$tipo}' registrado com sucesso!";
                } else {
                    $_SESSION['error_message'] = 'Erro ao registrar ponto.';
                }
            } else {
                $_SESSION['error_message'] = "Tipo de registro inválido.";
            }
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/dashboard');
        }
    }

    public function meuHistorico() {
        $userId = $_SESSION['user_id'];
        $pontos = $this->pontoModel->getPontosByUsuario($userId);

        $this->loadView('ponto/meu_historico', [
            'pontos' => $pontos,
            'pageTitle' => 'Meu Histórico de Pontos'
        ]);
    }

    public function relatorioGeral() {
        $this->ensureAdmin();

        $users = $this->userModel->getAllUsers();

        $usuario_id = $_GET['usuario_id'] ?? null;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        $pontos = $this->pontoModel->getAllPontos($start_date, $end_date, $usuario_id);

        $total_segundos = 0;
        if ($usuario_id && $start_date && $end_date) {
            $total_segundos = $this->pontoModel->calculateTotalHoursFromRecords($pontos);
        }

        $total_horas_formatado = '';
        if ($total_segundos > 0) {
            $horas = floor($total_segundos / 3600);
            $minutos = floor(($total_segundos % 3600) / 60);
            $total_horas_formatado = sprintf('%02d:%02d', $horas, $minutos);
        }

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCsv($pontos);
        }

        $this->loadView('ponto/relatorio', [
            'pontos' => $pontos,
            'users' => $users,
            'total_horas_formatado' => $total_horas_formatado,
            'pageTitle' => 'Relatório Geral de Pontos'
        ]);
    }

    private function exportCsv($pontos) {
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
}