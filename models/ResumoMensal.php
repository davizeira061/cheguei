<?php

class ResumoMensal {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca um resumo mensal para um usuário e um mês/ano específicos.
     *
     * @param int $usuario_id
     * @param string $mes_ano Formato 'YYYY-MM'
     * @return array|false
     */
    public function getResumo(int $usuario_id, string $mes_ano) {
        $sql = "SELECT * FROM resumo_mensal WHERE usuario_id = :usuario_id AND mes_ano = :mes_ano";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'mes_ano' => $mes_ano
        ]);
        return $stmt->fetch();
    }

    /**
     * Salva ou atualiza um resumo mensal no banco de dados.
     * Usa ON DUPLICATE KEY UPDATE para inserir ou atualizar conforme necessário.
     *
     * @param array $data Os dados a serem salvos.
     * @return bool
     */
    public function saveResumo(array $data): bool {
        $sql = "
            INSERT INTO resumo_mensal (usuario_id, mes_ano, total_horas_trabalhadas, banco_horas_saldo)
            VALUES (:usuario_id, :mes_ano, :total_horas_trabalhadas, :banco_horas_saldo)
            ON DUPLICATE KEY UPDATE
                total_horas_trabalhadas = VALUES(total_horas_trabalhadas),
                banco_horas_saldo = VALUES(banco_horas_saldo),
                updated_at = NOW()
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $data['usuario_id'],
            'mes_ano' => $data['mes_ano'],
            'total_horas_trabalhadas' => $data['total_horas_trabalhadas'],
            'banco_horas_saldo' => $data['banco_horas_saldo']
        ]);
    }
}
