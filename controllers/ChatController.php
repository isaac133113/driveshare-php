<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ChatModel.php';

class ChatController extends BaseController {
    private $chatModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->chatModel = new ChatModel();
    }
    
    /**
     * Mostrar lista de conversaciones
     */
    public function index() {
        $conversations = $this->chatModel->getUserConversations($_SESSION['user_id']);
        $unreadCount = $this->chatModel->getUnreadCount($_SESSION['user_id']);
        
        include __DIR__ . '/../views/chat/index.php';
    }
    
    /**
     * Mostrar conversación específica
     */
    public function conversation() {
        $conversationId = intval($_GET['id'] ?? 0);
        
        if (!$conversationId) {
            $this->redirect('?');
        }
        
        // Verificar acceso
        if (!$this->chatModel->canAccessConversation($conversationId, $_SESSION['user_id'])) {
            $this->setFlashMessage('No tienes acceso a esta conversación.', 'danger');
            $this->redirect('?');
        }
        
        // Marcar mensajes como leídos
        $this->chatModel->markMessagesAsRead($conversationId, $_SESSION['user_id']);
        
        // Obtener mensajes
        $messages = $this->chatModel->getMessages($conversationId);
        
        // Obtener información de la conversación
        $conversations = $this->chatModel->getUserConversations($_SESSION['user_id']);
        $currentConversation = null;
        foreach ($conversations as $conv) {
            if ($conv['id'] == $conversationId) {
                $currentConversation = $conv;
                break;
            }
        }
        
        if (!$currentConversation) {
            $this->redirect('?');
        }
        
        include __DIR__ . '/../views/chat/conversation.php';
    }
    
    /**
     * Iniciar nueva conversación desde un vehículo
     */
    public function startConversation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicleId = intval($_POST['vehicle_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if (!$vehicleId || empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            // Obtener información del vehículo
            $vehicle = $this->chatModel->getVehicleInfo($vehicleId);
            if (!$vehicle) {
                echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
                return;
            }
            
            // No permitir conversación consigo mismo
            if ($vehicle['owner_id'] == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes chatear contigo mismo']);
                return;
            }
            
            // Crear o obtener conversación
            $conversationId = $this->chatModel->createOrGetConversation(
                $vehicleId, 
                $_SESSION['user_id'], 
                $vehicle['owner_id']
            );
            
            if (!$conversationId) {
                echo json_encode(['success' => false, 'message' => 'Error al crear conversación']);
                return;
            }
            
            // Enviar mensaje
            $messageId = $this->chatModel->sendMessage($conversationId, $_SESSION['user_id'], $message);
            
            if ($messageId) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Mensaje enviado correctamente',
                    'conversation_id' => $conversationId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje']);
            }
            
            return;
        }
        
        // Mostrar formulario para iniciar conversación
        $vehicleId = intval($_GET['vehicle_id'] ?? 0);
        if (!$vehicleId) {
            $this->redirect('../ver-coches.php');
        }
        
        $vehicle = $this->chatModel->getVehicleInfo($vehicleId);
        if (!$vehicle) {
            $this->setFlashMessage('Vehículo no encontrado.', 'danger');
            $this->redirect('../ver-coches.php');
        }
        
        include __DIR__ . '/../views/chat/start.php';
    }
    
    /**
     * Enviar mensaje AJAX
     */
    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $conversationId = intval($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if (!$conversationId || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Verificar acceso
        if (!$this->chatModel->canAccessConversation($conversationId, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sin acceso a esta conversación']);
            return;
        }
        
        // Enviar mensaje
        $messageId = $this->chatModel->sendMessage($conversationId, $_SESSION['user_id'], $message);
        
        if ($messageId) {
            echo json_encode([
                'success' => true,
                'message' => 'Mensaje enviado',
                'message_id' => $messageId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje']);
        }
    }
    
    /**
     * Obtener mensajes nuevos (AJAX)
     */
    public function getNewMessages() {
        $conversationId = intval($_GET['conversation_id'] ?? 0);
        $lastMessageId = intval($_GET['last_message_id'] ?? 0);
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'message' => 'ID de conversación requerido']);
            return;
        }
        
        // Verificar acceso
        if (!$this->chatModel->canAccessConversation($conversationId, $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sin acceso']);
            return;
        }
        
        // Obtener mensajes nuevos
        $messages = $this->chatModel->getMessages($conversationId);
        
        // Filtrar mensajes nuevos
        $newMessages = array_filter($messages, function($msg) use ($lastMessageId) {
            return $msg['id'] > $lastMessageId;
        });
        
        // Marcar como leídos
        if (!empty($newMessages)) {
            $this->chatModel->markMessagesAsRead($conversationId, $_SESSION['user_id']);
        }
        
        echo json_encode([
            'success' => true,
            'messages' => array_values($newMessages)
        ]);
    }
    
    /**
     * API para obtener contador de mensajes no leídos
     */
    public function getUnreadCount() {
        $count = $this->chatModel->getUnreadCount($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'ChatController.php') {
    $controller = new ChatController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'conversation':
            $controller->conversation();
            break;
        case 'start':
            $controller->startConversation();
            break;
        case 'send':
            $controller->sendMessage();
            break;
        case 'new-messages':
            $controller->getNewMessages();
            break;
        case 'unread-count':
            $controller->getUnreadCount();
            break;
        default:
            $controller->index();
    }
}
?>