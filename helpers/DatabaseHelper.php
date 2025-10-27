<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

class DatabaseHelper {
    private $db;
    private $connection;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    public function executeQuery($sql, $params = [], $types = '') {
        try {
            if (empty($params)) {
                return $this->connection->query($sql);
            } else {
                $stmt = $this->connection->prepare($sql);
                if ($stmt && !empty($types)) {
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    return $stmt->get_result();
                }
            }
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function executePrepared($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            if ($stmt) {
                if (!empty($params) && !empty($types)) {
                    $stmt->bind_param($types, ...$params);
                }
                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
}
?>