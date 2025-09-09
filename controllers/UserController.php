<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->ensureAdmin(); // Apenas administradores podem gerenciar usuários
        $pdo = getDbConnection();
        $this->userModel = new User($pdo);
    }

    public function listUsers() {
        $users = $this->userModel->getAllUsers();
        $this->loadView('admin/users', [
            'users' => $users,
            'pageTitle' => 'Gerenciar Usuários'
        ]);
    }

    public function showCreateForm() {
        $this->loadView('admin/users_form', [
            'pageTitle' => 'Criar Novo Usuário',
            'user' => null // Para o formulário de criação
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $perfil = $_POST['perfil'] ?? 'colaborador';

            if (empty($nome) || empty($email) || empty($senha)) {
                $_SESSION['error_message'] = 'Todos os campos são obrigatórios.';
                $this->redirect('/admin/users/create');
            }

            if ($this->userModel->createUser($nome, $email, $senha, $perfil)) {
                $_SESSION['success_message'] = 'Usuário criado com sucesso!';
                $this->redirect('/admin/users');
            } else {
                $_SESSION['error_message'] = 'Erro ao criar usuário. O email já pode existir.';
                $this->redirect('/admin/users/create');
            }
        }
    }

    public function showEditForm() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/users');
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }

        $this->loadView('admin/users_form', [
            'user' => $user,
            'pageTitle' => 'Editar Usuário'
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? null; // Senha é opcional na atualização
            $perfil = $_POST['perfil'] ?? 'colaborador';

            if (empty($id) || empty($nome) || empty($email)) {
                $_SESSION['error_message'] = 'Nome e email são obrigatórios.';
                $this->redirect('/admin/users/edit?id=' . $id);
            }

            if ($this->userModel->updateUser($id, $nome, $email, $perfil, $senha)) {
                $_SESSION['success_message'] = 'Usuário atualizado com sucesso!';
                $this->redirect('/admin/users');
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar usuário.';
                $this->redirect('/admin/users/edit?id=' . $id);
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Evitar que o admin se auto-delete
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error_message'] = 'Você não pode excluir sua própria conta.';
            } else {
                if ($this->userModel->deleteUser($id)) {
                    $_SESSION['success_message'] = 'Usuário excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao excluir usuário.';
                }
            }
        }
        $this->redirect('/admin/users');
    }
}