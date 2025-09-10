<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/HorasTrabalhadasService.php'; // Adicionado
require_once __DIR__ . '/BaseController.php';

class PontoController extends BaseController {
    private $pontoModel;
    private $userModel;
    private $horasService; // Adicionado

    public function __construct() {
        $this->ensureAuthenticated();
        $pdo = getDbConnection();
        $this->pontoModel = new Ponto($pdo);
        $this->userModel = new User($pdo);
        $this->horasService = new HorasTrabalhadasService(); // Adicionado
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $tipo = $_POST['tipo'] ?? '';

            if (!in_array($tipo, ['entrada', 'saida_almoco', 'retorno_almoco', 'saida'])) {
                $_SESSION['error_message'] = "Tipo de registro inválido.";
                $this->redirect('/dashboard');
                return;
            }

            $ip_address = $this->getUserIP();
            $location_source = $_POST['location_source'] ?? 'ip';
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $location = 'N/A';

            if ($location_source === 'browser' && $latitude && $longitude) {
                $location = $this->getAddressFromCoordinates($latitude, $longitude);
            } else {
                $location = $this->getLocationFromIP($ip_address);
                $location_source = 'ip';
            }

            if ($this->pontoModel->registerPonto($userId, $tipo, $ip_address, $location_source, $latitude, $longitude, $location)) {
                $_SESSION['success_message'] = "Ponto de '{$tipo}' registrado com sucesso!";
            } else {
                $_SESSION['error_message'] = 'Erro desconhecido ao tentar registrar o ponto.';
            }

        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erro no Banco de Dados: Não foi possível registrar o ponto.';
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Ocorreu um erro inesperado. Por favor, tente novamente.';
        }

        $this->redirect('/dashboard');
    }

    private function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) { return $_SERVER['HTTP_CLIENT_IP']; }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { return $_SERVER['HTTP_X_FORWARDED_FOR']; }
        return $_SERVER['REMOTE_ADDR'];
    }

    private function getAddressFromCoordinates($lat, $lon) {
        if (!function_exists('curl_init')) { return "Localização indisponível (cURL não habilitado)"; }
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ChegueiApp/1.0');
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            $data = json_decode($response, true);
            return $data['display_name'] ?? "Endereço não encontrado";
        }
        return "Endereço não encontrado para as coordenadas.";
    }

    private function getLocationFromIP($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return "Localização indisponível (IP privado)";
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
        // Parâmetros de filtro
        $usuario_id = $_SESSION['user_id'];
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $situacao = $_GET['situacao'] ?? 'todos';

        // Busca os pontos e calcula o resumo
        $pontos = $this->pontoModel->getAllPontos($start_date, $end_date, $usuario_id, 'ASC');
        $resumo_calculado = $this->horasService->calcularHorasTrabalhadas($pontos);

        // Filtra por situação se necessário
        if ($situacao !== 'todos') {
            $resumo_calculado['resumo_diario'] = array_filter(
                $resumo_calculado['resumo_diario'],
                fn($dia) => $dia['status'] === $situacao
            );
        }

        $this->loadView('ponto/meu_historico', [
            'resumo' => $resumo_calculado,
            'filtros' => ['start_date' => $start_date, 'end_date' => $end_date, 'situacao' => $situacao],
            'pageTitle' => 'Meu Histórico de Pontos'
        ]);
    }

    public function relatorioGeral() {
        $this->ensureAdmin();

        // Parâmetros de filtro
        $users = $this->userModel->getAllUsers();
        $usuario_id = $_GET['usuario_id'] ?? null;
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $situacao = $_GET['situacao'] ?? 'todos';

        // Inicializa variáveis
        $resumo_calculado = [
            'resumo_diario' => [],
            'total_segundos_periodo' => 0,
            'banco_horas_saldo_periodo' => 0,
        ];

        if ($usuario_id) {
            // Busca os pontos e calcula o resumo
            $pontos = $this->pontoModel->getAllPontos($start_date, $end_date, $usuario_id, 'ASC');
            $resumo_calculado = $this->horasService->calcularHorasTrabalhadas($pontos);

            // Filtra por situação se necessário
            if ($situacao !== 'todos') {
                $resumo_calculado['resumo_diario'] = array_filter(
                    $resumo_calculado['resumo_diario'],
                    fn($dia) => $dia['status'] === $situacao
                );
            }
        }

        $this->loadView('ponto/relatorio', [
            'users' => $users,
            'resumo' => $resumo_calculado,
            'filtros' => ['usuario_id' => $usuario_id, 'start_date' => $start_date, 'end_date' => $end_date, 'situacao' => $situacao],
            'pageTitle' => 'Relatório Geral de Pontos'
        ]);
    }

    public function calendario() {
        $users = [];
        // Se for admin, busca todos os usuários para o filtro
        if ($_SESSION['user_perfil'] === 'admin') {
            $users = $this->userModel->getAllUsers();
        }

        $this->loadView('ponto/calendario', [
            'users' => $users,
            'pageTitle' => 'Calendário de Pontos'
        ]);
    }

    public function calendarioJson() {
        header('Content-Type: application/json');

        $start_date = $_GET['start'] ?? date('Y-m-01');
        $end_date = $_GET['end'] ?? date('Y-m-t');

        // Admin pode ver outros usuários, colaborador só pode ver a si mesmo
        if ($_SESSION['user_perfil'] === 'admin') {
            $usuario_id = $_GET['usuario_id'] ?? $_SESSION['user_id'];
        } else {
            $usuario_id = $_SESSION['user_id'];
        }

        $pontos = $this->pontoModel->getAllPontos($start_date, $end_date, $usuario_id, 'ASC');
        $resumo = $this->horasService->calcularHorasTrabalhadas($pontos);

        $events = [];
        foreach ($resumo['resumo_diario'] as $data => $dia) {
            $total_horas_formatado = $this->formatarSegundos($dia['total_segundos_trabalhados']);

            $event_title = "Horas: " . $total_horas_formatado;
            $event_color = $dia['status'] === 'completo' ? '#28a745' : '#ffc107';

            $events[] = [
                'title' => $event_title,
                'start' => $data,
                'backgroundColor' => $event_color,
                'borderColor' => $event_color,
                'extendedProps' => [
                    'status' => ucfirst($dia['status']),
                    'saldo' => $this->formatarSegundos($dia['banco_horas_saldo']),
                    'registros' => array_map(fn($r) => [
                        'tipo' => ucfirst(str_replace('_', ' ', $r['tipo'])),
                        'hora' => date('H:i:s', strtotime($r['data_hora']))
                    ], $dia['registros'])
                ]
            ];
        }

        echo json_encode($events);
        exit;
    }

    private function formatarSegundos($total_segundos) {
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
}