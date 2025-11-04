<?php
require_once __DIR__ . '/config/Database.php';

// Script para crear las tablas necesarias para DriveShare
try {
    $conn = Database::getInstance()->getConnection();
    
    echo "<h2>Configurando Base de Datos DriveShare</h2>";
    
    // 1. Crear tabla usuaris (usuarios)
    $createUsuaris = "
        CREATE TABLE IF NOT EXISTS usuaris (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            cognoms VARCHAR(150) NOT NULL,
            correu VARCHAR(255) UNIQUE NOT NULL,
            contrasenya VARCHAR(255) NOT NULL,
            data_registre TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reset_token VARCHAR(255) NULL,
            reset_token_expires DATETIME NULL,
            is_admin TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            email_notifications TINYINT(1) DEFAULT 1,
            sms_notifications TINYINT(1) DEFAULT 0,
            default_vehicle VARCHAR(50) DEFAULT '',
            saldo DECIMAL(10,2) DEFAULT 0.00,
            INDEX idx_correu (correu),
            INDEX idx_reset_token (reset_token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createUsuaris)) {
        echo "<p style='color: green;'>✓ Tabla 'usuaris' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'usuaris': " . $conn->error . "</p>";
    }
    
    // 2. Crear tabla user_activity (actividad de usuarios)
    $createUserActivity = "
        CREATE TABLE IF NOT EXISTS user_activity (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createUserActivity)) {
        echo "<p style='color: green;'>✓ Tabla 'user_activity' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'user_activity': " . $conn->error . "</p>";
    }
    
    // 3. Crear tabla failed_logins (intentos de login fallidos)
    $createFailedLogins = "
        CREATE TABLE IF NOT EXISTS failed_logins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_ip_address (ip_address),
            INDEX idx_attempted_at (attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createFailedLogins)) {
        echo "<p style='color: green;'>✓ Tabla 'failed_logins' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'failed_logins': " . $conn->error . "</p>";
    }
    
    // 4. Crear tabla horaris_rutes (horarios y rutas)
    $createHorarisRutes = "
        CREATE TABLE IF NOT EXISTS horaris_rutes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            data_ruta DATE NOT NULL,
            hora_sortida TIME NOT NULL,
            origen VARCHAR(255) NOT NULL,
            desti VARCHAR(255) NOT NULL,
            vehicle VARCHAR(100) NOT NULL,
            places_totals INT NOT NULL DEFAULT 4,
            places_disponibles INT NOT NULL DEFAULT 4,
            observacions TEXT,
            estat ENUM('actiu', 'complet', 'cancelat') DEFAULT 'actiu',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_data_ruta (data_ruta),
            INDEX idx_origen (origen),
            INDEX idx_desti (desti),
            INDEX idx_estat (estat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createHorarisRutes)) {
        echo "<p style='color: green;'>✓ Tabla 'horaris_rutes' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'horaris_rutes': " . $conn->error . "</p>";
    }
    
    // 5. Crear tabla reservas (reservas de vehículos)
    $createReservas = "
        CREATE TABLE IF NOT EXISTS reservas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            vehicle_name VARCHAR(255) NOT NULL,
            codigo_reserva VARCHAR(50) UNIQUE NOT NULL,
            fecha_inicio DATETIME NOT NULL,
            fecha_fin DATETIME NOT NULL,
            duracion_horas INT NOT NULL,
            precio_hora DECIMAL(8,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            estado ENUM('pendiente', 'confirmada', 'en_curso', 'completada', 'cancelada') DEFAULT 'pendiente',
            ubicacion_recogida JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_codigo_reserva (codigo_reserva),
            INDEX idx_fecha_inicio (fecha_inicio),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createReservas)) {
        echo "<p style='color: green;'>✓ Tabla 'reservas' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'reservas': " . $conn->error . "</p>";
    }
    
    // 6. Crear tabla vehicles (vehículos disponibles)
    $createVehicles = "
        CREATE TABLE IF NOT EXISTS vehicles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            marca VARCHAR(100) NOT NULL,
            modelo VARCHAR(100) NOT NULL,
            año YEAR NOT NULL,
            tipo ENUM('city', 'compacto', 'sedan', 'suv', 'furgoneta', 'electrico', 'lujo', 'moto') NOT NULL,
            precio_hora DECIMAL(8,2) NOT NULL,
            precio_dia DECIMAL(8,2) NOT NULL,
            combustible VARCHAR(50) NOT NULL,
            transmision ENUM('manual', 'automatica') NOT NULL,
            pasajeros INT NOT NULL,
            puertas INT NOT NULL,
            aire_acondicionado BOOLEAN DEFAULT TRUE,
            gps BOOLEAN DEFAULT FALSE,
            bluetooth BOOLEAN DEFAULT TRUE,
            disponible BOOLEAN DEFAULT TRUE,
            ubicacion_lat DECIMAL(10,8),
            ubicacion_lng DECIMAL(11,8),
            ubicacion_direccion VARCHAR(500),
            ubicacion_descripcion VARCHAR(255),
            imagen_url VARCHAR(500),
            descripcion TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tipo (tipo),
            INDEX idx_disponible (disponible),
            INDEX idx_ubicacion (ubicacion_lat, ubicacion_lng)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($createVehicles)) {
        echo "<p style='color: green;'>✓ Tabla 'vehicles' creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creando tabla 'vehicles': " . $conn->error . "</p>";
    }
    
    // 7. Insertar datos de ejemplo de vehículos
    $insertVehicles = "
        INSERT IGNORE INTO vehicles (id, nombre, marca, modelo, año, tipo, precio_hora, precio_dia, combustible, transmision, pasajeros, puertas, aire_acondicionado, gps, bluetooth, disponible, ubicacion_lat, ubicacion_lng, ubicacion_direccion, ubicacion_descripcion, imagen_url, descripcion) VALUES
        (1, 'BMW Serie 3', 'BMW', 'Serie 3', 2022, 'sedan', 25.00, 150.00, 'Gasolina', 'automatica', 5, 4, 1, 1, 1, 1, 41.6231, 0.8825, 'Plaça Major, 1, Mollerussa', 'Centro de Mollerussa', 'https://via.placeholder.com/300x200/007bff/ffffff?text=BMW+Serie+3', 'Sedan de lujo con excelente rendimiento y comodidad. Ideal para viajes de negocios y familiares.'),
        (2, 'Volkswagen Golf', 'Volkswagen', 'Golf', 2023, 'compacto', 18.00, 100.00, 'Gasolina', 'manual', 5, 5, 1, 0, 1, 1, 41.6245, 0.8840, 'Carrer de Lleida, 45, Mollerussa', 'Cerca del Instituto', 'https://via.placeholder.com/300x200/28a745/ffffff?text=VW+Golf', 'Compacto versátil y económico, perfecto para la ciudad y viajes cortos.'),
        (3, 'Ford Transit', 'Ford', 'Transit', 2021, 'furgoneta', 35.00, 200.00, 'Diesel', 'manual', 9, 4, 1, 1, 1, 1, 41.6160, 0.8910, 'Polígon Industrial, Mollerussa', 'Zona industrial', 'https://via.placeholder.com/300x200/ffc107/000000?text=Ford+Transit', 'Furgoneta espaciosa ideal para grupos grandes y mudanzas.'),
        (4, 'Tesla Model 3', 'Tesla', 'Model 3', 2023, 'electrico', 30.00, 180.00, 'Eléctrico', 'automatica', 5, 4, 1, 1, 1, 1, 41.6195, 0.8795, 'Avinguda de Catalunya, 23, Mollerussa', 'Estación de carga Tesla', 'https://via.placeholder.com/300x200/17a2b8/ffffff?text=Tesla+Model+3', 'Sedan eléctrico de última generación con tecnología avanzada y cero emisiones.'),
        (5, 'Jeep Wrangler', 'Jeep', 'Wrangler', 2022, 'suv', 28.00, 160.00, 'Gasolina', 'automatica', 5, 4, 1, 1, 1, 0, 41.6200, 0.8850, 'Avinguda de Lleida, 78, Mollerussa', 'Centro comercial', 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Jeep+Wrangler', 'SUV robusto perfecto para aventuras off-road y escapadas de fin de semana.'),
        (6, 'Fiat 500', 'Fiat', '500', 2023, 'city', 15.00, 80.00, 'Gasolina', 'manual', 4, 3, 1, 0, 1, 1, 41.6280, 0.8870, 'Carrer Sant Isidre, 12, Mollerussa', 'Zona residencial', 'https://via.placeholder.com/300x200/e83e8c/ffffff?text=Fiat+500', 'Pequeño y ágil, ideal para moverse por el centro de la ciudad.'),
        (7, 'Mercedes C-Class', 'Mercedes-Benz', 'Clase C', 2023, 'lujo', 40.00, 250.00, 'Gasolina', 'automatica', 5, 4, 1, 1, 1, 1, 41.6210, 0.8800, 'Carretera N-II, km 456, Mollerussa', 'Gasolinera Repsol', 'https://via.placeholder.com/300x200/6c757d/ffffff?text=Mercedes+C-Class', 'Sedan de lujo premium con todas las comodidades y tecnología de última generación.'),
        (8, 'Yamaha MT-07', 'Yamaha', 'MT-07', 2022, 'moto', 12.00, 60.00, 'Gasolina', 'manual', 2, 0, 0, 0, 0, 1, 41.6205, 0.8755, 'Carrer del Torrent, 8, Mollerussa', 'Cerca del parque', 'https://via.placeholder.com/300x200/fd7e14/ffffff?text=Yamaha+MT-07', 'Motocicleta deportiva ágil y potente, perfecta para desplazamientos rápidos.');
    ";
    
    if ($conn->query($insertVehicles)) {
        echo "<p style='color: green;'>✓ Datos de vehículos insertados correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error insertando datos de vehículos: " . $conn->error . "</p>";
    }
    
    // 8. Crear usuario de prueba si no existe
    $createTestUser = "
        INSERT IGNORE INTO usuaris (id, nom, cognoms, correu, contrasenya, saldo) 
        VALUES (9, 'Usuario', 'Prueba', 'test@driveshare.com', ?, 100.00)
    ";
    
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $conn->prepare($createTestUser);
    $stmt->bind_param('s', $hashedPassword);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Usuario de prueba creado (email: test@driveshare.com, password: 123456)</p>";
    } else {
        echo "<p style='color: orange;'>ℹ Usuario de prueba ya existe</p>";
    }
    
    echo "<h3 style='color: green;'>🎉 Base de datos configurada correctamente!</h3>";
    echo "<p><strong>Información de acceso de prueba:</strong></p>";
    echo "<ul>";
    echo "<li>Email: test@driveshare.com</li>";
    echo "<li>Contraseña: 123456</li>";
    echo "<li>Saldo inicial: €100.00</li>";
    echo "</ul>";
    echo "<p><a href='dashboard.php' class='btn btn-primary'>Ir al Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error configurando la base de datos: " . $e->getMessage() . "</p>";
}
?>