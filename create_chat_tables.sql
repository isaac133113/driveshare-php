-- Script SQL para crear las tablas del sistema de chat
-- Ejecuta esto en phpMyAdmin o tu cliente MySQL

USE aplicaciocompra;

-- Crear tabla de conversaciones
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

-- Crear tabla de mensajes
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

-- Verificar que las tablas se crearon correctamente
SHOW TABLES LIKE '%conversation%';
SHOW TABLES LIKE '%message%';

-- Mostrar estructura de las tablas creadas
DESCRIBE conversations;
DESCRIBE messages;