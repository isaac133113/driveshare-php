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
        
        // Nuevos parámetros para búsqueda por rutas
        $destinoLat = $_GET['destino_lat'] ?? null;
        $destinoLng = $_GET['destino_lng'] ?? null;
        $destinoNombre = $_GET['destino_nombre'] ?? '';
        $searchMode = $_GET['search_mode'] ?? 'nearby'; // 'nearby' o 'route'
        
        // Obtener ubicación del usuario si está disponible
        $userLocation = null;
        if ($userLat && $userLng) {
            $userLocation = [
                'lat' => floatval($userLat),
                'lng' => floatval($userLng)
            ];
        }
        
        // Obtener destino si está disponible
        $destinoLocation = null;
        if ($destinoLat && $destinoLng) {
            $destinoLocation = [
                'lat' => floatval($destinoLat),
                'lng' => floatval($destinoLng),
                'nombre' => $destinoNombre
            ];
        }
        
        // Obtener vehículos según el modo de búsqueda
        if ($searchMode === 'route' && $userLocation && $destinoLocation) {
            $vehicles = $this->getVehiclesForRoute($userLocation, $destinoLocation, $vehicleType);
        } else {
            $vehicles = $this->getNearbyVehicles($userLocation, $radius, $vehicleType);
        }
        
        // Asegurar que $vehicles siempre sea un array
        $vehicles = $vehicles ?? [];
        
        // Obtener rutas populares
        $popularRoutes = $this->getPopularRoutes();
        
        // Obtener estaciones de servicio cercanas
        $gasStations = $this->getNearbyGasStations($userLocation, $radius);
        
        // Obtener parkings cercanos
        $parkings = $this->getNearbyParkings($userLocation, $radius);
        
        $message = '';
        $messageType = '';
        
        // Manejar reserva rápida
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_reserve') {
            $result = $this->quickReserveVehicle();
            
            if ($result['success']) {
                // Si la reserva fue exitosa, redirigir a horaris a través del controlador
                $_SESSION['message'] = $result['message'];
                $_SESSION['messageType'] = $result['type'];
                header("Location: ../../public/index.php?controller=horaris&action=index");
                exit;
            } else {
                // Si hubo error, mostrar mensaje en la misma página
                $message = $result['message'];
                $messageType = $result['type'];
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/map/index.php';
    }
    
    private function getNearbyVehicles($userLocation = null, $radius = 5, $vehicleType = '') {
        // Simulación de vehículos con ubicaciones en Mollerussa y alrededores usando las mismas imágenes de VehicleController
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
                'imagen' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxIQEBUQEBAVFRUVEBUWFRUXFRUWFRAQFRUWFxUVFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMtNygtLisBCgoKDg0NFQ8PFSsdFR0rKy03KystLS0tLSsxLSstNysrKy0tKy0tKy4tKzcrKystKy0tKys3LTQtKysrKy03Lf/AABEIAOEA4QMBIgACEQEDEQH/xAAcAAADAAIDAQAAAAAAAAAAAAAAAQIDBAUGBwj/'
            ],
            [
                'id' => 2,
                'nombre' => 'Volkswagen Golf',
                'tipo' => 'compacto',
                'marca' => 'Volkswagen',
                'precio_hora' => 18.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6245,
                    'lng' => 0.8840,
                    'direccion' => 'Carrer de la Pau, 25, Mollerussa',
                    'descripcion' => 'Zona residencial'
                ],
                'distancia' => 0.4,
                'bateria' => 92,
                'combustible' => 'Gasolina',
                'imagen' => 'https://thumbs.dreamstime.com/b/vw-blanca-golf-aislado-en-el-fondo-blanco-127917589.jpg'
            ],
            [
                'id' => 3,
                'nombre' => 'Ford Transit',
                'tipo' => 'furgoneta',
                'marca' => 'Ford',
                'precio_hora' => 30.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6210,
                    'lng' => 0.8835,
                    'direccion' => 'Carrer Agramunt, 10, Mollerussa',
                    'descripcion' => 'Polígono industrial'
                ],
                'distancia' => 0.3,
                'bateria' => 78,
                'combustible' => 'Diesel',
                'imagen' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUQExAWFRUXGB0YGBUXFRYYGBgXFxgWFxYVFxgYHSggGBolHRcVITEiJSkrLy4vFx8zODMsNygtLisBCgoKDQ0OFw8QFS0lFRksNy4rKy0tMi0rKysrNzctLisrNys3KzcrKy0rKy0rKystNys3LSsrKysrLS0tLS0zLysrLf/AABEIAKsBJgMBIgACEQEDEQH/xAAcAAEAAQUBAQAAAAAAAAAAAAAABwIDBAUGAQj/'
            ],
            [
                'id' => 4,
                'nombre' => 'Tesla Model 3',
                'tipo' => 'electrico',
                'marca' => 'Tesla',
                'precio_hora' => 35.00,
                'disponible' => true,
                'ubicacion' => [
                    'lat' => 41.6200,
                    'lng' => 0.8810,
                    'direccion' => 'Avinguda Catalunya, 5, Mollerussa',
                    'descripcion' => 'Estación de carga'
                ],
                'distancia' => 0.5,
                'bateria' => 95,
                'combustible' => 'Eléctrico',
                'imagen' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUSEBIVEBUVFRUVFRcVGBYVFxYXFRYWFhUVFRUYHSggGRolHRUVITEhJSkrLi4uFx8zODMuNygtLisBCgoKDQ0NDg4PFTcmFR03KysrLSsrLSsrKys3Ky03LSsrKysrLSsrLS4rKysrKysrKysrKzc3KysrNysrKy0rLf/AABEIAKYBMAMBIgACEQEDEQH/xAAcAAEAAQUBAQAAAAAAAAAAAAAABgIDBAUHAQj/'
            ],
            [
                'id' => 5,
                'nombre' => 'Jeep Wrangler',
                'tipo' => 'suv',
                'marca' => 'Jeep',
                'precio_hora' => 28.00,
                'disponible' => false,
                'ubicacion' => [
                    'lat' => 41.6250,
                    'lng' => 0.8850,
                    'direccion' => 'Carrer Major, 15, Mollerussa',
                    'descripcion' => 'Aparcamiento público'
                ],
                'distancia' => 0.6,
                'bateria' => 88,
                'combustible' => 'Gasolina',
                'imagen' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUTEhIWFRUXGB0YGBUXFRYYGBgXFxgWFxYVFxgYHSggGBolHRUVITEiJSkrLy4vFx8zODMsNygtLisBCgoKDQ0OGw8QGjclHSUrMTcvNTEtLDc4LSsrLSs3KystOC4tMzc3MC4rLSsrKy0rLTA1LS0tLy0tLS0zLysrLf/AABEIAKsBJgMBIgACEQEDEQH/xAAcAAEAAQUBAQAAAAAAAAAAAAAABwIDBAUGAQj/'
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
        
        return array_values($vehicles); // Reindexar array
    }
    
    private function getVehiclesForRoute($userLocation, $destinoLocation, $vehicleType = '') {
        // Obtener todos los vehículos
        $allVehicles = $this->getNearbyVehicles(null, 50, $vehicleType); // Radio amplio para la ruta
        
        // Calcular la ruta ideal
        $routeDistance = $this->calculateDistance(
            $userLocation['lat'], $userLocation['lng'],
            $destinoLocation['lat'], $destinoLocation['lng']
        );
        
        // Filtrar vehículos que estén en la ruta o cerca del origen
        $routeVehicles = [];
        foreach ($allVehicles as $vehicle) {
            // Distancia del vehículo al punto de origen
            $distanceToOrigin = $this->calculateDistance(
                $userLocation['lat'], $userLocation['lng'],
                $vehicle['ubicacion']['lat'], $vehicle['ubicacion']['lng']
            );
            
            // Distancia del vehículo al destino
            $distanceToDestination = $this->calculateDistance(
                $vehicle['ubicacion']['lat'], $vehicle['ubicacion']['lng'],
                $destinoLocation['lat'], $destinoLocation['lng']
            );
            
            // Lógica para determinar si el vehículo es útil para la ruta
            // Un vehículo es útil si está cerca del origen O si está en la ruta hacia el destino
            $isUsefulForRoute = false;
            
            // Está cerca del origen (menos de 2km)
            if ($distanceToOrigin <= 2) {
                $isUsefulForRoute = true;
            }
            // O está en la ruta (la distancia total no es mucho mayor que la ruta directa)
            else {
                $totalDistanceViaVehicle = $distanceToOrigin + $distanceToDestination;
                $detourPercentage = (($totalDistanceViaVehicle - $routeDistance) / $routeDistance) * 100;
                
                // Si el desvío es menos del 30%, consideramos que está en la ruta
                if ($detourPercentage <= 30) {
                    $isUsefulForRoute = true;
                }
            }
            
            if ($isUsefulForRoute) {
                // Calcular relevancia del vehículo
                $relevance = 100;
                
                // Penalizar por distancia al origen
                $relevance -= ($distanceToOrigin * 10);
                
                // Bonificar si está disponible
                if ($vehicle['disponible']) {
                    $relevance += 20;
                }
                
                // Bonificar por batería/combustible
                $relevance += ($vehicle['bateria'] / 10);
                
                $vehicle['relevance'] = max(0, $relevance);
                $vehicle['distancia_origen'] = $distanceToOrigin;
                $vehicle['distancia_destino'] = $distanceToDestination;
                
                $routeVehicles[] = $vehicle;
            }
        }
        
        // Ordenar por relevancia
        usort($routeVehicles, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        return $routeVehicles;
    }
    
    private function getPopularRoutes() {
        return [
            [
                'id' => 1,
                'nombre' => 'Mollerussa → Lleida',
                'origen' => ['lat' => 41.6231, 'lng' => 0.8825, 'nombre' => 'Mollerussa Centro'],
                'destino' => ['lat' => 41.6175, 'lng' => 0.6200, 'nombre' => 'Lleida Centro'],
                'distancia' => 25.3,
                'duracion_estimada' => 22,
                'vehiculos_disponibles' => 5,
                'precio_estimado' => 15.50,
                'popularidad' => 95
            ],
            [
                'id' => 2,
                'nombre' => 'Mollerussa → Barcelona',
                'origen' => ['lat' => 41.6231, 'lng' => 0.8825, 'nombre' => 'Mollerussa Centro'],
                'destino' => ['lat' => 41.3851, 'lng' => 2.1734, 'nombre' => 'Barcelona Centro'],
                'distancia' => 140.2,
                'duracion_estimada' => 95,
                'vehiculos_disponibles' => 8,
                'precio_estimado' => 85.00,
                'popularidad' => 88
            ],
            [
                'id' => 3,
                'nombre' => 'Mollerussa → Balaguer',
                'origen' => ['lat' => 41.6231, 'lng' => 0.8825, 'nombre' => 'Mollerussa Centro'],
                'destino' => ['lat' => 41.7886, 'lng' => 0.8067, 'nombre' => 'Balaguer Centro'],
                'distancia' => 18.7,
                'duracion_estimada' => 18,
                'vehiculos_disponibles' => 3,
                'precio_estimado' => 12.00,
                'popularidad' => 72
            ],
            [
                'id' => 4,
                'nombre' => 'Mollerussa → Tàrrega',
                'origen' => ['lat' => 41.6231, 'lng' => 0.8825, 'nombre' => 'Mollerussa Centro'],
                'destino' => ['lat' => 41.6470, 'lng' => 1.1394, 'nombre' => 'Tàrrega Centro'],
                'distancia' => 24.1,
                'duracion_estimada' => 20,
                'vehiculos_disponibles' => 4,
                'precio_estimado' => 14.00,
                'popularidad' => 68
            ],
            [
                'id' => 5,
                'nombre' => 'Mollerussa → Aeroport Lleida',
                'origen' => ['lat' => 41.6231, 'lng' => 0.8825, 'nombre' => 'Mollerussa Centro'],
                'destino' => ['lat' => 41.7281, 'lng' => 0.5353, 'nombre' => 'Aeroport Alguaire'],
                'distancia' => 35.8,
                'duracion_estimada' => 28,
                'vehiculos_disponibles' => 6,
                'precio_estimado' => 22.00,
                'popularidad' => 65
            ]
        ];
    }
    
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    private function getNearbyGasStations($userLocation, $radius) {
        return [
            [
                'id' => 1,
                'nombre' => 'Estació de Servei CEPSA',
                'lat' => 41.6240,
                'lng' => 0.8830,
                'direccion' => 'Carretera N-II, Km 472, Mollerussa',
                'distancia' => 0.3,
                'precio_gasolina' => 1.42,
                'precio_diesel' => 1.38,
                'servicios' => ['Lavado', 'Tienda', 'Bar']
            ],
            [
                'id' => 2,
                'nombre' => 'Estació BP',
                'lat' => 41.6220,
                'lng' => 0.8850,
                'direccion' => 'Avinguda Catalunya, 45, Mollerussa',
                'distancia' => 0.5,
                'precio_gasolina' => 1.44,
                'precio_diesel' => 1.40,
                'servicios' => ['Tienda', 'Cajero']
            ]
        ];
    }
    
    private function getNearbyParkings($userLocation, $radius) {
        return [
            [
                'id' => 1,
                'nombre' => 'Parking Plaça Major',
                'lat' => 41.6235,
                'lng' => 0.8820,
                'direccion' => 'Plaça Major, Mollerussa',
                'distancia' => 0.1,
                'precio_hora' => 1.20,
                'plazas_disponibles' => 25,
                'plazas_totales' => 50,
                'tipo' => 'superficie'
            ],
            [
                'id' => 2,
                'nombre' => 'Parking Centre Comercial',
                'lat' => 41.6250,
                'lng' => 0.8840,
                'direccion' => 'Centre Comercial Les Comes, Mollerussa',
                'distancia' => 0.4,
                'precio_hora' => 0.80,
                'plazas_disponibles' => 180,
                'plazas_totales' => 300,
                'tipo' => 'cubierto'
            ]
        ];
    }
    
    private function quickReserveVehicle() {
        if (!isset($_POST['vehicle_id']) || !isset($_POST['duracion'])) {
            return [
                'success' => false,
                'message' => 'Datos de reserva incompletos',
                'type' => 'danger'
            ];
        }
        
        $vehicleId = intval($_POST['vehicle_id']);
        $duracion = intval($_POST['duracion']);
        $userId = $_SESSION['user_id'];
        
        // Obtener vehículo
        $vehicles = $this->getNearbyVehicles();
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
                'message' => 'Vehículo no encontrado',
                'type' => 'danger'
            ];
        }
        
        if (!$vehicle['disponible']) {
            return [
                'success' => false,
                'message' => 'El vehículo no está disponible',
                'type' => 'warning'
            ];
        }
        
        // Calcular precio total
        $precioTotal = $vehicle['precio_hora'] * $duracion;
        
        // Verificar saldo del usuario
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($userId);
        
        if ($user['saldo'] < $precioTotal) {
            return [
                'success' => false,
                'message' => "Saldo insuficiente. Necesitas {$precioTotal}€ y tienes {$user['saldo']}€",
                'type' => 'warning'
            ];
        }
        
        // Crear la ruta en horaris_rutes
        require_once __DIR__ . '/../models/HorariRutaModel.php';
        $horariRutaModel = new HorariRutaModel();
        
        $horaInicio = date('H:i:s');
        $horaFin = date('H:i:s', strtotime("+{$duracion} hours"));
        $dataRuta = date('Y-m-d');
        
        // Crear ruta para la reserva rápida
        $rutaData = [
            'user_id' => $userId,
            'origen' => $vehicle['ubicacion']['descripcion'],
            'desti' => 'Reserva Rápida - ' . $vehicle['nombre'],
            'data_ruta' => $dataRuta,
            'hora_inici' => $horaInicio,
            'hora_fi' => $horaFin,
            'vehicle_id' => null,
            'plazas_disponibles' => 1,
            'precio_euros' => $precioTotal,
            'comentaris' => "Reserva ràpida - Vehicle: {$vehicle['nombre']} - Duració: {$duracion}h",
            'origen_lat' => $vehicle['ubicacion']['lat'],
            'origen_lng' => $vehicle['ubicacion']['lng'],
            'desti_lat' => null,
            'desti_lng' => null,
            'estado' => 1
        ];
        
        $rutaId = $horariRutaModel->create($rutaData);
        
        if (!$rutaId) {
            error_log("Error al crear ruta - Datos: " . print_r($rutaData, true));
            return [
                'success' => false,
                'message' => 'Error al crear la ruta de reserva',
                'type' => 'danger'
            ];
        }
        
        error_log("Ruta creada con ID: $rutaId para usuario: $userId");
        
        // Crear la reserva
        require_once __DIR__ . '/../models/ReservaModel.php';
        $reservaModel = new ReservaModel();
        
        $reservaCreated = $reservaModel->create($userId, $rutaId, 1);
        
        if (!$reservaCreated) {
            error_log("Error al crear reserva - userId: $userId, rutaId: $rutaId");
            return [
                'success' => false,
                'message' => 'Error al crear la reserva',
                'type' => 'danger'
            ];
        }
        
        error_log("Reserva creada exitosamente para usuario: $userId, ruta: $rutaId");
        
        // Descontar el saldo del usuario
        $nuevoSaldo = $user['saldo'] - $precioTotal;
        $userModel->updateSaldo($userId, $nuevoSaldo);
        
        // Añadir bonus de DriveCoins (20% del precio)
        require_once __DIR__ . '/../models/DriveCoinModel.php';
        $driveCoinModel = new DriveCoinModel();
        $bonusDC = intval($precioTotal * 0.2);
        $driveCoinModel->addCoins($userId, $bonusDC, 'Bonus por reserva rápida');
        
        $codigoReserva = 'QR' . date('Ymd') . str_pad($rutaId, 5, '0', STR_PAD_LEFT);
        
        return [
            'success' => true,
            'message' => "Reserva realizada correctament!<br><strong>Codi: $codigoReserva</strong><br>Vehicle: {$vehicle['nombre']}<br>Duració: {$duracion}h<br>Total: {$precioTotal}€<br>Bonus: +{$bonusDC} DC",
            'type' => 'success'
        ];
    }
    
    // Endpoint para API AJAX
    public function api() {
        header('Content-Type: application/json');
        
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'vehicles':
                $userLat = $_GET['lat'] ?? null;
                $userLng = $_GET['lng'] ?? null;
                $radius = $_GET['radius'] ?? 5;
                $vehicleType = $_GET['vehicle_type'] ?? '';
                
                $userLocation = null;
                if ($userLat && $userLng) {
                    $userLocation = ['lat' => floatval($userLat), 'lng' => floatval($userLng)];
                }
                
                $vehicles = $this->getNearbyVehicles($userLocation, $radius, $vehicleType);
                echo json_encode(['success' => true, 'vehicles' => $vehicles]);
                break;
                
            case 'route_vehicles':
                $userLat = $_GET['user_lat'] ?? null;
                $userLng = $_GET['user_lng'] ?? null;
                $destinoLat = $_GET['destino_lat'] ?? null;
                $destinoLng = $_GET['destino_lng'] ?? null;
                $destinoNombre = $_GET['destino_nombre'] ?? '';
                $vehicleType = $_GET['vehicle_type'] ?? '';
                
                if (!$userLat || !$userLng || !$destinoLat || !$destinoLng) {
                    echo json_encode(['success' => false, 'message' => 'Ubicación incompleta']);
                    return;
                }
                
                $userLocation = ['lat' => floatval($userLat), 'lng' => floatval($userLng)];
                $destinoLocation = ['lat' => floatval($destinoLat), 'lng' => floatval($destinoLng), 'nombre' => $destinoNombre];
                
                $vehicles = $this->getVehiclesForRoute($userLocation, $destinoLocation, $vehicleType);
                echo json_encode(['success' => true, 'vehicles' => $vehicles]);
                break;
                
            case 'popular_routes':
                $routes = $this->getPopularRoutes();
                echo json_encode(['success' => true, 'routes' => $routes]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
        exit;
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'MapController.php') {
    $controller = new MapController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'api':
            $controller->api();
            break;
        default:
            $controller->index();
    }
}
?>