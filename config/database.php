<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cheguei_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDbConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão com o banco de dados: " . $e->getMessage());
    }
}