<?php
session_start();

// Buffer de salida para evitar problemas de headers
ob_start();

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VehicleController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/RutesController.php';
require_once __DIR__ . '/../controllers/HorariController.php';
require_once __DIR__ . '/../controllers/MapController.php';

// Obtener la ruta y acción desde la URL
$controllerName = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

// Debug: Mostrar qué controlador y acción se está intentando ejecutar
error_log("Controller: $controllerName, Action: $action");

// Determinar qué controlador cargar
switch ($controllerName) {
    case 'auth':
        $ctrl = new AuthController();
        break;
    case 'vehicle':
        $ctrl = new VehicleController();
        break;
    case 'dashboard':
        $ctrl = new DashboardController();
        break;
    case 'rutes':
        $ctrl = new RutesController();
        break;
    case 'horaris':
        $ctrl = new HorariController();
        break;
    case 'map':
        $ctrl = new MapController();
        break;
    default:
        ob_end_clean();
        http_response_code(404);
        echo "Controlador no encontrado: $controllerName";
        exit;
}

// Ejecutar la acción del controlador
if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    ob_end_clean();
    http_response_code(404);
    echo "Acción no encontrada: $action en el controlador $controllerName";
    exit;
}

ob_end_flush();


