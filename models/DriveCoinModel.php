<?php
require_once __DIR__ . '/../config/Database.php';

class DriveCoinModel {
    private $conn;

    const TRANSACTION_RESERVATION = 'reservation';
    const TRANSACTION_BONUS = 'bonus';
    const CONVERSION_RATE = 10; // 1 Euro = 10 DriveCoins

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->initialize();
    }

    private function initialize() {
        // Crear columna drivecoins_balance si no existe
        $checkColumn = $this->conn->query("SHOW COLUMNS FROM usuaris LIKE 'drivecoins_balance'");
        if ($checkColumn->num_rows == 0) {
            $this->conn->query("ALTER TABLE usuaris ADD COLUMN drivecoins_balance DECIMAL(10,2) DEFAULT 0.00");
        }

        // Crear tabla de transacciones si no existe
        $createTable = "
            CREATE TABLE IF NOT EXISTS drivecoins_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                transaction_type ENUM('reservation','bonus') NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                description VARCHAR(255) NOT NULL,
                reference_id VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $this->conn->query($createTable);
    }

    public function getBalance($userId) {
        $stmt = $this->conn->prepare("SELECT drivecoins_balance FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return floatval($row['drivecoins_balance']);
        }
        return 0.0;
    }

    public function addCoins($userId, $amount, $description = 'Bonus DriveCoins') {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE usuaris SET drivecoins_balance = drivecoins_balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();

            $stmt2 = $this->conn->prepare("INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description) VALUES (?, ?, ?, ?)");
            $type = self::TRANSACTION_BONUS;
            $stmt2->bind_param("idss", $userId, $amount, $type, $description);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function spendCoins($userId, $amount, $description = 'Reserva con DriveCoins') {
        $balance = $this->getBalance($userId);
        if ($balance < $amount) return false;

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE usuaris SET drivecoins_balance = drivecoins_balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();

            $stmt2 = $this->conn->prepare("INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description) VALUES (?, ?, ?, ?)");
            $type = self::TRANSACTION_RESERVATION;
            $stmt2->bind_param("idss", $userId, $amount, $type, $description);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
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
     * Gastar DriveCoins (alias para spendCoins)
     */
    public function spendDriveCoins($userId, $amount, $description = 'Reserva con DriveCoins', $referenceId = null) {
        $balance = $this->getBalance($userId);
        if ($balance < $amount) {
            return [
                'success' => false,
                'message' => 'Saldo insuficient',
                'new_balance' => $balance
            ];
        }

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE usuaris SET drivecoins_balance = drivecoins_balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $userId);
            $stmt->execute();

            $stmt2 = $this->conn->prepare("INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description, reference_id) VALUES (?, ?, ?, ?, ?)");
            $type = self::TRANSACTION_RESERVATION;
            $negativeAmount = -$amount; // Guardar como negativo para gastos
            $stmt2->bind_param("isdss", $userId, $type, $negativeAmount, $description, $referenceId);
            $stmt2->execute();

            $this->conn->commit();
            $newBalance = $this->getBalance($userId);
            return [
                'success' => true,
                'message' => 'Pagament processat correctament',
                'new_balance' => $newBalance
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al processar el pagament: ' . $e->getMessage(),
                'new_balance' => $balance
            ];
        }
    }

    /**
     * Obtener historial de transacciones
     */
    public function getTransactionHistory($userId, $limit = 50) {
        $stmt = $this->conn->prepare("SELECT * FROM drivecoins_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
