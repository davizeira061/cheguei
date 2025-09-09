<?php

class Ponto {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registerPonto($usuario_id, $tipo, $ip_address, $location_source, $latitude, $longitude, $location) {
        $sql = "INSERT INTO pontos (usuario_id, data_hora, tipo, ip_address, location_source, latitude, longitude, location)
                VALUES (:usuario_id, NOW(), :tipo, :ip_address, :location_source, :latitude, :longitude, :location)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'usuario_id' => $usuario_id,
            'tipo' => $tipo,
            'ip_address' => $ip_address,
            'location_source' => $location_source,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => $location
        ]);
    }

    public function getPontosByUsuario($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pontos WHERE usuario_id = :usuario_id ORDER BY data_hora DESC");
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll();
    }

    public function getAllPontos($start_date = null, $end_date = null, $usuario_id = null) {
        $sql = "SELECT p.*, u.nome AS usuario_nome FROM pontos p JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
        $params = [];

        if ($usuario_id) {
            $sql .= " AND p.usuario_id = :usuario_id";
            $params['usuario_id'] = $usuario_id;
        }
        if ($start_date) {
            $sql .= " AND p.data_hora >= :start_date";
            $params['start_date'] = $start_date . " 00:00:00";
        }
        if ($end_date) {
            $sql .= " AND p.data_hora <= :end_date";
            $params['end_date'] = $end_date . " 23:59:59";
        }

        $sql .= " ORDER BY p.data_hora DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalHorasTrabalhadas($usuario_id, $start_date, $end_date) {
        $sql = "
            SELECT
                SUM(TIME_TO_SEC(TIMEDIFF(saida.data_hora, entrada.data_hora))) AS total_segundos
            FROM
                pontos AS entrada
            JOIN
                pontos AS saida ON entrada.usuario_id = saida.usuario_id
            WHERE
                entrada.tipo = 'entrada'
                AND saida.tipo = 'saida'
                AND entrada.data_hora < saida.data_hora
                AND DATE(entrada.data_hora) = DATE(saida.data_hora)
                AND entrada.usuario_id = :usuario_id
                AND entrada.data_hora >= :start_date
                AND saida.data_hora <= :end_date
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'start_date' => $start_date . " 00:00:00",
            'end_date' => $end_date . " 23:59:59"
        ]);
        $result = $stmt->fetch();
        return $result['total_segundos'] ?? 0;
    }

    // Função para calcular o total de horas de um conjunto de pontos
    public function calculateTotalHoursFromRecords($records) {
        $total_seconds = 0;
        $entrada_time = null;

        foreach ($records as $record) {
            if ($record['tipo'] == 'entrada') {
                $entrada_time = strtotime($record['data_hora']);
            } elseif ($record['tipo'] == 'saida' && $entrada_time !== null) {
                $saida_time = strtotime($record['data_hora']);
                $total_seconds += ($saida_time - $entrada_time);
                $entrada_time = null; // Reset para a próxima entrada
            }
        }
        return $total_seconds;
    }
}