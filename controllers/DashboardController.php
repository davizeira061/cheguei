<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ponto.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /cheguei/login');
    exit;
}

$pdo = getDbConnection();
$userModel = new User($pdo);
$pontoModel = new Ponto($pdo);

// Lógica para exibir informações do dashboard (ex: último ponto, etc.)
// Para o colaborador: ver seu último ponto
// Para o administrador: talvez um resumo geral ou links para gerenciar usuários/relatórios

$user = $userModel->findById($_SESSION['user_id']);
$user_pontos = $pontoModel->getPontosByUsuario($_SESSION['user_id']);

include __DIR__ . '/../views/dashboard.php';