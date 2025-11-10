<?php
require_once __DIR__ . '/../config/Database.php';

class ReservaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $rutaId, $plazas) {
        $sql = "INSERT INTO reservas (user_id, ruta_id, plazas) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $userId, $rutaId, $plazas);
        return $stmt->execute();
    }

    public function getByRuta($rutaId) {
        $sql = "SELECT * FROM reservas WHERE ruta_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $rutaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserReservations($userId) {
        $sql = "SELECT r.*, hr.origen, hr.desti, hr.data_ruta FROM reservas r
                JOIN horaris_rutes hr ON r.ruta_id = hr.id
                WHERE r.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getReservedSeats($rutaId) {
        $sql = "SELECT SUM(plazas) as total_reservadas FROM reservas WHERE ruta_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $rutaId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total_reservadas'] ?? 0;
    }
}
