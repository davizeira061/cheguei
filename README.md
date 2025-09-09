# Cheguei - Sistema de Registro de Ponto (Refatorado)

Um sistema web completo para registro de ponto, desenvolvido em PHP 8 com MySQL, focado em usabilidade, segurança e código limpo. Esta versão foi refatorada para um padrão MVC mais claro e robusto.

## Funcionalidades

- **Autenticação Segura:** Login com `password_hash()` e `password_verify()`.
- **Controle de Acesso por Perfil:**
    - **Administrador:** Gerencia usuários (CRUD) e visualiza relatórios completos de ponto.
    - **Colaborador:** Registra seus pontos e visualiza seu próprio histórico.
- **Registro de Ponto Detalhado:**
    - Tipos de registro: Entrada, Saída para Almoço, Retorno do Almoço e Saída.
- **Relatórios Avançados:**
    - Filtragem por usuário e período.
    - Cálculo de horas trabalhadas.
    - Exportação de dados em formato CSV.
- **Interface Responsiva:** Construída com Bootstrap 5.
- **Estrutura de Código Organizada:**
    - Padrão MVC (Model-View-Controller) simplificado.
    - Roteamento centralizado em `public/index.php`.
    - Controladores de classe (`AuthController`, `UserController`, etc.).
    - `BaseController` para lógica comum (autenticação, renderização de views).

## Requisitos

- **PHP 8.0+** (com extensões `pdo_mysql`, `session`).
- **MySQL 5.7+** ou MariaDB.
- **Servidor Web** (Apache com `mod_rewrite` ou Nginx).

---

## Instalação Rápida

### 1. Clone o Repositório

```bash
git clone <URL_DO_REPOSITORIO> cheguei
cd cheguei
```

### 2. Crie o Banco de Dados

Crie um banco de dados no MySQL com o nome `cheguei_db` (ou outro de sua preferência).

Execute o script SQL abaixo para criar as tabelas `usuarios` e `pontos`.

```sql
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','colaborador') DEFAULT 'colaborador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pontos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('entrada','saida_almoco','retorno_almoco','saida') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pontos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Configure a Aplicação

1.  **Conexão com o Banco:** No arquivo `config/database.php`, adicione as credenciais do seu banco de dados.
2.  **URL Base:** Abra `config/app.php` e ajuste a constante `BASE_URL`.
    -   Se o projeto roda em `http://localhost/cheguei`, a `BASE_URL` deve ser `'/cheguei'`.
    -   Se o projeto roda na raiz `http://localhost`, a `BASE_URL` deve ser `'/'`.

### 4. Crie um Usuário Administrador

Para o primeiro acesso, você precisa de um usuário `admin`. Execute o comando SQL abaixo para criar um.

**Comando para criar usuário `admin@example.com` com senha `admin123`:**

```sql
INSERT INTO `usuarios` (nome, email, senha, perfil)
VALUES ('Admin', 'admin@example.com', '$2y$10$3g.OU.F3Yg4fS7Yg5y3M/e5bZ.PCz.Z5C.qB3H/g5w8Wj.5i/E.C.', 'admin');
```
*Este hash foi gerado para a senha `admin123`.*

### 5. Configure o Servidor Web (Apache)

A forma recomendada de configurar o servidor é apontar o `DocumentRoot` do seu Virtual Host diretamente para a pasta `public/`. Isso garante que nenhum arquivo sensível fora do diretório `public` seja acessível pela web.

Com o `DocumentRoot` configurado para `public/`, crie um arquivo `.htaccess` dentro dessa pasta (`cheguei/public/.htaccess`) com o seguinte conteúdo:

```apache
RewriteEngine On

# Se o projeto estiver em um subdiretório, descomente e ajuste a linha abaixo
# RewriteBase /cheguei/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

*Certifique-se que o `mod_rewrite` do Apache está habilitado e que a diretiva `AllowOverride All` está ativa para o diretório do projeto, para que o `.htaccess` seja lido.*

### 6. Teste o Login

1.  Acesse a URL do seu projeto (ex: `http://localhost/cheguei`).
2.  Você será redirecionado para a página de login.
3.  Use as credenciais do admin que você criou:
    -   **Email:** `admin@example.com`
    -   **Senha:** `admin123`
4.  Após o login, você verá o painel de administrador.

---

## Solução de Problemas Comuns

- **Erro 500 ou Página em Branco:**
    - Verifique os logs de erro do Apache/PHP.
    - Certifique-se que as permissões de arquivo/diretório estão corretas.
    - Garanta que as extensões do PHP (`pdo_mysql`) estão habilitadas.
- **Erro 404 (Não Encontrado) em todas as páginas, exceto a inicial:**
    - O `mod_rewrite` do Apache não está habilitado ou o `.htaccess` não está sendo lido. Verifique a configuração `AllowOverride` do seu Virtual Host.
    - A `BASE_URL` em `config/app.php` pode estar incorreta.
    - O `DocumentRoot` do seu servidor não está apontando para a pasta `public`.
- **Login não funciona (sem mensagem de erro):**
    - Verifique se as sessões estão funcionando corretamente no seu ambiente PHP.
    - A senha no banco de dados pode não corresponder ao hash esperado. Use o comando SQL acima para garantir.
- **Links ou CSS quebrados:**
    - A `BASE_URL` em `config/app.php` está incorreta e não corresponde à URL que você usa no navegador.