<?php
require_once __DIR__ . '/../config/Database.php';

class ValoracionModel {
    private $db;
    private $table = 'valoraciones';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->createTable();
    }

    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ruta_id INT NOT NULL,
            user_id INT NOT NULL,
            conductor_id INT NOT NULL,
            puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
            comentario TEXT,
            fecha_valoracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ruta (ruta_id),
            INDEX idx_user (user_id),
            INDEX idx_conductor (conductor_id),
            UNIQUE KEY unique_valoracion (ruta_id, user_id),
            FOREIGN KEY (ruta_id) REFERENCES horaris_rutes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE,
            FOREIGN KEY (conductor_id) REFERENCES usuaris(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->db->query($sql)) {
            error_log("Error creando tabla valoraciones: " . $this->db->error);
        }
    }

    public function create($rutaId, $userId, $conductorId, $puntuacion, $comentario = '') {
        $sql = "INSERT INTO {$this->table} (ruta_id, user_id, conductor_id, puntuacion, comentario) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                puntuacion = VALUES(puntuacion), 
                comentario = VALUES(comentario),
                fecha_valoracion = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando statement: " . $this->db->error);
            return false;
        }

        // Si puntuacion es null, usar 'i' y pasar null, si no, pasar el valor
        if ($puntuacion === null) {
            $stmt->bind_param("iiibs", $rutaId, $userId, $conductorId, $puntuacion, $comentario);
        } else {
            $stmt->bind_param("iiiis", $rutaId, $userId, $conductorId, $puntuacion, $comentario);
        }
        return $stmt->execute();
    }

    public function getByRuta($rutaId) {
        $sql = "SELECT v.*, u.nom, u.cognoms 
                FROM {$this->table} v
                JOIN usuaris u ON v.user_id = u.id
                WHERE v.ruta_id = ?
                ORDER BY v.fecha_valoracion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $rutaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPromedioByRuta($rutaId) {
        $sql = "SELECT AVG(puntuacion) as promedio, COUNT(*) as total_valoraciones
                FROM {$this->table} 
                WHERE ruta_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $rutaId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return [
            'promedio' => $result['promedio'] ? round($result['promedio'], 1) : 0,
            'total' => $result['total_valoraciones']
        ];
    }

    public function getPromedioByConductor($conductorId) {
        $sql = "SELECT AVG(puntuacion) as promedio, COUNT(*) as total_valoraciones
                FROM {$this->table} 
                WHERE conductor_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $conductorId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return [
            'promedio' => $result['promedio'] ? round($result['promedio'], 1) : 0,
            'total' => $result['total_valoraciones']
        ];
    }

    public function hasUserRated($rutaId, $userId) {
        $sql = "SELECT id FROM {$this->table} WHERE ruta_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $rutaId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getUserRating($rutaId, $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE ruta_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $rutaId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getValoracionesPendientes($userId) {
        $sql = "SELECT DISTINCT hr.id, hr.origen, hr.desti, hr.data_ruta, hr.hora_inici, hr.hora_fi, hr.user_id as conductor_id,
                   u.nom as conductor_nom, u.cognoms as conductor_cognoms,
                   r.id as reserva_id
            FROM reservas r
            JOIN horaris_rutes hr ON r.ruta_id = hr.id
            JOIN usuaris u ON hr.user_id = u.id
            LEFT JOIN {$this->table} v ON (v.ruta_id = hr.id AND v.user_id = ?)
            WHERE r.user_id = ? 
            AND v.id IS NULL
            AND hr.user_id != ?
            ORDER BY hr.data_ruta DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTopConductores($limit = 10) {
        $sql = "SELECT v.conductor_id, u.nom, u.cognoms, 
                       AVG(v.puntuacion) as promedio, 
                       COUNT(v.id) as total_valoraciones
                FROM {$this->table} v
                JOIN usuaris u ON v.conductor_id = u.id
                GROUP BY v.conductor_id
                HAVING total_valoraciones >= 3
                ORDER BY promedio DESC, total_valoraciones DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function delete($id, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $id, $userId);
        return $stmt->execute();
    }

    public function getValoracionesDadasByUser($userId) {
        $sql = "SELECT v.*, hr.origen, hr.desti, hr.data_ruta, hr.hora_inici, hr.hora_fi,
                       u.nom as conductor_nom, u.cognoms as conductor_cognoms
                FROM {$this->table} v
                JOIN horaris_rutes hr ON v.ruta_id = hr.id
                JOIN usuaris u ON v.conductor_id = u.id
                WHERE v.user_id = ?
                ORDER BY v.fecha_valoracion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByConductor($conductorId) {
        $sql = "SELECT v.*, u.nom, u.cognoms 
                FROM {$this->table} v
                JOIN usuaris u ON v.user_id = u.id
                WHERE v.conductor_id = ?
                ORDER BY v.fecha_valoracion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $conductorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>