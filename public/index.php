<?php
session_start();

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VehicleController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/RutesController.php';
require_once __DIR__ . '/../controllers/HorariController.php';

// Obtener la ruta y acción desde la URL
$controllerName = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

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
    default:
        http_response_code(404);
        echo "Controlador no encontrado";
        exit;
}

// Llamar a la acción (método)
if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    http_response_code(404);
    echo "Acción no encontrada";
}