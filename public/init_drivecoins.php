<?php
/**
 * Script para inicializar el sistema DriveCoins
 * Ejecutar una sola vez para configurar las tablas necesarias
 */

require_once 'config/Database.php';

function initializeDriveCoinsSystem() {
    try {
        $conn = Database::getInstance()->getConnection();
        
        echo "🚗 Inicializando sistema DriveCoins...\n\n";
        
        // 1. Crear tabla de transacciones DriveCoins
        echo "📊 Creando tabla drivecoins_transactions...\n";
        $createTransactionsTable = "
            CREATE TABLE IF NOT EXISTS drivecoins_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                transaction_type ENUM('purchase', 'reservation', 'refund', 'bonus') NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                description VARCHAR(255) NOT NULL,
                reference_id VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_transaction_type (transaction_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->query($createTransactionsTable)) {
            echo "✅ Tabla drivecoins_transactions creada exitosamente\n";
        } else {
            echo "❌ Error creando tabla drivecoins_transactions: " . $conn->error . "\n";
        }
        
        // 2. Añadir columna drivecoins_balance a usuarios
        echo "💰 Añadiendo columna drivecoins_balance a usuarios...\n";
        
        // Verificar si la columna ya existe
        $checkColumn = $conn->query("SHOW COLUMNS FROM usuaris LIKE 'drivecoins_balance'");
        if ($checkColumn->num_rows == 0) {
            $addBalanceColumn = "ALTER TABLE usuaris ADD COLUMN drivecoins_balance DECIMAL(10,2) DEFAULT 0.00";
            if ($conn->query($addBalanceColumn)) {
                echo "✅ Columna drivecoins_balance añadida exitosamente\n";
            } else {
                echo "❌ Error añadiendo columna drivecoins_balance: " . $conn->error . "\n";
            }
        } else {
            echo "ℹ️ Columna drivecoins_balance ya existe\n";
        }
        
        // 3. Crear tabla de paquetes DriveCoins
        echo "📦 Creando tabla drivecoins_packages...\n";
        $createPackagesTable = "
            CREATE TABLE IF NOT EXISTS drivecoins_packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                drivecoins_amount DECIMAL(10,2) NOT NULL,
                euro_price DECIMAL(10,2) NOT NULL,
                bonus_percentage INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->query($createPackagesTable)) {
            echo "✅ Tabla drivecoins_packages creada exitosamente\n";
        } else {
            echo "❌ Error creando tabla drivecoins_packages: " . $conn->error . "\n";
        }
        
        // 4. Insertar paquetes por defecto
        echo "🎁 Insertando paquetes por defecto...\n";
        $checkPackages = "SELECT COUNT(*) as count FROM drivecoins_packages";
        $result = $conn->query($checkPackages);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $defaultPackages = [
                ['Starter Pack', 100, 10, 0, '🚗 Perfecto para empezar'],
                ['Popular Pack', 250, 24, 5, '⭐ El más elegido - 5% bonus'],
                ['Premium Pack', 500, 45, 10, '💎 Valor excepcional - 10% bonus'],
                ['Mega Pack', 1000, 85, 15, '🔥 Mejor oferta - 15% bonus'],
                ['Ultimate Pack', 2500, 200, 20, '👑 Máximo ahorro - 20% bonus']
            ];
            
            $stmt = $conn->prepare("
                INSERT INTO drivecoins_packages (name, drivecoins_amount, euro_price, bonus_percentage) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($defaultPackages as $package) {
                $stmt->bind_param("sddi", $package[0], $package[1], $package[2], $package[3]);
                if ($stmt->execute()) {
                    echo "  ✅ {$package[0]} - {$package[1]} DC por €{$package[2]}\n";
                } else {
                    echo "  ❌ Error insertando {$package[0]}: " . $stmt->error . "\n";
                }
            }
        } else {
            echo "ℹ️ Los paquetes ya existen en la base de datos\n";
        }
        
        // 5. Dar DriveCoins de bienvenida a usuarios existentes
        echo "🎉 Asignando DriveCoins de bienvenida...\n";
        $updateExistingUsers = "
            UPDATE usuaris 
            SET drivecoins_balance = 50 
            WHERE drivecoins_balance = 0 AND id IN (SELECT * FROM (SELECT id FROM usuaris LIMIT 100) AS temp)
        ";
        
        if ($conn->query($updateExistingUsers)) {
            $affectedRows = $conn->affected_rows;
            echo "✅ {$affectedRows} usuarios recibieron 50 DriveCoins de bienvenida\n";
        } else {
            echo "❌ Error asignando DriveCoins de bienvenida: " . $conn->error . "\n";
        }
        
        // 6. Crear índices para optimización
        echo "⚡ Creando índices de optimización...\n";
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_drivecoins_balance ON usuaris(drivecoins_balance)",
            "CREATE INDEX IF NOT EXISTS idx_package_price ON drivecoins_packages(euro_price)",
            "CREATE INDEX IF NOT EXISTS idx_transaction_date ON drivecoins_transactions(created_at DESC)"
        ];
        
        foreach ($indexes as $index) {
            if ($conn->query($index)) {
                echo "  ✅ Índice creado\n";
            } else {
                echo "  ⚠️ Índice ya existe o error: " . $conn->error . "\n";
            }
        }
        
        echo "\n🎊 ¡Sistema DriveCoins inicializado exitosamente!\n\n";
        echo "📋 Resumen:\n";
        echo "   💰 Tasa de conversión: 1 Euro = 10 DriveCoins\n";
        echo "   🎁 Paquetes disponibles: 5 opciones con bonificaciones\n";
        echo "   👥 Usuarios existentes: 50 DriveCoins de bienvenida\n";
        echo "   📊 Sistema de transacciones completo\n\n";
        
        // Mostrar estadísticas
        $stats = [
            'usuarios_total' => $conn->query("SELECT COUNT(*) as count FROM usuaris")->fetch_assoc()['count'],
            'usuarios_con_drivecoins' => $conn->query("SELECT COUNT(*) as count FROM usuaris WHERE drivecoins_balance > 0")->fetch_assoc()['count'],
            'paquetes_disponibles' => $conn->query("SELECT COUNT(*) as count FROM drivecoins_packages WHERE is_active = 1")->fetch_assoc()['count'],
            'total_drivecoins_circulacion' => $conn->query("SELECT SUM(drivecoins_balance) as total FROM usuaris")->fetch_assoc()['total'] ?: 0
        ];
        
        echo "📈 Estadísticas del sistema:\n";
        echo "   👥 Usuarios totales: {$stats['usuarios_total']}\n";
        echo "   💰 Usuarios con DriveCoins: {$stats['usuarios_con_drivecoins']}\n";
        echo "   📦 Paquetes activos: {$stats['paquetes_disponibles']}\n";
        echo "   🪙 DriveCoins en circulación: " . number_format($stats['total_drivecoins_circulacion'], 0, ',', '.') . " DC\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "💥 Error inicializando DriveCoins: " . $e->getMessage() . "\n";
        return false;
    }
}

// Ejecutar si se llama directamente
if (basename($_SERVER['SCRIPT_NAME']) === 'init_drivecoins.php') {
    initializeDriveCoinsSystem();
}
?>