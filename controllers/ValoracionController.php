<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ValoracionModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/HorariModel.php';

class ValoracionController extends BaseController {
    private $valoracionModel;
    private $reservaModel;
    private $horariModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->valoracionModel = new ValoracionModel();
        $this->reservaModel = new ReservaModel();
        $this->horariModel = new HorariModel();
    }
    
    public function index() {
        $message = '';
        $messageType = '';
        
        // Obtener valoraciones pendientes
        $valoracionesPendientes = $this->valoracionModel->getValoracionesPendientes($_SESSION['user_id']);
        
        // Obtener top conductores
        $topConductores = $this->valoracionModel->getTopConductores(10);
        
        // Obtener mis valoraciones recibidas (como conductor)
        $misValoracionesRecibidas = $this->valoracionModel->getByConductor($_SESSION['user_id']);

        // Calcular media de mis valoraciones recibidas
        $mediaMisValoraciones = 0;
        $totalMisValoraciones = 0;
        if (count($misValoracionesRecibidas) > 0) {
            $suma = array_sum(array_column($misValoracionesRecibidas, 'puntuacion'));
            $totalMisValoraciones = count($misValoracionesRecibidas);
            $mediaMisValoraciones = round($suma / $totalMisValoraciones, 1);
        }

        // Obtener mis valoraciones dadas
        $misValoracionesDadas = $this->getMisValoracionesDadas();
        
        include __DIR__ . '/../views/valoracions/index.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../public/index.php?controller=valoracion&action=index');
            return;
        }
        
        $rutaId = intval($_POST['ruta_id']);
        $conductorId = intval($_POST['conductor_id']);
        $puntuacion = intval($_POST['puntuacion']);
        $comentario = $this->sanitizeInput($_POST['comentario'] ?? '');
        $userId = $_SESSION['user_id'];
        
        // Validaciones
        if ($puntuacion < 1 || $puntuacion > 5) {
            $_SESSION['message'] = 'La puntuació ha de ser entre 1 i 5 estrelles';
            $_SESSION['messageType'] = 'danger';
            header('Location: ../../public/index.php?controller=valoracion&action=index');
            return;
        }
        
        if ($conductorId === $userId) {
            $_SESSION['message'] = 'No pots valorar-te a tu mateix';
            $_SESSION['messageType'] = 'warning';
            header('Location: ../../public/index.php?controller=valoracion&action=index');
            return;
        }
        
        // Verificar que el usuario haya hecho una reserva para esta ruta
        $reservas = $this->reservaModel->getUserReservations($userId);
        $hasReservation = false;
        foreach ($reservas as $reserva) {
            if ($reserva['ruta_id'] == $rutaId) {
                $hasReservation = true;
                break;
            }
        }
        
        if (!$hasReservation) {
            $_SESSION['message'] = 'Només pots valorar rutes que has reservat';
            $_SESSION['messageType'] = 'warning';
            header('Location: ../../public/index.php?controller=valoracion&action=index');
            return;
        }
        
        // Crear o actualizar valoración
        if ($this->valoracionModel->create($rutaId, $userId, $conductorId, $puntuacion, $comentario)) {
            $_SESSION['message'] = 'Valoració enviada correctament!';
            $_SESSION['messageType'] = 'success';
        } else {
            $_SESSION['message'] = 'Error al enviar la valoració';
            $_SESSION['messageType'] = 'danger';
        }
        
        header('Location: ../../public/index.php?controller=valoracion&action=index');
    }
    
    public function delete() {
        if (!isset($_GET['id'])) {
            $_SESSION['message'] = 'ID de valoració no especificat';
            $_SESSION['messageType'] = 'danger';
            header('Location: ../../public/index.php?controller=valoracion&action=index');
            return;
        }
        
        $id = intval($_GET['id']);
        $userId = $_SESSION['user_id'];
        
        if ($this->valoracionModel->delete($id, $userId)) {
            $_SESSION['message'] = 'Valoració eliminada correctament';
            $_SESSION['messageType'] = 'success';
        } else {
            $_SESSION['message'] = 'Error al eliminar la valoració';
            $_SESSION['messageType'] = 'danger';
        }
        
        header('Location: ../../public/index.php?controller=valoracion&action=index');
    }
    
    public function getRutaValoraciones() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['ruta_id'])) {
            echo json_encode(['error' => 'ID de ruta no especificado']);
            return;
        }
        
        $rutaId = intval($_GET['ruta_id']);
        
        $valoraciones = $this->valoracionModel->getByRuta($rutaId);
        $promedio = $this->valoracionModel->getPromedioByRuta($rutaId);
        
        echo json_encode([
            'valoraciones' => $valoraciones,
            'promedio' => $promedio['promedio'],
            'total' => $promedio['total']
        ]);
    }
    
    private function getMisValoracionesRecibidas() {
        // Obtener mis rutas y sus valoraciones
        $misRutas = $this->horariModel->getHorarisByUserId($_SESSION['user_id']);
        $valoracionesRecibidas = [];
        
        foreach ($misRutas as $ruta) {
            $valoraciones = $this->valoracionModel->getByRuta($ruta['id']);
            foreach ($valoraciones as $valoracion) {
                $valoracion['ruta_info'] = $ruta;
                $valoracionesRecibidas[] = $valoracion;
            }
        }
        
        // Ordenar por fecha más reciente
        usort($valoracionesRecibidas, function($a, $b) {
            return strtotime($b['fecha_valoracion']) - strtotime($a['fecha_valoracion']);
        });
        
        return $valoracionesRecibidas;
    }
    
    private function getMisValoracionesDadas() {
        return $this->valoracionModel->getValoracionesDadasByUser($_SESSION['user_id']);
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'ValoracionController.php') {
    $controller = new ValoracionController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'delete':
            $controller->delete();
            break;
        case 'api_valoraciones':
            $controller->getRutaValoraciones();
            break;
        default:
            $controller->index();
    }
}
?>