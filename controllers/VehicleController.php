<?php
require_once __DIR__ . '/BaseController.php';

class VehicleController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        // Obtener filtros
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'precio_max' => $_GET['precio_max'] ?? '',
            'disponible' => $_GET['disponible'] ?? 'si'
        ];
        
        // Obtener lista de vehículos
        $vehicles = $this->getVehiclesList($filtros);
        
        // Obtener tipos de vehículos para el filtro
        $tiposVehicles = $this->getTiposVehicles();
        
        $message = '';
        $messageType = '';
        
        // Manejar reserva
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reservar') {
            $result = $this->reservarVehicle();
            $message = $result['message'];
            $messageType = $result['type'];
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/vehicles/index.php';
    }
    
    private function getVehiclesList($filtros = []) {
        $vehicles = [
            [
                'id' => 1,
                'nombre' => 'BMW Serie 3',
                'tipo' => 'sedan',
                'marca' => 'BMW',
                'modelo' => 'Serie 3',
                'año' => 2022,
                'precio_hora' => 25.00,
                'precio_dia' => 150.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Automática',
                'pasajeros' => 5,
                'puertas' => 4,
                'aire_acondicionado' => true,
                'gps' => true,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'data:image/jpeg;base64,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',
                'descripcion' => 'Sedan de lujo con excelente rendimiento y comodidad. Ideal para viajes de negocios y familiares.'
            ],
            [
                'id' => 2,
                'nombre' => 'Volkswagen Golf',
                'tipo' => 'compacto',
                'marca' => 'Volkswagen',
                'modelo' => 'Golf',
                'año' => 2023,
                'precio_hora' => 18.00,
                'precio_dia' => 100.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Manual',
                'pasajeros' => 5,
                'puertas' => 5,
                'aire_acondicionado' => true,
                'gps' => false,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'https://thumbs.dreamstime.com/b/vw-blanca-golf-aislado-en-el-fondo-blanco-127917589.jpg',
                'descripcion' => 'Compacto versátil y económico, perfecto para la ciudad y viajes cortos.'
            ],
            [
                'id' => 3,
                'nombre' => 'Ford Transit',
                'tipo' => 'furgoneta',
                'marca' => 'Ford',
                'modelo' => 'Transit',
                'año' => 2021,
                'precio_hora' => 35.00,
                'precio_dia' => 200.00,
                'combustible' => 'Diesel',
                'transmision' => 'Manual',
                'pasajeros' => 9,
                'puertas' => 4,
                'aire_acondicionado' => true,
                'gps' => true,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'https://via.placeholder.com/300x200/ffc107/000000?text=Ford+Transit',
                'descripcion' => 'Furgoneta espaciosa ideal para grupos grandes y mudanzas.'
            ],
            [
                'id' => 4,
                'nombre' => 'Tesla Model 3',
                'tipo' => 'electrico',
                'marca' => 'Tesla',
                'modelo' => 'Model 3',
                'año' => 2023,
                'precio_hora' => 30.00,
                'precio_dia' => 180.00,
                'combustible' => 'Eléctrico',
                'transmision' => 'Automática',
                'pasajeros' => 5,
                'puertas' => 4,
                'aire_acondicionado' => true,
                'gps' => true,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'data:image/jpeg;base64,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',
                'descripcion' => 'Sedan eléctrico de última generación con tecnología avanzada y cero emisiones.'
            ],
            [
                'id' => 5,
                'nombre' => 'Jeep Wrangler',
                'tipo' => 'suv',
                'marca' => 'Jeep',
                'modelo' => 'Wrangler',
                'año' => 2022,
                'precio_hora' => 28.00,
                'precio_dia' => 160.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Automática',
                'pasajeros' => 5,
                'puertas' => 4,
                'aire_acondicionado' => true,
                'gps' => true,
                'bluetooth' => true,
                'disponible' => false,
                'imagen' => 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Jeep+Wrangler',
                'descripcion' => 'SUV robusto perfecto para aventuras off-road y escapadas de fin de semana.'
            ],
            [
                'id' => 6,
                'nombre' => 'Fiat 500',
                'tipo' => 'city',
                'marca' => 'Fiat',
                'modelo' => '500',
                'año' => 2023,
                'precio_hora' => 15.00,
                'precio_dia' => 80.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Manual',
                'pasajeros' => 4,
                'puertas' => 3,
                'aire_acondicionado' => true,
                'gps' => false,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'https://via.placeholder.com/300x200/e83e8c/ffffff?text=Fiat+500',
                'descripcion' => 'Pequeño y ágil, ideal para moverse por el centro de la ciudad.'
            ],
            [
                'id' => 7,
                'nombre' => 'Mercedes C-Class',
                'tipo' => 'lujo',
                'marca' => 'Mercedes-Benz',
                'modelo' => 'Clase C',
                'año' => 2023,
                'precio_hora' => 40.00,
                'precio_dia' => 250.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Automática',
                'pasajeros' => 5,
                'puertas' => 4,
                'aire_acondicionado' => true,
                'gps' => true,
                'bluetooth' => true,
                'disponible' => true,
                'imagen' => 'https://via.placeholder.com/300x200/6c757d/ffffff?text=Mercedes+C-Class',
                'descripcion' => 'Sedan de lujo premium con todas las comodidades y tecnología de última generación.'
            ],
            [
                'id' => 8,
                'nombre' => 'Yamaha MT-07',
                'tipo' => 'moto',
                'marca' => 'Yamaha',
                'modelo' => 'MT-07',
                'año' => 2022,
                'precio_hora' => 12.00,
                'precio_dia' => 60.00,
                'combustible' => 'Gasolina',
                'transmision' => 'Manual',
                'pasajeros' => 2,
                'puertas' => 0,
                'aire_acondicionado' => false,
                'gps' => false,
                'bluetooth' => false,
                'disponible' => true,
                'imagen' => 'https://via.placeholder.com/300x200/fd7e14/ffffff?text=Yamaha+MT-07',
                'descripcion' => 'Motocicleta deportiva ágil y potente, perfecta para desplazamientos rápidos.'
            ]
        ];
        
        // Aplicar filtros
        if (!empty($filtros['tipo'])) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($filtros) {
                return $vehicle['tipo'] === $filtros['tipo'];
            });
        }
        
        if (!empty($filtros['precio_max'])) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($filtros) {
                return $vehicle['precio_dia'] <= floatval($filtros['precio_max']);
            });
        }
        
        if ($filtros['disponible'] === 'si') {
            $vehicles = array_filter($vehicles, function($vehicle) {
                return $vehicle['disponible'] === true;
            });
        }
        
        return array_values($vehicles); // Reindexar array
    }
    
    private function getTiposVehicles() {
        return [
            'sedan' => 'Sedan',
            'compacto' => 'Compacto',
            'suv' => 'SUV',
            'furgoneta' => 'Furgoneta',
            'electrico' => 'Eléctrico',
            'lujo' => 'Lujo',
            'city' => 'Urbano',
            'moto' => 'Motocicleta'
        ];
    }
    
    private function reservarVehicle() {
        $vehicleId = intval($_POST['vehicle_id']);
        $fechaInicio = $_POST['fecha_inicio'];
        $fechaFin = $_POST['fecha_fin'];
        $tipoRenta = $_POST['tipo_renta']; // 'horas' o 'dias'
        $cantidad = intval($_POST['cantidad']);
        
        // Validaciones básicas
        if (empty($vehicleId) || empty($fechaInicio) || empty($fechaFin) || empty($cantidad)) {
            return [
                'success' => false,
                'message' => 'Tots els camps són obligatoris.',
                'type' => 'danger'
            ];
        }
        
        // Verificar que las fechas sean válidas
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $ahora = new DateTime();
        
        if ($inicio < $ahora) {
            return [
                'success' => false,
                'message' => 'La data d\'inici no pot ser anterior a avui.',
                'type' => 'danger'
            ];
        }
        
        if ($fin <= $inicio) {
            return [
                'success' => false,
                'message' => 'La data de fi ha de ser posterior a la data d\'inici.',
                'type' => 'danger'
            ];
        }
        
        // Obtener datos del vehículo
        $vehicles = $this->getVehiclesList();
        $vehicle = null;
        foreach ($vehicles as $v) {
            if ($v['id'] == $vehicleId) {
                $vehicle = $v;
                break;
            }
        }
        
        if (!$vehicle) {
            return [
                'success' => false,
                'message' => 'Vehicle no trobat.',
                'type' => 'danger'
            ];
        }
        
        if (!$vehicle['disponible']) {
            return [
                'success' => false,
                'message' => 'Aquest vehicle no està disponible.',
                'type' => 'danger'
            ];
        }
        
        // Calcular precio
        $precio = $tipoRenta === 'horas' ? $vehicle['precio_hora'] : $vehicle['precio_dia'];
        $total = $precio * $cantidad;
        
        // Generar código de reserva
        $codigoReserva = 'DRS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // En una aplicación real, aquí guardarías la reserva en la base de datos
        // Por ahora, simularemos una reserva exitosa
        
        // Log de la actividad
        $this->userModel->logUserActivity(
            $_SESSION['user_id'], 
            "reserva_vehicle_" . $vehicle['nombre'], 
            $_SERVER['REMOTE_ADDR']
        );
        
        return [
            'success' => true,
            'message' => "Reserva realitzada correctament! Codi de reserva: <strong>$codigoReserva</strong><br>
                         Vehicle: {$vehicle['nombre']}<br>
                         Període: $fechaInicio - $fechaFin<br>
                         Total: €" . number_format($total, 2),
            'type' => 'success',
            'codigo_reserva' => $codigoReserva
        ];
    }
    
    public function details() {
        $vehicleId = intval($_GET['id'] ?? 0);
        
        if (!$vehicleId) {
            $this->redirect('?');
        }
        
        $vehicles = $this->getVehiclesList();
        $vehicle = null;
        foreach ($vehicles as $v) {
            if ($v['id'] == $vehicleId) {
                $vehicle = $v;
                break;
            }
        }
        
        if (!$vehicle) {
            $this->redirect('?');
        }
        
        // Cargar vista de detalles
        include __DIR__ . '/../views/vehicles/details.php';
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'VehicleController.php') {
    $controller = new VehicleController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'details':
            $controller->details();
            break;
        default:
            $controller->index();
    }
}
?>