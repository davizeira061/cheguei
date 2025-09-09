<?php

class BaseController {

    /**
     * Carrega uma view.
     *
     * @param string $viewName O nome do arquivo da view (sem .php)
     * @param array $data Dados a serem extraídos para a view
     */
    protected function loadView($viewName, $data = []) {
        // Extrai as variáveis para que possam ser usadas diretamente na view
        extract($data);

        // Inclui o header
        require_once __DIR__ . '/../views/includes/header.php';

        // Inclui o conteúdo da view
        require_once __DIR__ . '/../views/' . $viewName . '.php';

        // Inclui o footer
        require_once __DIR__ . '/../views/includes/footer.php';
    }

    /**
     * Redireciona para uma URL.
     *
     * @param string $url A URL de destino (relativa à BASE_URL)
     */
    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Garante que o usuário está autenticado.
     * Se não estiver, redireciona para a página de login.
     */
    protected function ensureAuthenticated() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Garante que o usuário é um administrador.
     * Se não for, mostra uma página de não autorizado ou redireciona.
     */
    protected function ensureAdmin() {
        $this->ensureAuthenticated(); // Admins devem estar autenticados primeiro
        if ($_SESSION['user_perfil'] !== 'admin') {
            // Pode ser uma view de "acesso negado" ou redirecionar para o dashboard
            http_response_code(403);
            $this->loadView('403'); // Carrega a view views/403.php
            exit;
        }
    }
}
