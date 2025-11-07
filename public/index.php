<?php
/**
 * DriveShare - Sistema de Car Sharing
 * Punto de entrada principal de la aplicación
 */

// Iniciar sesión
session_start();

// Definir rutas base
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/Controllers/' . $class . '.php',
        APP_PATH . '/Models/' . $class . '.php',
        APP_PATH . '/Helpers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Cargar configuración
require_once APP_PATH . '/Config/config.php';
require_once APP_PATH . '/Config/Database.php';

// Router simple
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/public', '', $path); // Remover /public si existe

// Rutas principales
switch ($path) {
    case '/':
    case '/dashboard':
        if (isset($_SESSION['user_id'])) {
            require_once '../dashboard.php';
        } else {
            require_once APP_PATH . '/Views/horaris/login.php';
        }
        break;
        
    case '/login':
        require_once APP_PATH . '/Views/horaris/login.php';
        break;
        
    case '/register':
        require_once APP_PATH . '/Views/horaris/registre.php';
        break;
        
    case '/vehicles':
    case '/ver-coches':
        require_once APP_PATH . '/Controllers/VehicleController.php';
        break;
        
    case '/map':
    case '/buscar-coche':
        require_once APP_PATH . '/Controllers/MapController.php';
        break;
        
    case '/drivecoins':
    case '/comprar-drivecoins':
        require_once APP_PATH . '/Controllers/DriveCoinController.php';
        break;
        
    case '/chat':
        require_once APP_PATH . '/Controllers/ChatController.php';
        break;
        
    case '/horaris':
        require_once APP_PATH . '/Controllers/HorariController.php';
        break;
        
    case '/auth':
        require_once APP_PATH . '/Controllers/AuthController.php';
        break;
        
    default:
        // Si no encuentra la ruta, mostrar 404
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Página no encontrada - DriveShare</title>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6 text-center'>
                        <h1 class='display-1'>404</h1>
                        <h2>Página no encontrada</h2>
                        <p class='lead'>La página que buscas no existe.</p>
                        <a href='/' class='btn btn-primary'>Volver al inicio</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        break;
}
?>