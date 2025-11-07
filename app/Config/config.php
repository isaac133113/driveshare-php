<?php
// Configuración general de la aplicación
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aplicaciocompra');

define('BASE_URL', '/PHP/');
define('APP_NAME', 'DriveShare');

// Configurar zona horaria
date_default_timezone_set('Europe/Madrid');

// Configurar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader simple
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?> 