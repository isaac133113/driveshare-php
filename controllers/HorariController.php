<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/HorariModel.php';

class HorariController extends BaseController {
    private $horariModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->horariModel = new HorariModel();
    }
    
    public function index() {
        // Manejar logout
        if (isset($_GET['logout'])) {
            $this->logout();
        }
        
        // Variables
        $message = '';
        $messageType = '';
        $editingHorari = null;

        // Procesar operaciones CRUD
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handlePostRequest();
            $message = $result['message'];
            $messageType = $result['type'];
        }

        // Manejar eliminación
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $result = $this->deleteHorari($_GET['delete']);
            $message = $result['message'];
            $messageType = $result['type'];
        }
        
        // Obtener horario para edición
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $editingHorari = $this->horariModel->getHorariById($_GET['edit'], $_SESSION['user_id']);
            if (!$editingHorari) {
                $message = 'Horari no trobat o no tens permisos per editar-lo';
                $messageType = 'danger';
            }
        }
        
        // Obtener datos para la vista
        $allHoraris = $this->horariModel->getAllHoraris();
        $myHoraris = $this->horariModel->getHorarisByUserId($_SESSION['user_id']);
        
        // Añadir valoraciones a mis rutas
        require_once __DIR__ . '/../models/ValoracionModel.php';
        $valoracionModel = new ValoracionModel();
        foreach ($myHoraris as &$horari) {
            $promedio = $valoracionModel->getPromedioByRuta($horari['id']);
            $horari['valoracion_promedio'] = $promedio['promedio'];
            $horari['valoracion_total'] = $promedio['total'];
        }
        
        // Obtener reservas del usuario
        require_once __DIR__ . '/../models/ReservaModel.php';
        $reservaModel = new ReservaModel();
        $myReservations = $reservaModel->getUserReservationsWithDetails($_SESSION['user_id']);
        
        $vehicles = $this->horariModel->getVehiclesList();
        $stats = $this->horariModel->getHorarisStats($_SESSION['user_id']);
        
        // Cargar la vista
        include_once __DIR__ . '/../views/horaris/horaris.php';
    }
    
    private function handlePostRequest() {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    return $this->createHorari();
                case 'update':
                    return $this->updateHorari();
                default:
                    return ['message' => 'Acció no vàlida', 'type' => 'danger'];
            }
        }
        return ['message' => '', 'type' => ''];
    }
    
    private function createHorari() {
        try {
            // Validar campos requeridos
            $requiredFields = ['data_ruta', 'hora_inici', 'hora_fi', 'origen', 'desti', 'vehicle'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    return ['message' => "El camp {$field} és obligatori", 'type' => 'danger'];
                }
            }

            $data = [
                'user_id' => $_SESSION['user_id'],
                'data_ruta' => $this->sanitizeInput($_POST['data_ruta']),
                'hora_inici' => $this->sanitizeInput($_POST['hora_inici']),
                'hora_fi' => $this->sanitizeInput($_POST['hora_fi']),
                'origen' => $this->sanitizeInput($_POST['origen']),
                'desti' => $this->sanitizeInput($_POST['desti']),
                'vehicle' => $this->sanitizeInput($_POST['vehicle']),
                'comentaris' => $this->sanitizeInput($_POST['comentaris'] ?? '')
            ];
            
            if ($this->horariModel->createHorari($data)) {
                return ['message' => 'Horari creat correctament!', 'type' => 'success'];
            } else {
                return ['message' => 'Error al crear l\'horari', 'type' => 'danger'];
            }
        } catch (Exception $e) {
            error_log("Error en createHorari: " . $e->getMessage());
            return ['message' => 'Error al crear l\'horari: ' . $e->getMessage(), 'type' => 'danger'];
        }
    }
    
    private function updateHorari() {
        try {
            if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                return ['message' => 'ID de horari no vàlid', 'type' => 'danger'];
            }

            $id = (int)$_POST['id'];
            
            // Validar campos requeridos
            $requiredFields = ['data_ruta', 'hora_inici', 'hora_fi', 'origen', 'desti', 'vehicle'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    return ['message' => "El camp {$field} és obligatori", 'type' => 'danger'];
                }
            }

            $data = [
                'data_ruta' => $this->sanitizeInput($_POST['data_ruta']),
                'hora_inici' => $this->sanitizeInput($_POST['hora_inici']),
                'hora_fi' => $this->sanitizeInput($_POST['hora_fi']),
                'origen' => $this->sanitizeInput($_POST['origen']),
                'desti' => $this->sanitizeInput($_POST['desti']),
                'vehicle' => $this->sanitizeInput($_POST['vehicle']),
                'comentaris' => $this->sanitizeInput($_POST['comentaris'] ?? '')
            ];
            
            if ($this->horariModel->updateHorari($id, $_SESSION['user_id'], $data)) {
                return ['message' => 'Horari actualitzat correctament!', 'type' => 'success'];
            } else {
                return ['message' => 'Error al actualitzar l\'horari o no tens permisos', 'type' => 'danger'];
            }
        } catch (Exception $e) {
            error_log("Error en updateHorari: " . $e->getMessage());
            return ['message' => 'Error al actualitzar l\'horari: ' . $e->getMessage(), 'type' => 'danger'];
        }
    }
    
    private function deleteHorari($id) {
        try {
            if ($this->horariModel->deleteHorari($id, $_SESSION['user_id'])) {
                return ['message' => 'Horari eliminat correctament!', 'type' => 'success'];
            } else {
                return ['message' => 'Error al eliminar l\'horari o no tens permisos', 'type' => 'danger'];
            }
        } catch (Exception $e) {
            error_log("Error en deleteHorari: " . $e->getMessage());
            return ['message' => 'Error al eliminar l\'horari: ' . $e->getMessage(), 'type' => 'danger'];
        }
    }
    
    public function search() {
        header('Content-Type: application/json');
        
        try {
            $filters = [
                'date' => $_GET['date'] ?? '',
                'vehicle' => $_GET['vehicle'] ?? '',
                'location' => $_GET['location'] ?? '',
                'user' => $_GET['user'] ?? ''
            ];
            
            $tab = $_GET['tab'] ?? 'all';
            $userId = ($tab === 'my') ? $_SESSION['user_id'] : null;
            
            $results = $this->horariModel->searchHoraris($filters, $userId);
            echo json_encode(['success' => true, 'data' => $results]);
        } catch (Exception $e) {
            error_log("Error en search: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error en la cerca']);
        }
        exit;
    }
    
    public function logout() {
        session_destroy();
        $this->redirect(BASE_URL . '/public/index.php?controller=auth&action=login');
    }

    public function editModal() {
        try {
            $id = intval($_GET['id'] ?? 0);
            $ruta = $this->horariModel->getHorariById($id, $_SESSION['user_id']);
            if ($ruta) {
                include __DIR__ . '/../views/dashboard/modal_edit.php';
            } else {
                echo json_encode(['success' => false, 'message' => 'Horari no trobat']);
            }
        } catch (Exception $e) {
            error_log("Error en editModal: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al carregar l\'horari']);
        }
        exit;
    }

    public function deleteAjax() {
        header('Content-Type: application/json');
        
        try {
            $id = intval($_POST['id'] ?? 0);
            $success = $this->horariModel->deleteHorari($id, $_SESSION['user_id']);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Horari eliminat correctament' : 'Error al eliminar l\'horari'
            ]);
        } catch (Exception $e) {
            error_log("Error en deleteAjax: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar l\'horari']);
        }
        exit;
    }

    public function updateAjax() {
        header('Content-Type: application/json');
        
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no vàlid']);
                exit;
            }

            $data = [];
            $camposPosibles = ['data_ruta', 'hora_inici', 'hora_fi', 'origen', 'desti', 'vehicle', 'comentaris', 'plazas_disponibles'];
            
            foreach ($camposPosibles as $campo) {
                if (isset($_POST[$campo])) {
                    $data[$campo] = $this->sanitizeInput($_POST[$campo]);
                }
            }

            if (empty($data)) {
                echo json_encode(['success' => false, 'message' => 'No hi ha camps per actualitzar']);
                exit;
            }

            $success = $this->horariModel->updateHorari($id, $_SESSION['user_id'], $data);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Horari actualitzat correctament' : 'Error al actualitzar l\'horari'
            ]);
        } catch (Exception $e) {
            error_log("Error en updateAjax: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualitzar l\'horari']);
        }
        exit;
    }

    public function iniciarChatPropietario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rutaId = intval($_POST['ruta_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            $userId = $_SESSION['user_id'];
            if (!$rutaId || empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            require_once __DIR__ . '/../models/HorariRutaModel.php';
            $rutaModel = new HorariRutaModel();
            $ruta = $rutaModel->getById($rutaId);
            if (!$ruta) {
                echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
                return;
            }
            $ownerId = $ruta['user_id'];
            require_once __DIR__ . '/../models/ChatModel.php';
            $chatModel = new ChatModel();
            $conversationId = $chatModel->createOrGetConversation($ruta['vehicle_id'], $userId, $ownerId);
            if (!$conversationId) {
                echo json_encode(['success' => false, 'message' => 'No se pudo crear la conversación']);
                return;
            }
            $msgId = $chatModel->sendMessage($conversationId, $userId, $message);
            if ($msgId) {
                echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo enviar el mensaje']);
            }
        }
    }
}
?>
