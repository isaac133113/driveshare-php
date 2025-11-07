<?php
// Script para inicializar las tablas de chat en la base de datos
require_once 'config/Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Crear tabla de conversaciones
    $createConversationsTable = "
    CREATE TABLE IF NOT EXISTS conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_id INT NOT NULL,
        renter_id INT NOT NULL,
        owner_id INT NOT NULL,
        status ENUM('active', 'closed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (renter_id) REFERENCES usuaris(id) ON DELETE CASCADE,
        FOREIGN KEY (owner_id) REFERENCES usuaris(id) ON DELETE CASCADE,
        UNIQUE KEY unique_conversation (vehicle_id, renter_id, owner_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    // Crear tabla de mensajes
    $createMessagesTable = "
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES usuaris(id) ON DELETE CASCADE,
        INDEX idx_conversation_created (conversation_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    // Crear tabla de vehículos (simulada para el chat)
    $createVehiclesTable = "
    CREATE TABLE IF NOT EXISTS vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(100) NOT NULL,
        brand VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        price_hour DECIMAL(10,2) NOT NULL,
        price_day DECIMAL(10,2) NOT NULL,
        available BOOLEAN DEFAULT TRUE,
        location_lat DECIMAL(10,6),
        location_lng DECIMAL(10,6),
        address TEXT,
        description TEXT,
        image_url TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES usuaris(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->execute_query($createConversationsTable);
    echo "✓ Tabla 'conversations' creada correctamente.\n";
    
    $conn->execute_query($createMessagesTable);
    echo "✓ Tabla 'messages' creada correctamente.\n";
    
    $conn->execute_query($createVehiclesTable);
    echo "✓ Tabla 'vehicles' creada correctamente.\n";
    
    // Insertar algunos vehículos de ejemplo
    $insertVehicles = "
    INSERT IGNORE INTO vehicles (id, owner_id, name, type, brand, model, year, price_hour, price_day, location_lat, location_lng, address, description) VALUES
    (1, 1, 'BMW Serie 3', 'sedan', 'BMW', 'Serie 3', 2022, 25.00, 150.00, 41.6231, 0.8825, 'Plaça Major, 1, Mollerussa', 'Sedan de lujo con excelente rendimiento'),
    (2, 2, 'Volkswagen Golf', 'compacto', 'Volkswagen', 'Golf', 2023, 18.00, 108.00, 41.6245, 0.8840, 'Carrer de la Pau, 25, Mollerussa', 'Compacto versátil y económico'),
    (3, 1, 'Ford Transit', 'furgoneta', 'Ford', 'Transit', 2021, 30.00, 180.00, 41.6210, 0.8835, 'Carrer Agramunt, 10, Mollerussa', 'Furgoneta espaciosa ideal para grupos'),
    (4, 3, 'Tesla Model 3', 'electrico', 'Tesla', 'Model 3', 2023, 35.00, 210.00, 41.6200, 0.8810, 'Avinguda Catalunya, 5, Mollerussa', 'Sedan eléctrico de última generación'),
    (5, 2, 'Jeep Wrangler', 'suv', 'Jeep', 'Wrangler', 2022, 28.00, 168.00, 41.6250, 0.8850, 'Carrer Major, 15, Mollerussa', 'SUV robusto perfecto para aventuras'),
    (6, 3, 'Fiat 500', 'city', 'Fiat', '500', 2023, 15.00, 90.00, 41.6260, 0.8820, 'Plaça de l\'Església, 3, Mollerussa', 'Pequeño y ágil para la ciudad'),
    (7, 1, 'Mercedes C-Class', 'lujo', 'Mercedes-Benz', 'Clase C', 2023, 40.00, 240.00, 41.6220, 0.8805, 'Passeig de Balafia, 20, Mollerussa', 'Lujo y comodidad en un solo paquete'),
    (8, 2, 'Yamaha MT-07', 'moto', 'Yamaha', 'MT-07', 2022, 20.00, 120.00, 41.6275, 0.8795, 'Carrer del Segre, 8, Mollerussa', 'Motocicleta deportiva ágil y potente');
    ";

    $conn->execute_query($insertVehicles);
    echo "✓ Vehículos de ejemplo insertados.\n";
    
    echo "\n🎉 Sistema de chat inicializado correctamente!\n";
    echo "Puedes usar el chat para comunicarte con los propietarios de los vehículos.\n";
    
} catch (PDOException $e) {
    echo "❌ Error al crear las tablas de chat: " . $e->getMessage() . "\n";
}
?>