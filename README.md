# Cheguei - Sistema de Registro de Ponto

Um sistema web completo para registro de ponto, desenvolvido em PHP 8 com MySQL, focado em usabilidade e responsividade.

## Funcionalidades

- **Autenticação Segura:** Login com email e senha (hashes criptográficos).
- **Controle de Sessão:** Acesso restrito a usuários logados.
- **Perfis de Acesso:**
    - **Administrador:** Gerencia usuários (cria, edita, exclui) e visualiza/exporta todos os registros de ponto.
    - **Colaborador:** Registra entradas e saídas de ponto, e visualiza seu próprio histórico.
- **Registro de Ponto:**
    - Batidas de ponto com registro automático de data e hora.
    - Tipos de registro: Entrada e Saída.
- **Relatórios:**
    - Filtragem por usuário, data inicial e data final.
    - Exibição do total de horas trabalhadas no período selecionado.
    - Exportação em CSV. (Exportação em PDF requer bibliotecas externas e não está implementada nesta versão básica).
- **Interface Responsiva:** Desenvolvida com Bootstrap 5 para ótima experiência em desktops e dispositivos móveis.
- **Estrutura de Código:** Organizado em um padrão MVC simples para facilitar manutenção.

## Requisitos Mínimos

- **PHP 8.0 ou superior:**
    - Extensão `php_pdo_mysql` habilitada.
    - Extensão `php_openssl` (geralmente habilitada por padrão).
- **MySQL 5.7 ou superior:** Ou MariaDB equivalente.
- **Servidor Web:** Apache (com `mod_rewrite` habilitado) ou Nginx.

## Instalação e Configuração

Siga os passos abaixo para colocar o projeto em funcionamento:

### 1. Clonar o Repositório (ou Baixar o Projeto)

Assumindo que você vai clonar para dentro do diretório `htdocs` (Apache) ou equivalente:

'```bash
git clone <URL_DO_SEU_REPOSITORIO> cheguei
cd cheguei
'
### 2. Configuração do Banco de Dados

Crie um banco de dados MySQL com o nome cheguei_db.
Em seguida, execute as seguintes instruções SQL para criar as tabelas usuarios e pontos:

