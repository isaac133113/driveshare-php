<?php
session_start();

// Buffer de salida para evitar problemas de headers
ob_start();

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VehicleController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/RutesController.php';
require_once __DIR__ . '/../controllers/HorariController.php';


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
    default:
        // Limpiar buffer antes de establecer headers
        ob_end_clean();
        http_response_code(404);
        echo "Controlador no encontrado: $controllerName";
        exit;
}

// Llamar a la acción (método)
if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    // Limpiar buffer antes de establecer headers
    ob_end_clean();
    http_response_code(404);
    echo "Acción no encontrada: $action en controlador $controllerName";
    
    // Mostrar métodos disponibles para debug
    echo "<br><br>Métodos disponibles en " . get_class($ctrl) . ":<br>";
    $methods = get_class_methods($ctrl);
    foreach ($methods as $method) {
        if (!in_array($method, ['__construct', '__destruct'])) {
            echo "- $method<br>";
        }
    }
}

// Enviar buffer si no se ha limpiado
ob_end_flush();

