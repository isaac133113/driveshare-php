<?php
require_once __DIR__ . '/../config/Database.php';

class HorariRutaModel {
    protected static $tabla = 'horaris_rutes';
    protected static $columnasDB = [
        'id',
        'user_id',
        'data_ruta',
        'hora_inici',
        'hora_fi',
        'origen',
        'desti',
        'vehicle_id',
        'comentaris',
        'data_creacio',
        'data_modificacio',
        'origen_lat',
        'origen_lng',
        'desti_lat',
        'desti_lng',
        'plazas_disponibles',
        'precio_euros',
        'estado'
    ];

    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById($id) {
        $sql = "SELECT hr.*, 
                    u.nom, u.cognoms,
                    COALESCE(v.marca_model, hr.vehicle) AS vehicle,
                    COALESCE(v.marca_model, hr.vehicle) AS marca_model,
                    COALESCE(v.tipus, 'No especificat') AS tipus,
                    COALESCE(hr.plazas_disponibles, v.places, 1) AS plazas_disponibles,
                    hr.precio_euros AS precio_euros
                FROM horaris_rutes hr
                JOIN usuaris u ON hr.user_id = u.id
                LEFT JOIN vehicles v ON hr.vehicle_id = v.id
                WHERE hr.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getByUserId($userId) {
        $sql = "SELECT hr.*, v.marca_model as vehicle_name 
                FROM horaris_rutes hr 
                LEFT JOIN vehicles v ON hr.vehicle_id = v.id 
                WHERE hr.user_id = ? 
                ORDER BY hr.data_ruta DESC, hr.hora_inici ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
        // Campos básicos obligatorios + plazas_disponibles y precio_euros
        $sql = "INSERT INTO horaris_rutes (
                    user_id, data_ruta, hora_inici, hora_fi, 
                    origen, desti, comentaris,
                    plazas_disponibles, precio_euros,
                    data_creacio, data_modificacio
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        
        if ($stmt === false) {
            error_log("Error preparando statement HorariRuta: " . $this->db->error);
            error_log("SQL: $sql");
            return false;
        }
        
        $comentaris = $data['comentaris'] ?? '';
        $plazasDisponibles = $data['plazas_disponibles'] ?? 1;
        $precioEuros = $data['precio_euros'] ?? 0;
        
        $stmt->bind_param(
            "issssssis",
            $data['user_id'],
            $data['data_ruta'],
            $data['hora_inici'],
            $data['hora_fi'],
            $data['origen'],
            $data['desti'],
            $comentaris,
            $plazasDisponibles,
            $precioEuros
        );

        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            error_log("Ruta creada exitosamente con ID: $insertId");
            return $insertId;
        } else {
            error_log("Error ejecutando insert HorariRuta: " . $stmt->error);
            error_log("Datos: " . print_r($data, true));
            return false;
        }
    }

    public function getAllRutes() {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT hr.*, 
                    u.nom, u.cognoms,
                    COALESCE(v.marca_model, hr.vehicle) AS vehicle,
                    COALESCE(v.marca_model, hr.vehicle) AS marca_model,
                    COALESCE(v.tipus, 'No especificat') AS tipus,
                    vi.url AS vehicle_image,
                    COALESCE(hr.plazas_disponibles, 4) AS plazas_disponibles,
                    COALESCE(hr.precio_euros, 0) AS precio_euros,
                    COALESCE(hr.estado, 1) AS estado
                FROM horaris_rutes hr
                JOIN usuaris u ON hr.user_id = u.id
                LEFT JOIN vehicles v ON hr.vehicle_id = v.id
                LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.orden = 0
                WHERE (hr.estado IS NULL OR hr.estado = 1)
                AND hr.user_id <> ?  -- Excluye tus propias rutas
                ORDER BY hr.data_ruta DESC, hr.hora_inici ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt === false) {
            error_log("Error preparing getAllRutes statement: " . $this->db->error);
            return [];
        }
        
        if (!$stmt->execute()) {
            error_log("Error executing getAllRutes statement: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        error_log("getAllRutes returned " . count($result) . " rows");
        return $result;
    }

    // public function getFilteredRutes($filtros = []) {
    //     $sql = "SELECT * FROM rutes WHERE 1=1";
    //     $params = [];

    //     if (!empty($filtros['origen'])) {
    //         $sql .= " AND origen LIKE :origen";
    //         $params[':origen'] = '%' . $filtros['origen'] . '%';
    //     }
    //     if (!empty($filtros['desti'])) {
    //         $sql .= " AND desti LIKE :desti";
    //         $params[':desti'] = '%' . $filtros['desti'] . '%';
    //     }
    //     if (!empty($filtros['tipus'])) {
    //         $sql .= " AND tipus LIKE :tipus";
    //         $params[':tipus'] = '%' . $filtros['tipus'] . '%';
    //     }
    //     if (!empty($filtros['marca_model'])) {
    //         $sql .= " AND marca_model LIKE :marca_model";
    //         $params[':marca_model'] = '%' . $filtros['marca_model'] . '%';
    //     }
    //     if (!empty($filtros['min_precio'])) {
    //         $sql .= " AND preu_hora >= :min_precio";
    //         $params[':min_precio'] = $filtros['min_precio'];
    //     }
    //     if (!empty($filtros['max_precio'])) {
    //         $sql .= " AND preu_hora <= :max_precio";
    //         $params[':max_precio'] = $filtros['max_precio'];
    //     }

    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute($params);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function getFilteredRutes($filtros = []) {
        $sql = "SELECT hr.*, v.marca_model, v.tipus, vi.url AS vehicle_image
                FROM horaris_rutes hr
                LEFT JOIN vehicles v ON hr.vehicle_id = v.id
                LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.orden = 0
                WHERE 1=1";
        
        $types = '';
        $values = [];

        if (!empty($filtros['origen'])) {
            $sql .= " AND hr.origen LIKE ?";
            $types .= 's';
            $values[] = '%' . $filtros['origen'] . '%';
        }
        if (!empty($filtros['desti'])) {
            $sql .= " AND hr.desti LIKE ?";
            $types .= 's';
            $values[] = '%' . $filtros['desti'] . '%';
        }
        if (!empty($filtros['tipus'])) {
            $sql .= " AND v.tipus LIKE ?";
            $types .= 's';
            $values[] = '%' . $filtros['tipus'] . '%';
        }
        if (!empty($filtros['marca_model'])) {
            $sql .= " AND v.marca_model LIKE ?";
            $types .= 's';
            $values[] = '%' . $filtros['marca_model'] . '%';
        }
        if (!empty($filtros['min_precio'])) {
            $sql .= " AND hr.precio_euros >= ?";
            $types .= 'd';
            $values[] = $filtros['min_precio'];
        }
        if (!empty($filtros['max_precio'])) {
            $sql .= " AND hr.precio_euros <= ?";
            $types .= 'd';
            $values[] = $filtros['max_precio'];
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($values)) {
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEstados() {
        return [
            1 => 'Pendent',
            2 => 'Confirmada',
            3 => 'Completada',
            4 => 'Cancel·lada'
        ];
    }
}