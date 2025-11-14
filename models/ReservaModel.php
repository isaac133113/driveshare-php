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

    public function getUserReservationsWithDetails($userId) {
        $sql = "SELECT r.*, hr.origen, hr.desti, hr.data_ruta, hr.hora_inici, hr.hora_fi, 
                       hr.vehicle, hr.comentaris, hr.precio_euros,
                       u.nom, u.cognoms, u.correu,
                       CASE 
                           WHEN hr.comentaris LIKE 'RESERVA_RAPIDA:%' THEN 'reserva_rapida'
                           ELSE 'reserva_normal'
                       END as tipo_reserva
                FROM reservas r
                JOIN horaris_rutes hr ON r.ruta_id = hr.id
                JOIN usuaris u ON hr.user_id = u.id
                WHERE r.user_id = ?
                ORDER BY hr.data_ruta DESC, hr.hora_inici ASC";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt === false) {
            error_log("Error preparing statement: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $userId);
        
        if (!$stmt->execute()) {
            error_log("Error executing statement: " . $stmt->error);
            return [];
        }
        
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
