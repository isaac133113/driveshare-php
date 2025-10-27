<?php
require_once __DIR__ . '/../helpers/DatabaseHelper.php';

class UserModel {
    private $dbHelper;
    private $table = 'usuaris';
    
    public function __construct() {
        $this->dbHelper = new DatabaseHelper();
    }
    
    public function getUserById($id) {
        $result = $this->dbHelper->executeQuery(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id],
            'i'
        );
        return $result ? $result->fetch_assoc() : false;
    }
    
    public function getAllUsers() {
        $result = $this->dbHelper->executeQuery("SELECT id, nom, cognoms, correu FROM {$this->table} ORDER BY nom, cognoms");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function authenticate($email, $password) {
        $result = $this->dbHelper->executeQuery(
            "SELECT id, nom, cognoms, correu, contrasenya FROM {$this->table} WHERE correu = ?",
            [$email],
            's'
        );
        
        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user['contrasenya'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function createUser($data) {
        if (!$this->validateUserData($data)) {
            return false;
        }
        
        if ($this->emailExists($data['email'])) {
            return false;
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->dbHelper->executePrepared(
            "INSERT INTO {$this->table} (nom, cognoms, correu, contrasenya) VALUES (?, ?, ?, ?)",
            [$data['nom'], $data['cognoms'], $data['email'], $hashedPassword],
            'ssss'
        );
    }
    
    public function updateUser($id, $data) {
        return $this->dbHelper->executePrepared(
            "UPDATE {$this->table} SET nom = ?, cognoms = ?, correu = ? WHERE id = ?",
            [$data['nom'], $data['cognoms'], $data['email'], $id],
            'sssi'
        );
    }
    
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE correu = ?";
        $params = [$email];
        $types = 's';
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }
        
        $result = $this->dbHelper->executeQuery($sql, $params, $types);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        }
        return false;
    }
    
    private function validateUserData($data) {
        return !empty($data['nom']) && 
               !empty($data['cognoms']) && 
               !empty($data['email']) && 
               !empty($data['password']) &&
               filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }
    
    public function validatePassword($password) {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password);
    }
    
    public function getUserStats($userId) {
        $result = $this->dbHelper->executeQuery(
            "SELECT 
                COUNT(hr.id) as total_viajes,
                COALESCE(SUM(CASE WHEN hr.data_ruta = CURDATE() THEN 1 ELSE 0 END), 0) as viajes_hoy,
                COALESCE(SUM(CASE WHEN hr.data_ruta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END), 0) as viajes_semana
             FROM usuaris u 
             LEFT JOIN horaris_rutes hr ON u.id = hr.user_id 
             WHERE u.id = ?",
            [$userId],
            'i'
        );
        
        return $result ? $result->fetch_assoc() : [
            'total_viajes' => 0,
            'viajes_hoy' => 0,
            'viajes_semana' => 0
        ];
    }
    
    public function getById($id) {
        return $this->getUserById($id);
    }
    
    public function logUserActivity($userId, $action, $ipAddress) {
        $this->dbHelper->executeQuery("
            CREATE TABLE IF NOT EXISTS user_activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES usuaris(id) ON DELETE CASCADE
            )
        ");
        
        return $this->dbHelper->executePrepared(
            "INSERT INTO user_activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)",
            [$userId, $action, $ipAddress],
            'iss'
        );
    }
    
    public function logFailedLogin($email, $ipAddress) {
        $this->dbHelper->executeQuery("
            CREATE TABLE IF NOT EXISTS failed_login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        return $this->dbHelper->executePrepared(
            "INSERT INTO failed_login_attempts (email, ip_address) VALUES (?, ?)",
            [$email, $ipAddress],
            'ss'
        );
    }
    
    public function generateResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->dbHelper->executeQuery("
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->dbHelper->executePrepared(
            "UPDATE password_reset_tokens SET used = TRUE WHERE email = ?",
            [$email],
            's'
        );
        
        if ($this->dbHelper->executePrepared(
            "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)",
            [$email, $token, $expires],
            'sss'
        )) {
            return $token;
        }
        
        return false;
    }
    
    public function validateResetToken($token) {
        $result = $this->dbHelper->executeQuery(
            "SELECT email FROM password_reset_tokens 
             WHERE token = ? AND expires_at > NOW() AND used = FALSE",
            [$token],
            's'
        );
        
        return $result && $result->fetch_assoc();
    }
    
    public function resetPassword($token, $newPassword) {
        $tokenData = $this->validateResetToken($token);
        
        if (!$tokenData) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $success = $this->dbHelper->executePrepared(
            "UPDATE {$this->table} SET contrasenya = ? WHERE correu = ?",
            [$hashedPassword, $tokenData['email']],
            'ss'
        );
        
        if ($success) {
            $this->dbHelper->executePrepared(
                "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?",
                [$token],
                's'
            );
        }
        
        return $success;
    }
    
    public function updateProfile($userId, $data) {
        return $this->dbHelper->executePrepared(
            "UPDATE {$this->table} SET nom = ?, cognoms = ?, correu = ? WHERE id = ?",
            [$data['nom'], $data['cognoms'], $data['email'], $userId],
            'sssi'
        );
    }
    
    public function verifyCurrentPassword($userId, $currentPassword) {
        $result = $this->dbHelper->executeQuery(
            "SELECT contrasenya FROM {$this->table} WHERE id = ?",
            [$userId],
            'i'
        );
        
        if ($result && $user = $result->fetch_assoc()) {
            return password_verify($currentPassword, $user['contrasenya']);
        }
        
        return false;
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->dbHelper->executePrepared(
            "UPDATE {$this->table} SET contrasenya = ? WHERE id = ?",
            [$hashedPassword, $userId],
            'si'
        );
    }
    
    public function getFailedLoginAttempts($email, $timeWindow = '15 minutes') {
        $result = $this->dbHelper->executeQuery(
            "SELECT COUNT(*) as attempts FROM failed_login_attempts 
             WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$email, (int)str_replace(' minutes', '', $timeWindow)],
            'si'
        );
        
        return $result ? $result->fetch_assoc()['attempts'] : 0;
    }
    
    public function isAccountLocked($email) {
        $attempts = $this->getFailedLoginAttempts($email);
        return $attempts >= 5;
    }
    
    public function clearFailedAttempts($email) {
        return $this->dbHelper->executePrepared(
            "DELETE FROM failed_login_attempts WHERE email = ?",
            [$email],
            's'
        );
    }
    
    public function getUserActivity($userId, $limit = 10) {
        $result = $this->dbHelper->executeQuery(
            "SELECT action, ip_address, created_at 
             FROM user_activity_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit],
            'ii'
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getLastInsertId() {
        return $this->dbHelper->getLastInsertId();
    }
}
?>