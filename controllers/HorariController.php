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
            $this->handlePostRequest();
        }

        // Manejar eliminación
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $this->deleteHorari($_GET['delete']);
        }

        // Obtener mensajes flash
        $flash = $this->getFlashMessage();
        if ($flash && isset($flash['message'])) {
            $message = $flash['message'];
            $messageType = $flash['type'];
        }
        
        // Mensajes de confirmación después de redirección
        $message = $this->getMessageFromQuery($messageType) ?: $message;
        
        // Obtener horario para edición
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $editingHorari = $this->horariModel->getHorariById($_GET['edit'], $_SESSION['user_id']);
        }
        
        // Obtener datos para la vista
        $allHoraris = $this->horariModel->getAllHoraris();
        $myHoraris = $this->horariModel->getHorarisByUserId($_SESSION['user_id']);
        $vehicles = $this->horariModel->getVehiclesList();
        $stats = $this->horariModel->getHorarisStats($_SESSION['user_id']);
        
        // Cargar la vista
        include __DIR__ . '/../views/horaris/horaris.php';
    }
    
    private function handlePostRequest() {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $this->createHorari();
                    break;
                case 'update':
                    $this->updateHorari();
                    break;
            }
        }
    }
    
    private function createHorari() {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'data_ruta' => $this->sanitizeInput($_POST['data_ruta']),
            'hora_inici' => $this->sanitizeInput($_POST['hora_inici']),
            'hora_fi' => $this->sanitizeInput($_POST['hora_fi']),
            'origen' => $this->sanitizeInput($_POST['origen']),
            'desti' => $this->sanitizeInput($_POST['desti']),
            'vehicle' => $this->sanitizeInput($_POST['vehicle']),
            'comentaris' => $this->sanitizeInput($_POST['comentaris'])
        ];
        
        if ($this->horariModel->createHorari($data)) {
            $this->redirectWithMessage('horaris.php?created=1', 'Horari creat correctament!', 'success');
        } else {
            $this->redirectWithMessage('horaris.php?error=create', 'Error al crear l\'horari', 'danger');
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
            $this->redirectWithMessage('horaris.php?updated=1', 'Horari actualitzat correctament!', 'success');
        } else {
            $this->redirectWithMessage('horaris.php?error=update', 'Error al actualitzar l\'horari', 'danger');
        }
    }
    
    private function deleteHorari($id) {
        if ($this->horariModel->deleteHorari($id, $_SESSION['user_id'])) {
            $this->redirectWithMessage('horaris.php?deleted=1', 'Horari eliminat correctament!', 'success');
        } else {
            $this->redirectWithMessage('horaris.php?error=delete', 'Error al eliminar l\'horari', 'danger');
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
                case 'create':
                    return 'Error al crear l\'horari.';
                case 'update':
                    return 'Error al actualitzar l\'horari.';
                case 'delete':
                    return 'Error al eliminar l\'horari.';
                default:
                    return 'S\'ha produït un error.';
            }
        }
        
        return '';
    }
    
    public function search() {
        header('Content-Type: application/json');
        
        $filters = [
            'date' => $_GET['date'] ?? '',
            'vehicle' => $_GET['vehicle'] ?? '',
            'location' => $_GET['location'] ?? '',
            'user' => $_GET['user'] ?? ''
        ];
        
        $tab = $_GET['tab'] ?? 'all';
        $userId = ($tab === 'my') ? $_SESSION['user_id'] : null;
        
        $results = $this->horariModel->searchHoraris($filters, $userId);
        echo json_encode($results);
        exit;
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('login.php');
    }
}
