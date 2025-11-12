<?php
require_once __DIR__ . '/../config/Database.php';

class VehicleModel {
    private $db;
    protected static $tabla = 'vehicles';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Tipus en català
    public function getTiposVehicles() {
        return [
            'sedan'     => 'Sedan',
            'compacto'  => 'Compacte',
            'suv'       => 'SUV',
            'furgoneta' => 'Furgoneta',
            'electrico' => 'Elèctric',
            'lujo'      => 'Luxe',
            'city'      => 'Urbà',
            'moto'      => 'Motocicle'
        ];
    }

    // Llistar vehicles amb filtres (p. ex. ['user_id' => 1])
    public function getAllVehicles($filters = []) {
        $sql = "SELECT v.*, GROUP_CONCAT(vi.url ORDER BY vi.orden SEPARATOR ',') AS images
                FROM vehicles v
                LEFT JOIN vehicle_images vi ON vi.vehicle_id = v.id
                WHERE 1=1";

        $where = [];
        $types = '';
        $values = [];

        if (!empty($filters['user_id'])) {
            $where[] = "v.user_id = ?";
            $types .= 'i';
            $values[] = (int)$filters['user_id'];
        }

        if (!empty($filters['tipus'])) {
            $where[] = "v.tipus = ?";
            $types .= 's';
            $values[] = $filters['tipus'];
        }

        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }

        $sql .= " GROUP BY v.id ORDER BY v.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if ($types !== '') {
            // bind_param necesita referencias
            $bindParams = [];
            $bindParams[] = &$types;
            foreach ($values as $k => $v) {
                $bindParams[] = &$values[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $vehicles = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($vehicles as &$v) {
            $v['images'] = $v['images'] ? explode(',', $v['images']) : [];
        }

        return $vehicles;
    }

    public function getVehicleById($id) {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $vehicle = $stmt->get_result()->fetch_assoc();
        if (!$vehicle) return null;

        $stmt2 = $this->db->prepare("SELECT url FROM vehicle_images WHERE vehicle_id = ? ORDER BY orden ASC");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $imgs = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $vehicle['images'] = array_column($imgs, 'url');

        return $vehicle;
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO vehicles (user_id, marca_model, tipus, places, transmissio, descripcio, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param(
            "ississ",
            $data['user_id'],
            $data['marca_model'],
            $data['tipus'],
            $data['places'],
            $data['transmissio'],
            $data['descripcio']
        );
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->db->prepare(
            "UPDATE vehicles 
            SET marca_model = ?, tipus = ?, places = ?, transmissio = ?, descripcio = ? 
            WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param(
            "ssissii",
            $data['marca_model'],
            $data['tipus'],
            $data['places'],
            $data['transmissio'],
            $data['descripcio'],
            $data['id'],
            $data['user_id']
        );
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Imatges
    public function addVehicleImage($vehicleId, $url, $orden = 0) {
        $stmt = $this->db->prepare("INSERT INTO vehicle_images (vehicle_id, url, orden, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isi", $vehicleId, $url, $orden);
        return $stmt->execute();
    }

    public function getImagesForVehicle($vehicleId) {
        $stmt = $this->db->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY orden ASC");
        $stmt->bind_param("i", $vehicleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getVehicleImages($vehicleId) {
        $sql = "SELECT * FROM vehicle_images WHERE vehicle_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            die("Error en prepare: " . $this->db->error);
        }
        $stmt->bind_param("i", $vehicleId); // "i" = integer
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteVehicleImage($imageId) {
        $stmt = $this->db->prepare("DELETE FROM vehicle_images WHERE id = ?");
        if (!$stmt) {
            die("Error en prepare: " . $this->db->error);
        }
        $stmt->bind_param("i", $imageId); // "i" = integer
        return $stmt->execute();
    }
}
?>