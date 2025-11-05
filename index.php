<?php
// Archivo principal de DriveShare
require_once 'config/Database.php';

// Verificar si la base de datos está configurada
try {
    $conn = Database::getInstance()->getConnection();
    
    // Verificar si las tablas existen
    $checkUsuaris = $conn->query("SHOW TABLES LIKE 'usuaris'");
    $checkDriveCoins = $conn->query("SHOW TABLES LIKE 'drivecoins_packages'");
    
    if ($checkUsuaris->num_rows == 0 || $checkDriveCoins->num_rows == 0) {
        // Base de datos no configurada, redirigir a setup
        header('Location: setup_complete.php');
        exit();
    }
    
    // Base de datos configurada, redirigir al login
    header('Location: views/horaris/login.php');
    exit();
    
} catch (Exception $e) {
    // Error de conexión, redirigir a setup
    header('Location: setup_complete.php');
    exit();
}
?>