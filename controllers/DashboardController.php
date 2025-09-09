<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController {

    public function index() {
        $this->ensureAuthenticated();

        $pdo = getDbConnection();
        $userModel = new User($pdo);
        $pontoModel = new Ponto($pdo);

        $user = $userModel->findById($_SESSION['user_id']);

        // Passa os dados para a view
        $data = [
            'user' => $user,
            'pageTitle' => 'Painel'
        ];

        $this->loadView('dashboard', $data);
    }
}