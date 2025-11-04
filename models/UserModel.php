<?php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM usuaris WHERE correu = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['contrasenya'])) {
                return $user;
            }
        }
        return false;
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUser($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO usuaris (nom, cognoms, correu, contrasenya, data_registre) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $data['nom'], $data['cognoms'], $data['email'], $hashedPassword);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM usuaris WHERE correu = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Obtener usuario por ID (alias para compatibilidad)
     */
    public function getUserById($id) {
        return $this->getById($id);
    }
    
    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuaris WHERE correu = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile($userId, $data) {
        $stmt = $this->db->prepare("UPDATE usuaris SET nom = ?, cognoms = ?, correu = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data['nom'], $data['cognoms'], $data['email'], $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Validar contraseña según criterios de seguridad
     */
    public function validatePassword($password) {
        // Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password);
    }
    
    /**
     * Verificar contraseña actual del usuario
     */
    public function verifyCurrentPassword($userId, $password) {
        $stmt = $this->db->prepare("SELECT contrasenya FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            return password_verify($password, $user['contrasenya']);
        }
        return false;
    }
    
    /**
     * Actualizar contraseña del usuario
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE usuaris SET contrasenya = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Generar token de reset de contraseña
     */
    public function generateResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("UPDATE usuaris SET reset_token = ?, reset_token_expires = ? WHERE correu = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }
    
    /**
     * Validar token de reset
     */
    public function validateResetToken($token) {
        $stmt = $this->db->prepare("SELECT id FROM usuaris WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Reset de contraseña con token
     */
    public function resetPassword($token, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE usuaris SET contrasenya = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("ss", $hashedPassword, $token);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    
    /**
     * Registrar actividad del usuario
     */
    public function logUserActivity($userId, $action, $ipAddress = null) {
        $ipAddress = $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $this->db->prepare("INSERT INTO user_activity (user_id, action, ip_address, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $userId, $action, $ipAddress);
        
        return $stmt->execute();
    }
    
    /**
     * Registrar intento de login fallido
     */
    public function logFailedLogin($email, $ipAddress = null) {
        $ipAddress = $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $this->db->prepare("INSERT INTO failed_logins (email, ip_address, attempted_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $email, $ipAddress);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas del usuario
     */
    public function getUserStats($userId) {
        $stats = [];
        
        // Total de horarios
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM horaris_rutes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_horaris'] = $result->fetch_assoc()['total'];
        
        // Horarios este mes
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM horaris_rutes WHERE user_id = ? AND MONTH(data_ruta) = MONTH(CURDATE()) AND YEAR(data_ruta) = YEAR(CURDATE())");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['horaris_mes'] = $result->fetch_assoc()['total'];
        
        // Fecha de registro
        $stmt = $this->db->prepare("SELECT data_registre FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stats['member_since'] = $user['data_registre'];
        
        return $stats;
    }
    
    /**
     * Obtener actividad reciente del usuario
     */
    public function getUserActivity($userId, $limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM user_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtener todos los usuarios (para administración)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT id, nom, cognoms, correu, data_registre FROM usuaris ORDER BY data_registre DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Buscar usuarios
     */
    public function searchUsers($query, $limit = 10) {
        $searchTerm = "%$query%";
        $stmt = $this->db->prepare("SELECT id, nom, cognoms, correu FROM usuaris WHERE nom LIKE ? OR cognoms LIKE ? OR correu LIKE ? LIMIT ?");
        $stmt->bind_param("sssi", $searchTerm, $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin($userId) {
        $stmt = $this->db->prepare("SELECT is_admin FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            return (bool)$user['is_admin'];
        }
        return false;
    }
    
    /**
     * Activar/desactivar usuario
     */
    public function toggleUserStatus($userId) {
        $stmt = $this->db->prepare("UPDATE usuaris SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUser($userId) {
        // Primero eliminar datos relacionados
        $this->db->prepare("DELETE FROM user_activity WHERE user_id = ?")->execute([$userId]);
        $this->db->prepare("DELETE FROM horaris_rutes WHERE user_id = ?")->execute([$userId]);
        
        // Luego eliminar el usuario
        $stmt = $this->db->prepare("DELETE FROM usuaris WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        return $stmt->execute();
    }
}
?>