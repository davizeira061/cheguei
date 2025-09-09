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

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($senha, $user['senha'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_perfil'] = $user['perfil'];
                $this->redirect('/dashboard');
            } else {
                // Falha no login
                // O erro genérico é o mais seguro para produção.
                $_SESSION['error_message'] = 'Email ou senha incorretos.';

                // Para depuração, podemos verificar se o usuário foi encontrado mas a senha falhou.
                // Isto sugere um problema com o hash da senha (ex: coluna do DB muito curta).
                if ($user) {
                    // Não mostre esta mensagem para o usuário final em produção.
                    // Apenas para ajudar no diagnóstico do problema atual.
                    error_log("Login falhou para o usuário '{$email}': A senha não confere. Verifique se a coluna 'senha' no DB é VARCHAR(255).");
                }

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