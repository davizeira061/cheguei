<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'admin') {
    $_SESSION['error_message'] = 'Você não tem permissão para acessar esta página.';
    header('Location: /cheguei/dashboard');
    exit;
}

$pdo = getDbConnection();
$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] ?? 'colaborador';

        if ($_POST['action'] === 'create_user') {
            if ($userModel->createUser($nome, $email, $senha, $perfil)) {
                $_SESSION['success_message'] = 'Usuário criado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao criar usuário.';
            }
        } elseif ($_POST['action'] === 'update_user') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                if ($userModel->updateUser($id, $nome, $email, $perfil, $senha ?: null)) {
                    $_SESSION['success_message'] = 'Usuário atualizado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao atualizar usuário.';
                }
            }
        }
        header('Location: /cheguei/admin/users');
        exit;
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete_user') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($userModel->deleteUser($id)) {
                $_SESSION['success_message'] = 'Usuário excluído com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao excluir usuário.';
            }
        }
        header('Location: /cheguei/admin/users');
        exit;
    }

    if ($_GET['action'] === 'edit_user') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $user_to_edit = $userModel->findById($id);
            if (!$user_to_edit) {
                $_SESSION['error_message'] = 'Usuário não encontrado.';
                header('Location: /cheguei/admin/users');
                exit;
            }
            include __DIR__ . '/../views/admin/user_form.php';
            exit;
        }
    }
}

// Se não houver ação específica, lista todos os usuários
$users = $userModel->getAllUsers();
include __DIR__ . '/../views/admin/users.php';