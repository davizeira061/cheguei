<?php
session_start(); // Inicia a sessão no front controller

// Autoload para classes dos modelos (opcional, pode ser feito manualmente como já está)
// spl_autoload_register(function ($class_name) {
//     $paths = [
//         __DIR__ . '/../models/',
//         __DIR__ . '/../controllers/'
//     ];
//     foreach ($paths as $path) {
//         $file = $path . $class_name . '.php';
//         if (file_exists($file)) {
//             require_once $file;
//             return;
//         }
//     }
// });

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/cheguei', '', $request_uri); // Ajuste se seu projeto estiver em um subdiretório

// Remove barras extras e garante que começa com barra
$request_uri = '/' . trim($request_uri, '/');

// Rotas
switch ($request_uri) {
    case '/login':
        require __DIR__ . '/../controllers/AuthController.php';
        break;
    case '/logout':
        require __DIR__ . '/../controllers/AuthController.php';
        break;
    case '/dashboard':
        require __DIR__ . '/../controllers/DashboardController.php';
        break;
    case '/ponto':
        require __DIR__ . '/../controllers/PontoController.php';
        break;
    case '/ponto/meu_historico':
        $_GET['action'] = 'meu_historico';
        require __DIR__ . '/../controllers/PontoController.php';
        break;
    case '/ponto/relatorio':
        $_GET['action'] = 'relatorio';
        require __DIR__ . '/../controllers/PontoController.php';
        break;
    case '/admin/users':
        require __DIR__ . '/../controllers/UserController.php';
        break;
    case '/admin/users/create':
        // A action de criar será tratada pelo formulário POST
        // apenas exibe o formulário vazio
        $users = []; // Para não dar erro na view
        require __DIR__ . '/../views/admin/user_form.php';
        break;
    case '/admin/users/edit':
        $_GET['action'] = 'edit_user';
        require __DIR__ . '/../controllers/UserController.php';
        break;
    case '/admin/users/delete':
        $_GET['action'] = 'delete_user';
        require __DIR__ . '/../controllers/UserController.php';
        break;
    case '/':
        // Redireciona a raiz para o dashboard se logado, senão para o login
        if (isset($_SESSION['user_id'])) {
            header('Location: /cheguei/dashboard');
        } else {
            header('Location: /cheguei/login');
        }
        exit;
    default:
        // Página não encontrada
        http_response_code(404);
        include __DIR__ . '/../views/404.php';
        break;
}