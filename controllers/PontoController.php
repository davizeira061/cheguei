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
                // Obter IP e localização
                $ip_address = $this->getUserIP();
                $location = $this->getLocationFromIP($ip_address);

                if ($this->pontoModel->registerPonto($userId, $tipo, $ip_address, $location)) {
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

    private function getUserIP() {
        $ip = $_SERVER['REMOTE_ADDR'];

        // Para testes em ambiente local, pode retornar '::1' ou '127.0.0.1'
        // Usamos um IP público para fins de teste de geolocalização.
        if ($ip == '::1' || $ip == '127.0.0.1') {
            return '8.8.8.8'; // IP público do Google para teste
        }

        return $ip;
    }

    private function getLocationFromIP($ip) {
        // Evita fazer requisições para IPs inválidos ou locais
        if (!filter_var($ip, FILTER_VALIDATE_IP) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return "Localização indisponível (IP privado ou inválido)";
        }

        try {
            // Usar @ para suprimir warnings em caso de falha na requisição
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?lang=pt-BR");

            if ($response === false) {
                return "Não foi possível obter a localização";
            }

            $data = json_decode($response, true);

            if ($data && $data['status'] == 'success') {
                $city = $data['city'] ?? 'N/A';
                $region = $data['regionName'] ?? 'N/A';
                $country = $data['country'] ?? 'N/A';
                return "{$city}, {$region}, {$country}";
            }
        } catch (Exception $e) {
            // Em um ambiente de produção, seria bom logar o erro.
            return "Erro ao buscar localização";
        }

        return "Localização não encontrada";
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