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

    /**
     * Busca todos os pontos com base nos filtros fornecidos.
     * A ordenação é importante para o serviço de cálculo de horas.
     */
    public function getAllPontos($start_date = null, $end_date = null, $usuario_id = null, $order = 'DESC') {
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

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY p.data_hora " . $order;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}