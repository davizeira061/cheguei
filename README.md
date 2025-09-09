# Cheguei - Sistema de Registro de Ponto

Este documento fornece uma visão geral do sistema "Cheguei", com foco na resolução de problemas de login e na configuração correta do ambiente.

## 1. Estrutura do Banco de Dados

Para que o sistema funcione corretamente, as tabelas no seu banco de dados MySQL devem ter a seguinte estrutura.

### Tabela `usuarios`

**Importante:** A coluna `senha` **deve** ser do tipo `VARCHAR(255)` para garantir que o hash da senha nunca seja truncado.

**Importante:** A coluna `senha` **deve** ser do tipo `VARCHAR(255)`. O `password_hash()` do PHP gera hashes com cerca de 60 caracteres, mas o comprimento pode aumentar em futuras versões do PHP. Usar `VARCHAR(255)` é a recomendação oficial para garantir que o hash nunca seja truncado.

```sql
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL, -- Essencial que seja VARCHAR(255)
  `perfil` enum('admin','colaborador') DEFAULT 'colaborador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Tabela `pontos`

Esta tabela armazena os registros de ponto. A coluna `tipo` foi atualizada para incluir os registros de almoço.

```sql
CREATE TABLE `pontos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('entrada','saida_almoco','retorno_almoco','saida') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pontos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Se você já tinha a tabela criada, pode alterá-la com o seguinte comando:
```sql
ALTER TABLE pontos MODIFY COLUMN tipo ENUM('entrada','saida_almoco','retorno_almoco','saida') NOT NULL;
```

## 2. Como Funciona o Login Seguro

O processo de login foi projetado para ser seguro, seguindo as melhores práticas do PHP.

1.  **Criação do Usuário:** Quando um novo usuário é criado (seja pelo sistema ou por um script), a senha fornecida **não é salva em texto puro**. Ela é processada pela função `password_hash()`, que a transforma em um hash criptográfico seguro.

2.  **Tentativa de Login:** Quando um usuário tenta fazer login:
    *   O sistema primeiro busca o usuário no banco de dados pelo `email`.
    *   Se o usuário existe, o sistema usa a função `password_verify()` para comparar a senha digitada no formulário com o hash que está salvo no banco de dados.
    *   `password_verify()` é uma função segura que sabe como comparar uma string de texto puro com um hash gerado por `password_hash()`.
    *   Se a comparação for bem-sucedida, o login é autorizado. Caso contrário, é negado.

## 3. Como Criar um Usuário Administrador (Via Script)

Para facilitar a configuração inicial, foi criado um script que insere um usuário administrador com dados padrão. Para executá-lo, siga os passos:

1.  **Configure o Banco de Dados:** Certifique-se de que o arquivo `config/database.php` contém as credenciais corretas do seu banco de dados.

2.  **Execute o Script pela Linha de Comando:** Abra seu terminal, navegue até a pasta raiz do projeto e execute o seguinte comando:

    ```bash
    php scripts/create_admin.php
    ```

3.  **Verifique a Saída:** O script irá confirmar a criação do usuário ou informará se um usuário com o mesmo email já existe.

    **Credenciais do Admin Padrão:**
    *   **Email:** `admin@example.com`
    *   **Senha:** `123456`

## 4. Resolvendo Erros Comuns de Login

Se você está recebendo a mensagem "Email ou senha inválidos" mesmo com as credenciais corretas, aqui estão as causas mais comuns e como resolvê-las.

### Causa nº 1: Hash da Senha Truncado (O Mais Provável)

-   **Problema:** A coluna `senha` na sua tabela `usuarios` é muito curta (ex: `VARCHAR(60)`). Quando o `password_hash()` gera uma string longa, o banco de dados a corta para caber na coluna. O hash armazenado fica incompleto e `password_verify()` nunca encontrará uma correspondência.
-   **Solução:** Altere a estrutura da sua tabela para que a coluna `senha` seja `VARCHAR(255)`.
    ```sql
    ALTER TABLE usuarios MODIFY COLUMN senha VARCHAR(255) NOT NULL;
    ```
    Depois de alterar a coluna, você precisará **recriar o usuário** (seja pelo script ou pela interface) para que a senha seja hasheada e armazenada corretamente no novo campo.

### Causa nº 2: Senha em Texto Puro no Banco

-   **Problema:** Você inseriu um usuário diretamente no banco de dados com a senha em texto puro (ex: '123456'). O `password_verify()` espera um hash e não saberá como comparar com texto puro.
-   **Solução:** Nunca insira senhas em texto puro. Use sempre o script `create_admin.php` ou a interface do sistema para criar usuários, pois eles garantem que a senha seja processada com `password_hash()`.

### Causa nº 3: Espaços Extras ou Problemas de Charset

-   **Problema:** Ao copiar e colar o email ou a senha, espaços extras podem ter sido inseridos no banco de dados ou no formulário de login.
-   **Solução:** Verifique os dados no banco de dados em busca de espaços no início ou no fim dos emails. No código, o uso de `trim()` pode ajudar a mitigar isso, embora a versão atual do código não o faça explicitamente. A configuração `charset=utf8mb4` no arquivo `config/database.php` já ajuda a prevenir problemas de codificação de caracteres.