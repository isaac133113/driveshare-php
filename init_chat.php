<?php
// Script para inicializar las tablas de chat en la base de datos
require_once 'config/Database.php';

try {
    $database = Database::getInstance();
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
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES usuaris(id) ON DELETE CASCADE,
        INDEX idx_conversation_created (conversation_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($conn->query($createConversationsTable)) {
        echo "✓ Tabla 'conversations' creada correctamente.\n";
    } else {
        echo "Error creando tabla conversations: " . $conn->error . "\n";
    }
    
    if ($conn->query($createMessagesTable)) {
        echo "✓ Tabla 'messages' creada correctamente.\n";
    } else {
        echo "Error creando tabla messages: " . $conn->error . "\n";
    }
    
    echo "\n🎉 Sistema de chat inicializado correctamente!\n";
    echo "Las tablas 'conversations' y 'messages' están listas para usar.\n";
    echo "El sistema usará la tabla 'vehicles' existente en la base de datos.\n";
    
} catch (Exception $e) {
    echo "❌ Error al crear las tablas de chat: " . $e->getMessage() . "\n";
}
?>