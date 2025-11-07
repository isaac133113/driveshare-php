<?php

class DriveCoinModel {
    private $conn;
    
    // Tipos de transacciones
    const TRANSACTION_PURCHASE = 'purchase';
    const TRANSACTION_RESERVATION = 'reservation';
    const TRANSACTION_REFUND = 'refund';
    const TRANSACTION_BONUS = 'bonus';
    
    // Tasa de conversión: 1 Euro = 10 DriveCoins
    const CONVERSION_RATE = 10;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->initializeTables();
    }
    
    /**
     * Inicializar las tablas necesarias para DriveCoins
     */
    private function initializeTables() {
        try {
            // Verificar si la tabla usuaris existe antes de crear las foreign keys
            $checkUsuaris = $this->conn->query("SHOW TABLES LIKE 'usuaris'");
            if ($checkUsuaris === false || $checkUsuaris->num_rows == 0) {
                throw new Exception("La tabla 'usuaris' no existe. Por favor, ejecuta setup_database.php primero para configurar la base de datos.");
            }
            
            // Primero crear tabla de transacciones SIN foreign key
            $createTransactionsTable = "
                CREATE TABLE IF NOT EXISTS drivecoins_transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    transaction_type ENUM('purchase', 'reservation', 'refund', 'bonus') NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    description VARCHAR(255) NOT NULL,
                    reference_id VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_transaction_type (transaction_type),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            if (!$this->conn->query($createTransactionsTable)) {
                throw new Exception("Error creando tabla drivecoins_transactions: " . $this->conn->error);
            }
            
            // Ahora intentar añadir la foreign key si no existe
            $checkForeignKey = $this->conn->query("
                SELECT COUNT(*) as count 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'drivecoins_transactions' 
                AND CONSTRAINT_NAME LIKE 'drivecoins_transactions_ibfk_%'
                AND TABLE_SCHEMA = DATABASE()
            ");
            
            if ($checkForeignKey && $checkForeignKey->fetch_assoc()['count'] == 0) {
                $addForeignKey = "
                    ALTER TABLE drivecoins_transactions 
                    ADD CONSTRAINT fk_drivecoins_user 
                    FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE
                ";
                $this->conn->query($addForeignKey); // Si falla, continuamos sin FK
            }
            
        } catch (Exception $e) {
            // Si hay algún error, log pero continúa sin FK
            error_log("DriveCoins: " . $e->getMessage());
        }
        
        // Añadir columna de DriveCoins balance a la tabla usuarios si no existe
        $checkColumn = $this->conn->query("SHOW COLUMNS FROM usuaris LIKE 'drivecoins_balance'");
        if ($checkColumn->num_rows == 0) {
            $addDriveCoinsColumn = "ALTER TABLE usuaris ADD COLUMN drivecoins_balance DECIMAL(10,2) DEFAULT 0.00";
            if (!$this->conn->query($addDriveCoinsColumn)) {
                throw new Exception("Error añadiendo columna drivecoins_balance: " . $this->conn->error);
            }
        }
        
        // Tabla para paquetes de DriveCoins
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
        
        if (!$this->conn->query($createPackagesTable)) {
            throw new Exception("Error creando tabla drivecoins_packages: " . $this->conn->error);
        }
        
        // Insertar paquetes por defecto si no existen
        $this->createDefaultPackages();
    }
    
    /**
     * Crear paquetes por defecto de DriveCoins
     */
    private function createDefaultPackages() {
        $checkPackages = "SELECT COUNT(*) as count FROM drivecoins_packages";
        $result = $this->conn->query($checkPackages);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $defaultPackages = [
                ['Starter Pack', 100, 10, 0],
                ['Popular Pack', 250, 25, 5],
                ['Premium Pack', 500, 50, 10],
                ['Mega Pack', 1000, 100, 15],
                ['Ultimate Pack', 2500, 250, 20]
            ];
            
            $stmt = $this->conn->prepare("
                INSERT INTO drivecoins_packages (name, drivecoins_amount, euro_price, bonus_percentage) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($defaultPackages as $package) {
                $stmt->bind_param("sddi", $package[0], $package[1], $package[2], $package[3]);
                $stmt->execute();
            }
        }
    }
    
    /**
     * Obtener el saldo de DriveCoins de un usuario
     */
    public function getBalance($userId) {
        $stmt = $this->conn->prepare("SELECT drivecoins_balance FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return floatval($row['drivecoins_balance']);
        }
        
        return 0.00;
    }
    
    /**
     * Actualizar el saldo de DriveCoins de un usuario
     */
    private function updateBalance($userId, $amount, $operation = 'add') {
        if ($operation === 'add') {
            $sql = "UPDATE usuaris SET drivecoins_balance = drivecoins_balance + ? WHERE id = ?";
        } else {
            $sql = "UPDATE usuaris SET drivecoins_balance = drivecoins_balance - ? WHERE id = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amount, $userId);
        return $stmt->execute();
    }
    
    /**
     * Registrar una transacción de DriveCoins
     */
    private function logTransaction($userId, $type, $amount, $description, $referenceId = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description, reference_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdss", $userId, $type, $amount, $description, $referenceId);
        return $stmt->execute();
    }
    
    /**
     * Comprar DriveCoins con euros
     */
    public function purchaseDriveCoins($userId, $packageId, $paymentMethod = 'card') {
        // Obtener información del paquete
        $stmt = $this->conn->prepare("SELECT * FROM drivecoins_packages WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $packageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Paquete no válido'];
        }
        
        $package = $result->fetch_assoc();
        
        // Calcular DriveCoins totales con bonus
        $baseCoins = $package['drivecoins_amount'];
        $bonusCoins = ($baseCoins * $package['bonus_percentage']) / 100;
        $totalCoins = $baseCoins + $bonusCoins;
        
        // Iniciar transacción
        $this->conn->begin_transaction();
        
        try {
            // Actualizar saldo del usuario
            $updateResult = $this->updateBalance($userId, $totalCoins, 'add');
            
            if (!$updateResult) {
                throw new Exception('Error al actualizar el saldo');
            }
            
            // Generar ID de referencia único
            $referenceId = 'DC' . date('Ymd') . rand(1000, 9999);
            
            // Registrar transacción de compra
            $description = "Compra de {$package['name']} - {$baseCoins} DC";
            if ($bonusCoins > 0) {
                $description .= " + {$bonusCoins} DC bonus";
            }
            
            $logResult = $this->logTransaction($userId, self::TRANSACTION_PURCHASE, $totalCoins, $description, $referenceId);
            
            if (!$logResult) {
                throw new Exception('Error al registrar la transacción');
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Compra realizada exitosamente',
                'data' => [
                    'reference_id' => $referenceId,
                    'package_name' => $package['name'],
                    'base_coins' => $baseCoins,
                    'bonus_coins' => $bonusCoins,
                    'total_coins' => $totalCoins,
                    'euro_price' => $package['euro_price'],
                    'new_balance' => $this->getBalance($userId)
                ]
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Gastar DriveCoins (para reservas)
     */
    public function spendDriveCoins($userId, $amount, $description, $referenceId = null) {
        $currentBalance = $this->getBalance($userId);
        
        if ($currentBalance < $amount) {
            return [
                'success' => false, 
                'message' => 'Saldo insuficiente de DriveCoins',
                'current_balance' => $currentBalance,
                'required_amount' => $amount
            ];
        }
        
        $this->conn->begin_transaction();
        
        try {
            // Reducir saldo
            $updateResult = $this->updateBalance($userId, $amount, 'subtract');
            
            if (!$updateResult) {
                throw new Exception('Error al actualizar el saldo');
            }
            
            // Registrar transacción
            $logResult = $this->logTransaction($userId, self::TRANSACTION_RESERVATION, -$amount, $description, $referenceId);
            
            if (!$logResult) {
                throw new Exception('Error al registrar la transacción');
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Pago realizado exitosamente',
                'new_balance' => $this->getBalance($userId)
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener paquetes disponibles de DriveCoins
     */
    public function getAvailablePackages() {
        $stmt = $this->conn->prepare("
            SELECT id, name, drivecoins_amount, euro_price, bonus_percentage 
            FROM drivecoins_packages 
            WHERE is_active = 1 
            ORDER BY euro_price ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $row['total_coins'] = $row['drivecoins_amount'] + (($row['drivecoins_amount'] * $row['bonus_percentage']) / 100);
            $packages[] = $row;
        }
        
        return $packages;
    }
    
    /**
     * Obtener historial de transacciones
     */
    public function getTransactionHistory($userId, $limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT transaction_type, amount, description, reference_id, created_at 
            FROM drivecoins_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        return $transactions;
    }
    
    /**
     * Convertir euros a DriveCoins
     */
    public static function eurosToDriveCoins($euros) {
        return $euros * self::CONVERSION_RATE;
    }
    
    /**
     * Convertir DriveCoins a euros
     */
    public static function driveCoinsToEuros($driveCoins) {
        return $driveCoins / self::CONVERSION_RATE;
    }
    
    /**
     * Añadir DriveCoins de bonus
     */
    public function addBonus($userId, $amount, $reason) {
        $this->conn->begin_transaction();
        
        try {
            $updateResult = $this->updateBalance($userId, $amount, 'add');
            
            if (!$updateResult) {
                throw new Exception('Error al actualizar el saldo');
            }
            
            $logResult = $this->logTransaction($userId, self::TRANSACTION_BONUS, $amount, "Bonus: " . $reason);
            
            if (!$logResult) {
                throw new Exception('Error al registrar la transacción');
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Bonus añadido exitosamente',
                'new_balance' => $this->getBalance($userId)
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}