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
        //    $this->deleteHorari($_GET['delete']);
        }

        // Obtener mensajes flash
        $flash = $this->getFlashMessage();
        if ($flash && isset($flash['message'])) {
            $message = $flash['message'];
            $messageType = $flash['type'];
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
    // Asegurarse de que la sesión tenga user_id válido
    $userId = $_SESSION['user_id'] ?? 0;
    if ($userId === 0) {
        $this->redirectWithMessage(
            'index.php?controller=horaris&action=index&error=create',
            'Error: usuario no autenticado',
            'danger'
        );
        return;
    }

    $data = [
        'user_id' => $userId, // <-- usar siempre SESSION
        'data_ruta' => $this->sanitizeInput($_POST['data_ruta']),
        'hora_inici' => $this->sanitizeInput($_POST['hora_inici']),
        'hora_fi' => $this->sanitizeInput($_POST['hora_fi']),
        'origen' => $this->sanitizeInput($_POST['origen']),
        'desti' => $this->sanitizeInput($_POST['desti']),
        'vehicle' => $this->sanitizeInput($_POST['vehicle']),
        'comentaris' => $this->sanitizeInput($_POST['comentaris'])
    ];
    
    if ($this->horariModel->createHorari($data)) {
        $this->redirectWithMessage(
            'index.php?controller=horaris&action=index&created=1',
            'Ruta creada correctament!',
            'success'
        );
    } else {
        $this->redirectWithMessage(
            'index.php?controller=horaris&action=index&error=create',
            'Error al crear la ruta',
            'danger'
        );
    }
}

    
    private function updateHorari() {
        $data = [
            'data_ruta' => $this->sanitizeInput($_POST['data_ruta']),
            'hora_inici' => $this->sanitizeInput($_POST['hora_inici']),
            'hora_fi' => $this->sanitizeInput($_POST['hora_fi']),
            'origen' => $this->sanitizeInput($_POST['origen']),
            'desti' => $this->sanitizeInput($_POST['desti']),
            'vehicle' => $this->sanitizeInput($_POST['vehicle']),
            'comentaris' => $this->sanitizeInput($_POST['comentaris'])
        ];
        
        $id = (int)$_POST['id'];
        
        if ($this->horariModel->updateHorari($id, $_SESSION['user_id'], $data)) {
            $this->redirectWithMessage(
                'index.php?controller=horaris&action=index&updated=1',
                'Horari actualitzat correctament!',
                'success'
            );
        } else {
            $this->redirectWithMessage(
                'index.php?controller=horaris&action=index&error=update',
                'Error al actualitzar l\'horari',
                'danger'
            );
        }
    }

    
    private function getMessageFromQuery(&$messageType) {
        if (isset($_GET['created']) && $_GET['created'] == '1') {
            $messageType = 'success';
            return 'Horari creat correctament!';
        }
        
        if (isset($_GET['updated']) && $_GET['updated'] == '1') {
            $messageType = 'success';
            return 'Horari actualitzat correctament!';
        }
        
        if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
            $messageType = 'success';
            return 'Horari eliminat correctament!';
        }
        
        if (isset($_GET['error'])) {
            $messageType = 'danger';
            switch ($_GET['error']) {
                case 'create': return 'Error al crear l\'horari.';
                case 'update': return 'Error al actualitzar l\'horari.';
                case 'delete': return 'Error al eliminar l\'horari.';
                default: return 'S\'ha produït un error.';
            }
        }
        return '';
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
}
?>