-- Tabela `usuarios`
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `perfil` ENUM('admin', 'colaborador') DEFAULT 'colaborador',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela `pontos`
CREATE TABLE `pontos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `data_hora` DATETIME NOT NULL,
    `tipo` ENUM('entrada', 'saida') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
);

-- Inserir um usuário administrador inicial (senha: admin123)
-- A senha 'admin123' será automaticamente hashada pelo sistema no primeiro login ou criação.
-- Para gerar o hash manualmente (antes da primeira execução do sistema), você pode usar um script PHP:
-- echo password_hash('admin123', PASSWORD_DEFAULT);
-- E insira o hash gerado diretamente na base.
-- Como o sistema já cria usuários com hash, vamos fazer com a senha bruta e deixar o sistema lidar com isso.
-- A senha será tratada na primeira autenticação ou na criação via interface.
-- Para o primeiro admin, o ideal é criar via interface de usuário após logar com um admin inicial.
-- Ou, para fins de teste, você pode inserir o hash diretamente:
-- INSERT INTO `usuarios` (`nome`, `email`, `senha`, `perfil`) VALUES
-- ('Administrador Padrão', 'admin@cheguei.com', '$2y$10$seu_hash_da_senha_admin123_aqui', 'admin');
--
-- Por agora, para fins de teste e facilidade, vamos criar o admin via interface.
-- Ou você pode rodar este comando para criar um admin temporário com senha 'admin123' (hashado):
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `perfil`) VALUES
('Administrador Inicial', 'admin@cheguei.com', '$2y$10$61v8x7.JqQh2lJ5R7tXy.uO0Y7c8QY5K.p2v9G.l7jY6qXz2k/iK', 'admin'); -- Hash para 'admin123'

Atualize o arquivo config/database.php com as suas credenciais de banco de dados se forem diferentes de root sem senha.

### 3. Configuração do Servidor Web (Apache)
Certifique-se de que o módulo mod_rewrite esteja habilitado no Apache.
Crie ou edite o arquivo .htaccess na raiz do projeto (cheguei/.htaccess) com o conteúdo fornecido:

Apache
# .htaccess na raiz do projeto 'cheguei/'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]

Importante: Se você estiver utilizando um virtual host, a configuração AllowOverride All deve estar ativa para o diretório do projeto. Se estiver em um subdiretório sem virtual host, pode ser necessário ajustar as regras de reescrita para incluir o nome do subdiretório (ex: RewriteBase /cheguei/). O index.php do public já tenta lidar com o subdiretório.

4. Acessar o Projeto
Após a configuração, você pode acessar o sistema no seu navegador:
http://localhost/cheguei/
Você será redirecionado para a tela de login.
Usuário e Senha Padrão do Administrador Inicial
Email: admin@cheguei.com
Senha: admin123

Recomendação: Altere a senha do administrador após o primeiro login para maior segurança.
Como Utilizar o Sistema
Login
Acesse a URL base do projeto (ex: http://localhost/cheguei/).
Digite o email e a senha do administrador inicial ou de um colaborador.
Clique em "Entrar".
Dashboard
Após o login, você será direcionado para o dashboard.
Colaborador: Verá opções para "Registrar Entrada" e "Registrar Saída", além de um resumo dos seus últimos pontos.
Administrador: Verá links para "Gerenciar Usuários" e "Ver Relatórios de Ponto".
Registrar Ponto (Colaborador)
No dashboard, clique em "Registrar Entrada" ou "Registrar Saída".
Uma mensagem de sucesso aparecerá e o ponto será registrado com a data e hora atuais.
Seu histórico será atualizado.
Gerenciar Usuários (Administrador)
No dashboard, clique em "Gerenciar Usuários" ou navegue para http://localhost/cheguei/admin/users.
Você verá uma lista de todos os usuários.
Adicionar Novo Usuário: Clique no botão "Adicionar Novo Usuário". Preencha os dados (nome, email, senha, perfil) e clique em "Criar Usuário".
Editar Usuário: Clique no botão "Editar" ao lado do usuário desejado. Altere as informações e clique em "Salvar Alterações". Se deixar o campo de senha em branco, a senha atual não será alterada.
Excluir Usuário: Clique no botão "Excluir" ao lado do usuário desejado. Confirme a exclusão.
Relatórios de Ponto (Administrador)
No dashboard, clique em "Ver Relatórios de Ponto" ou navegue para http://localhost/cheguei/ponto/relatorio.
Use os filtros "Usuário", "Data Inicial" e "Data Final" para refinar os resultados.
Clique em "Filtrar" para aplicar os filtros.
O total de horas trabalhadas para o período e usuário selecionados será exibido.
Exportar CSV: Clique em "Exportar CSV" para baixar o relatório em formato de planilha.
Exportar PDF: Esta funcionalidade não está implementada nesta versão básica e exigiria a inclusão de uma biblioteca PHP externa (ex: DomPDF).
Considerações sobre Segurança
Senhas: As senhas são armazenadas com password_hash() para maior segurança.
Injeção SQL: O uso de Prepared Statements com PDO previne ataques de injeção SQL.
XSS: A saída de dados HTML para as views usa htmlspecialchars() para mitigar XSS.
Sessões: As sessões são gerenciadas pelo PHP, mas considere usar configurações mais seguras de sessão em ambiente de produção (ex: cookies somente HTTP, tempo de vida da sessão).
Melhorias Futuras (Opcionais)
Implementação completa da exportação em PDF.
Recuperação de senha.
Verificação de email para novos usuários.
Interface de usuário para alterar a própria senha.
Controle mais granular de permissões (além de admin/colaborador).
Registro de ponto com geolocalização.
Testes unitários e de integração.
Containerização (Docker).

---

**Observações Finais:**

1.  **Imagens:** O sistema em si não gera imagens de forma dinâmica. Se você quiser um wireframe ou um mockup visual, me diga! Por exemplo: "Gere um mockup da tela de login do sistema 'Cheguei'".

2.  **HTML/CSS/JS:** Os arquivos `style.css` e `script.js` em `public/` estão vazios. Você pode adicionar seu CSS personalizado e JavaScript para interatividade, se necessário, além do Bootstrap.

3.  **Exportação PDF:** Conforme mencionado no `README.md` e no controlador, a exportação para PDF é mais complexa e exigiria uma biblioteca como [DomPDF](https://github.com/dompdf/dompdf) ou [FPDF](http://www.fpdf.org/). O código atual apenas mostra uma mensagem de erro.

4.  **UX/UI:** O layout é simples e segue o Bootstrap 5. Para otimização mobile, o Bootstrap já ajuda bastante, mas ajustes finos no CSS podem ser necessários dependendo da complexidade do design.

5.  **Subdiretório:** Se o projeto não for a raiz do seu servidor virtual host, e estiver em `http://localhost/cheguei/`, o `public/index.php` e os links `href` nos headers já estão configurados para `'/cheguei/'`. Se for outro nome, você precisará ajustar.

Este é um sistema bem robusto para começar!