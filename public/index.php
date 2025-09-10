<?php
// Carrega as configurações da aplicação, como a BASE_URL
require_once __DIR__ . '/../config/app.php';

session_start(); // Inicia a sessão no front controller

// Autoloader simples para carregar classes de controladores e modelos
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Roteamento
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (BASE_URL !== '/' && strpos($request_uri, BASE_URL) === 0) {
    $request_uri = substr($request_uri, strlen(BASE_URL));
}
$request_uri = '/' . trim($request_uri, '/');
if ($request_uri === '') {
    $request_uri = '/';
}

// Mapeamento de rotas para [Controller, método]
$routes = [
    '/' => ['DashboardController', 'index'], // Redireciona ou mostra o painel
    '/login' => ['AuthController', 'showLoginForm'],
    '/login/do' => ['AuthController', 'login'], // Rota para o POST do login
    '/logout' => ['AuthController', 'logout'],
    '/dashboard' => ['DashboardController', 'index'],
    '/ponto/registrar' => ['PontoController', 'registrar'],
    '/ponto/meu_historico' => ['PontoController', 'meuHistorico'],
    '/ponto/calendario' => ['PontoController', 'calendario'],
    '/ponto/calendarioJson' => ['PontoController', 'calendarioJson'],
    '/admin/relatorio' => ['PontoController', 'relatorioGeral'],
    '/admin/users' => ['UserController', 'listUsers'],
    '/admin/users/create' => ['UserController', 'showCreateForm'],
    '/admin/users/store' => ['UserController', 'store'],
    '/admin/users/edit' => ['UserController', 'showEditForm'], // Espera ?id=
    '/admin/users/update' => ['UserController', 'update'],
    '/admin/users/delete' => ['UserController', 'delete'], // Espera ?id=
];

// Função de roteamento
function route($uri, $routes) {
    if (array_key_exists($uri, $routes)) {
        list($controller, $method) = $routes[$uri];

        // Verifica se o arquivo do controller existe antes de tentar instanciar
        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller não encontrado: {$controller}");
        }

        $controllerInstance = new $controller();

        if (method_exists($controllerInstance, $method)) {
            $controllerInstance->$method();
        } else {
            throw new Exception("Método não encontrado: {$method} em {$controller}");
        }
    } else {
        // Página não encontrada
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
    }
}

try {
    route($request_uri, $routes);
} catch (Exception $e) {
    // Em um ambiente de produção, você logaria o erro em vez de exibi-lo.
    // Para depuração, é útil ver a mensagem.
    error_log($e->getMessage());
    http_response_code(500);
    echo "<h1>Erro 500 - Erro Interno do Servidor</h1>";
    echo "<p>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>";
    // echo "<p>Detalhes: " . htmlspecialchars($e->getMessage()) . "</p>"; // Descomente para depurar
}