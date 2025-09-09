<?php
// views/403.php
// Simples página de acesso negado.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - Cheguei</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>403 - Acesso Negado</h1>
        <p>Você não tem permissão para acessar esta página.</p>
        <a href="<?= BASE_URL ?>/dashboard">Voltar para o Painel</a>
    </div>
</body>
</html>
