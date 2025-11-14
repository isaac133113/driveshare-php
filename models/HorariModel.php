<?php
require_once __DIR__ . '/../helpers/DatabaseHelper.php';

class HorariModel {
    private $dbHelper;
    private $table = 'horaris_rutes';
    
    public function __construct() {
        $this->dbHelper = new DatabaseHelper();
    }
    
    public function getAllHoraris() {
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
            ORDER BY hr.data_ruta DESC, hr.hora_inici ASC
        ";
        $result = $this->dbHelper->executeQuery($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getHorarisByUserId($userId) {
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
            WHERE hr.user_id = ? 
            AND (hr.comentaris NOT LIKE 'RESERVA_RAPIDA:%' OR hr.comentaris IS NULL)
            ORDER BY hr.data_ruta DESC, hr.hora_inici ASC
        ";
        $result = $this->dbHelper->executeQuery($sql, [$userId], 'i');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getHorariById($id, $userId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $types = 'i';
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        $result = $this->dbHelper->executeQuery($sql, $params, $types);
        return $result ? $result->fetch_assoc() : false;
    }
    
    public function createHorari($data) {
        if (!$this->validateHorariData($data)) {
            return false;
        }
        
        return $this->dbHelper->executePrepared(
            "INSERT INTO {$this->table} (user_id, data_ruta, hora_inici, hora_fi, origen, desti, vehicle, comentaris) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], 
                $data['data_ruta'], 
                $data['hora_inici'], 
                $data['hora_fi'], 
                $data['origen'], 
                $data['desti'], 
                $data['vehicle'], 
                $data['comentaris']
            ],
            'isssssss'
        );
    }
    
    // public function updateHorari($id, $userId, $data) {
    //     if (!$this->validateHorariData($data)) {
    //         return false;
    //     }
        
    //     return $this->dbHelper->executePrepared(
    //         "UPDATE {$this->table} SET data_ruta = ?, hora_inici = ?, hora_fi = ?, origen = ?, desti = ?, vehicle = ?, comentaris = ? WHERE id = ? AND user_id = ?",
    //         [
    //             $data['data_ruta'], 
    //             $data['hora_inici'], 
    //             $data['hora_fi'], 
    //             $data['origen'], 
    //             $data['desti'], 
    //             $data['vehicle'], 
    //             $data['comentaris'],
    //             $id,
    //             $userId
    //         ],
    //         'sssssssii'
    //     );
    // }

    public function updateHorari($id, $userId, $data) {
        if (empty($data)) return false;

        $campos = [];
        $tipos = '';
        $valores = [];

        foreach ($data as $key => $value) {
            $campos[] = "$key = ?";
            $tipos .= is_int($value) ? 'i' : (is_double($value) ? 'd' : 's');
            $valores[] = $value;
        }

        // Agregar id y user_id al final
        $camposSql = implode(', ', $campos);
        $valores[] = $id;
        $valores[] = $userId;
        $tipos .= 'ii';

        $sql = "UPDATE horaris_rutes SET $camposSql WHERE id = ? AND user_id = ?";

        // Ejecutar con el helper pasando parámetros y tipos
        return $this->dbHelper->executePrepared($sql, $valores, $tipos);
    }

    public function deleteHorari($id, $userId) {
        return $this->dbHelper->executePrepared(
            "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?",
            [$id, $userId],
            'ii'
        );
    }
    
    public function searchHoraris($filters, $userId = null) {
        $whereConditions = [];
        $params = [];
        $types = '';
        
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
        ";
        
        if ($userId) {
            $whereConditions[] = "hr.user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        if (!empty($filters['date'])) {
            $whereConditions[] = "hr.data_ruta = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        
        if (!empty($filters['vehicle'])) {
            $whereConditions[] = "hr.vehicle LIKE ?";
            $params[] = '%' . $filters['vehicle'] . '%';
            $types .= 's';
        }
        
        if (!empty($filters['location'])) {
            $whereConditions[] = "(hr.origen LIKE ? OR hr.desti LIKE ?)";
            $params[] = '%' . $filters['location'] . '%';
            $params[] = '%' . $filters['location'] . '%';
            $types .= 'ss';
        }
        
        if (!empty($filters['user'])) {
            $whereConditions[] = "(u.nom LIKE ? OR u.cognoms LIKE ?)";
            $params[] = '%' . $filters['user'] . '%';
            $params[] = '%' . $filters['user'] . '%';
            $types .= 'ss';
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY hr.data_ruta DESC, hr.hora_inici ASC";
        
        $result = $this->dbHelper->executeQuery($sql, $params, $types);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getVehiclesList() {
        return [
            'Seat Ibiza' => ['precio' => 12, 'tipo' => 'Econòmic'],
            'Ford Focus' => ['precio' => 18, 'tipo' => 'Compacte'],
            'Tesla Model 3' => ['precio' => 35, 'tipo' => 'Premium Elèctric'],
            'BMW X5' => ['precio' => 45, 'tipo' => 'SUV Premium']
        ];
    }
    
    private function validateHorariData($data) {
        return !empty($data['user_id']) && 
               !empty($data['data_ruta']) && 
               !empty($data['hora_inici']) && 
               !empty($data['hora_fi']) && 
               !empty($data['origen']) && 
               !empty($data['desti']) && 
               !empty($data['vehicle']);
    }
    
    public function getHorarisStats($userId = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN data_ruta = CURDATE() THEN 1 END) as avui,
                    COUNT(CASE WHEN data_ruta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as setmana
                FROM {$this->table}";
        
        if ($userId) {
            $sql .= " WHERE user_id = ?";
            $result = $this->dbHelper->executeQuery($sql, [$userId], 'i');
        } else {
            $result = $this->dbHelper->executeQuery($sql);
        }
        
        return $result ? $result->fetch_assoc() : ['total' => 0, 'avui' => 0, 'setmana' => 0];
    }
    
    public function getLastInsertId() {
        return $this->dbHelper->getLastInsertId();
    }
    
    public function getUpcomingHoraris($userId, $days = 7) {
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
            WHERE hr.user_id = ? 
            AND hr.data_ruta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY hr.data_ruta ASC, hr.hora_inici ASC
        ";
        $result = $this->dbHelper->executeQuery($sql, [$userId, $days], 'ii');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getUserMostUsedVehicles($userId, $limit = 3) {
        $sql = "
            SELECT vehicle, COUNT(*) as count 
            FROM {$this->table} 
            WHERE user_id = ? 
            GROUP BY vehicle 
            ORDER BY count DESC 
            LIMIT ?
        ";
        $result = $this->dbHelper->executeQuery($sql, [$userId, $limit], 'ii');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getUserFavoriteRoutes($userId, $limit = 3) {
        $sql = "
            SELECT CONCAT(origen, ' → ', desti) as ruta, COUNT(*) as count, origen, desti
            FROM {$this->table} 
            WHERE user_id = ? 
            GROUP BY origen, desti 
            ORDER BY count DESC 
            LIMIT ?
        ";
        $result = $this->dbHelper->executeQuery($sql, [$userId, $limit], 'ii');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getRecentActivity($limit = 5) {
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
            ORDER BY hr.id DESC 
            LIMIT ?
        ";
        $result = $this->dbHelper->executeQuery($sql, [$limit], 'i');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getMonthlyStats($year, $month, $userId = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_horaris,
                COUNT(DISTINCT vehicle) as vehicles_used,
                COUNT(DISTINCT CONCAT(origen, '-', desti)) as routes_used
            FROM {$this->table} 
            WHERE YEAR(data_ruta) = ? AND MONTH(data_ruta) = ?
        ";
        
        $params = [$year, $month];
        $types = 'ii';
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        $result = $this->dbHelper->executeQuery($sql, $params, $types);
        return $result ? $result->fetch_assoc() : ['total_horaris' => 0, 'vehicles_used' => 0, 'routes_used' => 0];
    }
    
    public function getPopularRoutes($limit = 3) {
        $sql = "
            SELECT CONCAT(origen, ' → ', desti) as ruta, COUNT(*) as count, origen, desti
            FROM {$this->table} 
            GROUP BY origen, desti 
            ORDER BY count DESC 
            LIMIT ?
        ";
        $result = $this->dbHelper->executeQuery($sql, [$limit], 'i');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getPopularVehicles($limit = 3) {
        $sql = "
            SELECT vehicle, COUNT(*) as count 
            FROM {$this->table} 
            GROUP BY vehicle 
            ORDER BY count DESC 
            LIMIT ?
        ";
        $result = $this->dbHelper->executeQuery($sql, [$limit], 'i');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getHorarisByLocation($query) {
        $sql = "
            SELECT hr.*, u.nom, u.cognoms 
            FROM {$this->table} hr 
            JOIN usuaris u ON hr.user_id = u.id 
            WHERE hr.origen LIKE ? OR hr.desti LIKE ?
            ORDER BY hr.data_ruta DESC, hr.hora_inici ASC
        ";
        $searchTerm = '%' . $query . '%';
        $result = $this->dbHelper->executeQuery($sql, [$searchTerm, $searchTerm], 'ss');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>