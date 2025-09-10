-- Migration para criar a tabela de resumo mensal de horas
-- Esta tabela armazena os totais calculados por mês para otimizar as consultas
-- e consolidar o banco de horas do usuário.

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Comentário:
-- A coluna `mes_ano` no formato 'YYYY-MM' permite agrupar e consultar facilmente os dados mensais.
-- O `banco_horas_saldo` é o resultado de (total_horas_trabalhadas - total_horas_esperadas).
-- A chave única em `(usuario_id, mes_ano)` garante que haja apenas um registro de resumo por usuário por mês.
