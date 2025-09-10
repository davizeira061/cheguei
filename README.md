# Cheguei - Sistema de Registro de Ponto (Versão 2.0)

Este documento fornece uma visão geral do sistema "Cheguei", com foco nas novas funcionalidades implementadas e na configuração correta do ambiente.

## Novas Funcionalidades

A versão 2.0 do sistema introduz melhorias significativas para o cálculo de horas e visualização de dados:

1.  **Cálculo Automático de Horas:** O sistema agora calcula automaticamente as horas trabalhadas por dia, considerando entradas, saídas e pausas para almoço. Registros incompletos (ex: entrada sem saída) são identificados e não são somados no total.
2.  **Banco de Horas:** O saldo de horas (positivas ou negativas) é calculado diariamente e consolidado por período.
3.  **Relatórios Avançados:** Uma nova página de relatórios (disponível para administradores) permite filtrar os registros por usuário, intervalo de datas e situação (completo/incompleto). Os dados são apresentados em uma tabela interativa com opções de exportação para **CSV e PDF**.
4.  **Calendário de Marcações:** Uma nova visualização em calendário permite que os usuários vejam suas marcações mensais. Cada dia exibe o total de horas e o status. Administradores podem filtrar para visualizar o calendário de qualquer funcionário.
5.  **Histórico Melhorado:** A página "Meu Histórico" para o usuário final foi aprimorada com as mesmas funcionalidades de filtro e cálculo da página de relatórios.

## Estrutura do Banco de Dados

Para que o sistema funcione corretamente, as tabelas no seu banco de dados MySQL devem ter a seguinte estrutura.

### Tabela `usuarios`
(A estrutura permanece a mesma)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabela `pontos` (Estrutura Atualizada)
A tabela `pontos` foi estendida para incluir dados de geolocalização.
```sql
CREATE TABLE `pontos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('entrada','saida_almoco','retorno_almoco','saida') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `location_source` varchar(20) DEFAULT 'ip',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pontos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **NOVO:** Tabela `resumo_mensal`
É **necessário** criar esta nova tabela para armazenar os cálculos mensais e o banco de horas.

**Execute o script de migration:** Para criar a tabela, execute o conteúdo do arquivo `scripts/migration_resumo_mensal.sql` no seu banco de dados.

```sql
-- Conteúdo de scripts/migration_resumo_mensal.sql
CREATE TABLE `resumo_mensal` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `mes_ano` VARCHAR(7) NOT NULL COMMENT 'Formato YYYY-MM',
  `total_horas_trabalhadas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de segundos trabalhados no mês',
  `banco_horas_saldo` INT(11) NOT NULL DEFAULT 0 COMMENT 'Saldo de segundos do banco de horas no mês',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usuario_mes` (`usuario_id`, `mes_ano`),
  CONSTRAINT `fk_resumo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Dependências Frontend

As novas funcionalidades de relatório e calendário utilizam bibliotecas Javascript, que são carregadas via CDN nos arquivos de cabeçalho e rodapé da aplicação. Nenhuma instalação manual é necessária.

-   **jQuery**
-   **DataTables:** Para tabelas interativas e exportação (CSV/PDF).
-   **FullCalendar:** Para a visualização em calendário.

## Como Usar

1.  **Relatórios (Admin):** Navegue até a página "Relatórios". Selecione um usuário e um período para ver o detalhamento diário de horas, o total trabalhado e o saldo do banco de horas. Use os botões para exportar os dados.
2.  **Calendário:** Navegue até "Calendário". Admins podem selecionar um usuário para visualizar. Clique em um dia para ver os detalhes das marcações.
3.  **Meu Histórico (Colaborador):** A página "Meu Histórico" agora funciona como um relatório pessoal, com as mesmas funcionalidades de filtro e cálculo.

---
*(O conteúdo sobre como criar um admin e resolver problemas de login do README anterior ainda é válido e pode ser consultado no histórico do Git se necessário.)*