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
                $ip_address = $this->getUserIP();
                $location_source = $_POST['location_source'] ?? 'ip';
                $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
                $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
                $location = 'N/A';

                if ($location_source === 'browser' && $latitude && $longitude) {
                    $location = $this->getAddressFromCoordinates($latitude, $longitude);
                } else {
                    $location = $this->getLocationFromIP($ip_address);
                    $location_source = 'ip'; // Garante que a fonte seja 'ip' se o fallback for usado
                }

                if ($this->pontoModel->registerPonto($userId, $tipo, $ip_address, $location_source, $latitude, $longitude, $location)) {
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
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    private function getAddressFromCoordinates($lat, $lon) {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&addressdetails=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // É crucial definir um User-Agent para o Nominatim
        curl_setopt($ch, CURLOPT_USERAGENT, 'ChegueiApp/1.0 (seu-email@exemplo.com)');
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['display_name'])) {
                return $data['display_name'];
            }
        }
        return "Endereço não encontrado para as coordenadas.";
    }

    private function getLocationFromIP($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return "Localização indisponível (IP privado ou inválido)";
        }

        $url = "http://ip-api.com/json/{$ip}?lang=pt-BR";
        $response = @file_get_contents($url);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['status'] == 'success') {
                return "{$data['city']}, {$data['regionName']}, {$data['country']}";
            }
        }
        return "Localização não encontrada pelo IP.";
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