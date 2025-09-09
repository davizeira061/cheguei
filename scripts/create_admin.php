<?php
// scripts/create_admin.php

// Este script deve ser executado a partir da linha de comando:
// php scripts/create_admin.php

require_once __DIR__ . '/../config/database.php';

// --- Dados do Administrador ---
$nome = 'Administrador';
$email = 'admin@example.com';
$senha_texto_puro = '123456';
$perfil = 'admin';
// -----------------------------

echo "Iniciando criação do usuário administrador...\n";

try {
    $pdo = getDbConnection();

    // 1. Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo "ERRO: O email '{$email}' já existe no banco de dados.\n";
        exit(1);
    }

    // 2. Gerar o hash da senha
    $senha_hash = password_hash($senha_texto_puro, PASSWORD_DEFAULT);
    if (!$senha_hash) {
        echo "ERRO: Falha ao gerar o hash da senha.\n";
        exit(1);
    }
    echo "Hash da senha gerado com sucesso.\n";

    // 3. Inserir o novo usuário no banco de dados
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)"
    );

    $success = $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senha_hash,
        'perfil' => $perfil
    ]);

    if ($success) {
        echo "--------------------------------------------------------\n";
        echo "Usuário administrador criado com sucesso!\n";
        echo "  Nome: {$nome}\n";
        echo "  Email: {$email}\n";
        echo "  Senha: {$senha_texto_puro}\n";
        echo "--------------------------------------------------------\n";
    } else {
        echo "ERRO: Falha ao inserir o usuário no banco de dados.\n";
    }

} catch (PDOException $e) {
    echo "ERRO DE BANCO DE DADOS: " . $e->getMessage() . "\n";
    exit(1);
}
