<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$pdo = getDbConnection();
$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $user = $userModel->verifyPassword($email, $senha);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_perfil'] = $user['perfil'];
            header('Location: /cheguei/dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = 'Email ou senha incorretos.';
            header('Location: /cheguei/login');
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: /cheguei/login');
    exit;
}

// Se não for POST de login e nem GET de logout, exibe a tela de login
include __DIR__ . '/../views/auth/login.php';