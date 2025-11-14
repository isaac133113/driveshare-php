<?php
require_once __DIR__ . '/../config/Database.php';

class ChatModel {
    private $conn;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Crear una nueva conversación o obtener una existente
     */
    public function createOrGetConversation($vehicleId, $renterId, $ownerId) {
        try {
            // Verificar si ya existe una conversación
            $stmt = $this->conn->prepare("
                SELECT id FROM conversations 
                WHERE vehicle_id = ? AND renter_id = ? AND owner_id = ?
            ");
            $stmt->bind_param("iii", $vehicleId, $renterId, $ownerId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($conversation = $result->fetch_assoc()) {
                return $conversation['id'];
            }
            
            // Crear nueva conversación
            $stmt = $this->conn->prepare("
                INSERT INTO conversations (vehicle_id, renter_id, owner_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iii", $vehicleId, $renterId, $ownerId);
            $stmt->execute();
            
            return $this->conn->insert_id;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error creating conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar un mensaje
     */
    public function sendMessage($conversationId, $senderId, $message) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO messages (conversation_id, sender_id, message) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $conversationId, $senderId, $message);
            $result = $stmt->execute();
            
            if ($result) {
                $messageId = $this->conn->insert_id;
                
                // Actualizar timestamp de la conversación
                $updateStmt = $this->conn->prepare("
                    UPDATE conversations SET updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $updateStmt->bind_param("i", $conversationId);
                $updateStmt->execute();
                
                return $messageId;
            }
            
            return false;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error sending message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener mensajes de una conversación
     */
    public function getMessages($conversationId, $limit = 50) {
        try {
            $stmt = $this->conn->prepare("
                SELECT m.*, u.nom as sender_name, u.email as sender_email
                FROM messages m
                JOIN usuaris u ON m.sender_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $conversationId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener conversaciones de un usuario
     */
    public function getUserConversations($userId) {
        try {
            $sql = "
                SELECT c.*, 
                       v.marca_model, v.tipus, v.preu_hora,
                       renter.nom as renter_name, renter.email as renter_email,
                       owner.nom as owner_name, owner.email as owner_email,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count,
                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
                FROM conversations c
                LEFT JOIN vehicles v ON c.vehicle_id = v.id
                LEFT JOIN usuaris renter ON c.renter_id = renter.id
                LEFT JOIN usuaris owner ON c.owner_id = owner.id
                WHERE c.renter_id = ? OR c.owner_id = ?
                ORDER BY c.updated_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                error_log('SQL Prepare failed: ' . $this->conn->error);
                throw new Exception('SQL Prepare failed: ' . $this->conn->error);
            }
            $stmt->bind_param("iii", $userId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $conversations = [];
            while ($row = $result->fetch_assoc()) {
                $conversations[] = $row;
            }
            return $conversations;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting user conversations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar mensajes como leídos
     */
    public function markMessagesAsRead($conversationId, $userId) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
            ");
            $stmt->bind_param("ii", $conversationId, $userId);
            return $stmt->execute();
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error marking messages as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener información del vehículo
     */
    public function getVehicleInfo($vehicleId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT v.*, u.nom as owner_name, u.email as owner_email, v.user_id as owner_id
                FROM vehicles v
                JOIN usuaris u ON v.user_id = u.id
                WHERE v.id = ?
            ");
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting vehicle info: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si el usuario puede acceder a la conversación
     */
    public function canAccessConversation($conversationId, $userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id FROM conversations 
                WHERE id = ? AND (renter_id = ? OR owner_id = ?)
            ");
            $stmt->bind_param("iii", $conversationId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc() !== null;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error checking conversation access: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de mensajes no leídos
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as total_unread
                FROM messages m
                JOIN conversations c ON m.conversation_id = c.id
                WHERE (c.renter_id = ? OR c.owner_id = ?) 
                AND m.sender_id != ? 
                AND m.is_read = 0
            ");
            $stmt->bind_param("iii", $userId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $row = $result->fetch_assoc();
            return $row ? $row['total_unread'] : 0;
            
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Crear o obtener conversación para un horario específico
     */
    public function createOrGetConversationForHorari($horariId, $requesterId, $ownerId) {
        try {
            // Para horarios usamos vehicle_id = 0 y añadimos el horari_id al final
            // Esto evita problemas con foreign keys y permite distinguir tipos de conversación
            $virtualVehicleId = 0;
            
            // Buscar conversación existente para este horario específico
            // Usamos una combinación única: vehicle_id=0, y verificamos por horario en los mensajes
            $stmt = $this->conn->prepare("
                SELECT c.id FROM conversations c
                WHERE c.vehicle_id = ? AND c.renter_id = ? AND c.owner_id = ?
                AND EXISTS (
                    SELECT 1 FROM messages m 
                    WHERE m.conversation_id = c.id 
                    AND m.message LIKE CONCAT('📍 Horari: %horari_', ?, '%')
                    LIMIT 1
                )
            ");
            $stmt->bind_param("iiii", $virtualVehicleId, $requesterId, $ownerId, $horariId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($conversation = $result->fetch_assoc()) {
                return $conversation['id'];
            }
            
            // Crear nueva conversación con vehicle_id = 0 para horarios
            $stmt = $this->conn->prepare("
                INSERT INTO conversations (vehicle_id, renter_id, owner_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iii", $virtualVehicleId, $requesterId, $ownerId);
            $stmt->execute();
            
            return $this->conn->insert_id;
            
        } catch (Exception $e) {
            error_log("Error creating horari conversation: " . $e->getMessage());
            return false;
        }
    }
}
?>