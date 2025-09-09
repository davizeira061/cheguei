<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $pdo = getDbConnection();
        $this->userModel = new User($pdo);
    }

    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm() {
        // Apenas carrega a view, sem header/footer para um layout diferente
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Processa a tentativa de login.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $user = $this->userModel->verifyPassword($email, $senha);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_perfil'] = $user['perfil'];
                $this->redirect('/dashboard');
            } else {
                $_SESSION['error_message'] = 'Email ou senha incorretos.';
                $this->redirect('/login');
            }
        } else {
            // Se não for POST, redireciona para o formulário de login
            $this->redirect('/login');
        }
    }

    /**
     * Faz o logout do usuário.
     */
    public function logout() {
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }
}