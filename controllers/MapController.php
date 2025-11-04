<?php
require_once __DIR__ . '/BaseController.php';

class MapController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        // Obtener parámetros de búsqueda
        $userLat = $_GET['lat'] ?? null;
        $userLng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 5; // km por defecto
        $vehicleType = $_GET['vehicle_type'] ?? '';
        
        // Obtener ubicación del usuario si está disponible
        $userLocation = null;
        if ($userLat && $userLng) {
            $userLocation = [
                'lat' => floatval($userLat),
                'lng' => floatval($userLng)
            ];
        }
        
        // Obtener vehículos cercanos
        $nearbyVehicles = $this->getNearbyVehicles($userLocation, $radius, $vehicleType);
        
        // Obtener estaciones de servicio cercanas
        $gasStations = $this->getNearbyGasStations($userLocation, $radius);
        
        // Obtener parkings cercanos
        $parkings = $this->getNearbyParkings($userLocation, $radius);
        
        $message = '';
        $messageType = '';
        
        // Manejar reserva rápida
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_reserve') {
            $result = $this->quickReserveVehicle();
            $message = $result['message'];
            $messageType = $result['type'];
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/map/index.php';
    }
    
    private function getNearbyVehicles($userLocation = null, $radius = 5, $vehicleType = '') {
        // Simulación de vehículos con ubicaciones en Mollerussa y alrededores
        $vehicles = [
            [
                'id' => 1,
                'nombre' => 'BMW Serie 3',
                'tipo' => 'sedan',
                'marca' => 'BMW',
                'precio_hora' => 25.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6231,
                    'lng' => 0.8825,
                    'direccion' => 'Plaça Major, 1, Mollerussa',
                    'descripcion' => 'Centro de Mollerussa'
                ],
                'distancia' => 0.2,
                'bateria' => 85,
                'combustible' => 'Gasolina',
                'imagen' => 'https://via.placeholder.com/150x100/007bff/ffffff?text=BMW'
            ],
            [
                'id' => 2,
                'nombre' => 'VW Golf',
                'tipo' => 'compacto',
                'marca' => 'Volkswagen',
                'precio_hora' => 18.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6245,
                    'lng' => 0.8840,
                    'direccion' => 'Carrer de Lleida, 45, Mollerussa',
                    'descripcion' => 'Cerca del Instituto'
                ],
                'distancia' => 0.5,
                'bateria' => 92,
                'combustible' => 'Gasolina',
                'imagen' => 'https://via.placeholder.com/150x100/28a745/ffffff?text=VW+Golf'
            ],
            [
                'id' => 3,
                'nombre' => 'Tesla Model 3',
                'tipo' => 'electrico',
                'marca' => 'Tesla',
                'precio_hora' => 30.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6195,
                    'lng' => 0.8795,
                    'direccion' => 'Avinguda de Catalunya, 23, Mollerussa',
                    'descripcion' => 'Estación de carga Tesla'
                ],
                'distancia' => 0.8,
                'bateria' => 78,
                'combustible' => 'Eléctrico',
                'imagen' => 'https://via.placeholder.com/150x100/17a2b8/ffffff?text=Tesla'
            ],
            [
                'id' => 4,
                'nombre' => 'Fiat 500',
                'tipo' => 'city',
                'marca' => 'Fiat',
                'precio_hora' => 15.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6280,
                    'lng' => 0.8870,
                    'direccion' => 'Carrer Sant Isidre, 12, Mollerussa',
                    'descripcion' => 'Zona residencial'
                ],
                'distancia' => 1.2,
                'bateria' => 68,
                'combustible' => 'Gasolina',
                'imagen' => 'https://via.placeholder.com/150x100/e83e8c/ffffff?text=Fiat+500'
            ],
            [
                'id' => 5,
                'nombre' => 'Ford Transit',
                'tipo' => 'furgoneta',
                'marca' => 'Ford',
                'precio_hora' => 35.00,
                'disponible' => false,
                'ubicacion' => [
                    'lat' => 41.6160,
                    'lng' => 0.8910,
                    'direccion' => 'Polígon Industrial, Mollerussa',
                    'descripcion' => 'Zona industrial'
                ],
                'distancia' => 1.8,
                'bateria' => 45,
                'combustible' => 'Diesel',
                'imagen' => 'https://via.placeholder.com/150x100/ffc107/000000?text=Ford+Transit'
            ],
            [
                'id' => 6,
                'nombre' => 'Yamaha MT-07',
                'tipo' => 'moto',
                'marca' => 'Yamaha',
                'precio_hora' => 12.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6205,
                    'lng' => 0.8755,
                    'direccion' => 'Carrer del Torrent, 8, Mollerussa',
                    'descripcion' => 'Cerca del parque'
                ],
                'distancia' => 0.3,
                'bateria' => 95,
                'combustible' => 'Gasolina',
                'imagen' => 'https://via.placeholder.com/150x100/fd7e14/ffffff?text=Yamaha'
            ]
        ];
        
        // Filtrar por tipo de vehículo si se especifica
        if (!empty($vehicleType)) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($vehicleType) {
                return $vehicle['tipo'] === $vehicleType;
            });
        }
        
        // Filtrar por radio de distancia
        if ($userLocation) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($radius) {
                return $vehicle['distancia'] <= $radius;
            });
        }
        
        // Ordenar por distancia
        usort($vehicles, function($a, $b) {
            return $a['distancia'] <=> $b['distancia'];
        });
        
        return array_values($vehicles);
    }
    
    private function getNearbyGasStations($userLocation = null, $radius = 5) {
        return [
            [
                'nombre' => 'Repsol Mollerussa',
                'lat' => 41.6210,
                'lng' => 0.8800,
                'direccion' => 'Carretera N-II, km 456',
                'precio_gasolina' => 1.459,
                'precio_diesel' => 1.389,
                'servicios' => ['Lavado', 'Tienda', '24h']
            ],
            [
                'nombre' => 'BP La Salle',
                'lat' => 41.6190,
                'lng' => 0.8820,
                'direccion' => 'Avinguda La Salle, 15',
                'precio_gasolina' => 1.465,
                'precio_diesel' => 1.395,
                'servicios' => ['Tienda', 'Cafetería']
            ],
            [
                'nombre' => 'Carga Eléctrica Tesla',
                'lat' => 41.6195,
                'lng' => 0.8795,
                'direccion' => 'Avinguda de Catalunya, 23',
                'precio_gasolina' => null,
                'precio_diesel' => null,
                'servicios' => ['Carga rápida', 'Tesla Supercharger']
            ]
        ];
    }
    
    private function getNearbyParkings($userLocation = null, $radius = 5) {
        return [
            [
                'nombre' => 'Parking Plaça Major',
                'lat' => 41.6235,
                'lng' => 0.8830,
                'direccion' => 'Plaça Major, Mollerussa',
                'precio_hora' => 1.20,
                'plazas_libres' => 45,
                'plazas_totales' => 120,
                'servicios' => ['Cubierto', 'Vigilancia']
            ],
            [
                'nombre' => 'Parking Centro Comercial',
                'lat' => 41.6200,
                'lng' => 0.8850,
                'direccion' => 'Avinguda de Lleida, 78',
                'precio_hora' => 0.80,
                'plazas_libres' => 23,
                'plazas_totales' => 80,
                'servicios' => ['Gratuito 2h', 'Ascensor']
            ]
        ];
    }
    
    private function quickReserveVehicle() {
        $vehicleId = intval($_POST['vehicle_id']);
        $duracion = intval($_POST['duracion']); // en horas
        
        if (empty($vehicleId) || empty($duracion)) {
            return [
                'success' => false,
                'message' => 'Tots els camps són obligatoris.',
                'type' => 'danger'
            ];
        }
        
        // Obtener datos del vehículo
        $vehicles = $this->getNearbyVehicles();
        $vehicle = null;
        foreach ($vehicles as $v) {
            if ($v['id'] == $vehicleId) {
                $vehicle = $v;
                break;
            }
        }
        
        if (!$vehicle || !$vehicle['disponible']) {
            return [
                'success' => false,
                'message' => 'Vehicle no disponible.',
                'type' => 'danger'
            ];
        }
        
        // Calcular precio
        $total = $vehicle['precio_hora'] * $duracion;
        
        // Generar código de reserva
        $codigoReserva = 'DRS' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Log de la actividad
        $this->userModel->logUserActivity(
            $_SESSION['user_id'], 
            "reserva_rapida_" . $vehicle['nombre'], 
            $_SERVER['REMOTE_ADDR']
        );
        
        return [
            'success' => true,
            'message' => "Reserva ràpida realitzada! Codi: <strong>$codigoReserva</strong><br>
                         Vehicle: {$vehicle['nombre']}<br>
                         Ubicació: {$vehicle['ubicacion']['direccion']}<br>
                         Duració: $duracion hores<br>
                         Total: €" . number_format($total, 2),
            'type' => 'success',
            'codigo_reserva' => $codigoReserva,
            'vehicle_location' => $vehicle['ubicacion']
        ];
    }
    
    public function getVehiclesApi() {
        header('Content-Type: application/json');
        
        $userLat = $_GET['lat'] ?? null;
        $userLng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 5;
        $vehicleType = $_GET['vehicle_type'] ?? '';
        
        $userLocation = null;
        if ($userLat && $userLng) {
            $userLocation = [
                'lat' => floatval($userLat),
                'lng' => floatval($userLng)
            ];
        }
        
        $vehicles = $this->getNearbyVehicles($userLocation, $radius, $vehicleType);
        
        echo json_encode([
            'success' => true,
            'vehicles' => $vehicles
        ]);
        exit;
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'MapController.php') {
    $controller = new MapController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'api':
            $controller->getVehiclesApi();
            break;
        default:
            $controller->index();
    }
}
?>