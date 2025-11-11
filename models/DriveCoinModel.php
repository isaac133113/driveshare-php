<?php
require_once __DIR__ . '/../config/Database.php';

class DriveCoinModel {
    private $conn;

    const TRANSACTION_RESERVATION = 'reservation';
    const TRANSACTION_BONUS = 'bonus';

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

            $stmt2 = $this->conn->prepare(
                "INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description) VALUES (?, ?, ?, ?)"
            );
            $type = self::TRANSACTION_BONUS;
            $stmt2->bind_param("isds", $userId, $type, $amount, $description);
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

            $stmt2 = $this->conn->prepare(
                "INSERT INTO drivecoins_transactions (user_id, transaction_type, amount, description) VALUES (?, ?, ?, ?)"
            );
            $type = self::TRANSACTION_RESERVATION;
            $stmt2->bind_param("isds", $userId, $type, $amount, $description);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
