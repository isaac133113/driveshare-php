<?php
require_once __DIR__ . '/BaseController.php';

class VehicleController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        // Obtener los vehículos del usuario actual
        $filtros = ['user_id' => $_SESSION['user_id']];
        $userVehicles = $this->getVehiclesList($filtros);
        
        // Obtener tipos de vehículos para el formulario (desde el model, en català)
        require_once __DIR__ . '/../models/VehicleModel.php';
        $vehicleModel = new VehicleModel();
        $tiposVehiculos = $vehicleModel->getTiposVehicles();
        
        $message = '';
        $messageType = '';
        
        // Procesar acciones CRUD
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($_POST['action']) {
                case 'save':
                    $result = $this->saveVehicle();
                    header("Location: ?controller=vehicle&action=index&success=" . urlencode($result['message']) . "&type=" . $result['type']);
                    exit;

                case 'delete':
                    $result = $this->deleteVehicle();
                    header("Location: ?controller=vehicle&action=index&success=" . urlencode($result['message']) . "&type=" . $result['type']);
                    exit;

                case 'upload_image':
                    $result = $this->uploadVehicleImages();
                    header("Location: ?controller=vehicle&action=index&success=" . urlencode($result['message']) . "&type=" . $result['type']);
                    exit;
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/vehicles/index.php';
    }

    private function getVehiclesList($filtros = []) {
        require_once __DIR__ . '/../models/VehicleModel.php';
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->getAllVehicles($filtros);

        // Agregar imágenes a cada vehículo
        foreach ($vehicles as &$v) {
            $v['images'] = $vehicleModel->getVehicleImages($v['id']);
            $v['images'] = array_map(fn($img) => $img['url'], $v['images']); // solo URLs
        }

        return $vehicles;
    }

    private function getTiposVehicles() {
        return [
            'sedan' => 'Sedan',
            'compacto' => 'Compacto',
            'suv' => 'SUV',
            'furgoneta' => 'Furgoneta',
            'electrico' => 'Eléctrico',
            'lujo' => 'Lujo',
            'city' => 'Urbano',
            'moto' => 'Motocicleta'
        ];
    }
    
    public function details() {
        $vehicleId = intval($_GET['id'] ?? 0);
        
        if (!$vehicleId) {
            $this->redirect('?');
        }
        
        $vehicles = $this->getVehiclesList();
        $vehicle = null;
        foreach ($vehicles as $v) {
            if ($v['id'] == $vehicleId) {
                $vehicle = $v;
                break;
            }
        }
        
        if (!$vehicle) {
            $this->redirect('?');
        }
        
        // Cargar vista de detalles
        include __DIR__ . '/../views/vehicles/details.php';
    }

    private function saveVehicle() {
        require_once __DIR__ . '/../models/VehicleModel.php';
        
        $id = $_POST['id'] ?? null;
        $data = [
            'user_id' => $_SESSION['user_id'],
            'marca_model' => $_POST['marca_model'] ?? '',
            'tipus' => $_POST['tipus'] ?? '',
            'places' => intval($_POST['places'] ?? 0),
            'transmissio' => $_POST['transmissio'] ?? '',
            'preu_hora' => floatval($_POST['preu_hora'] ?? 0),
            'descripcio' => $_POST['descripcio'] ?? ''
        ];
        
        // Validar datos requeridos
        if (empty($data['marca_model']) || empty($data['tipus']) || 
            empty($data['places']) || empty($data['transmissio']) || 
            empty($data['preu_hora'])) {
            return [
                'success' => false,
                'message' => 'Tots els camps són obligatoris',
                'type' => 'danger'
            ];
        }
        
        $vehicleModel = new VehicleModel($data);
        
        // Crear o actualizar
        if ($id) {
            // Verificar que el vehículo pertenece al usuario
            $existingVehicle = $vehicleModel->getVehicleById($id);
            if (!$existingVehicle || $existingVehicle['user_id'] != $_SESSION['user_id']) {
                return [
                    'success' => false,
                    'message' => 'No tens permís per editar aquest vehicle',
                    'type' => 'danger'
                ];
            }
            
            $data['id'] = $id;
            $success = $vehicleModel->update($data);
            $message = 'Vehicle actualitzat correctament';
        } else {
            $success = $vehicleModel->create($data);
            $message = 'Vehicle creat correctament';
        }
        
        return [
            'success' => $success,
            'message' => $success ? $message : 'Error al guardar el vehicle',
            'type' => $success ? 'success' : 'danger'
        ];
    }
    
    private function deleteVehicle() {
        require_once __DIR__ . '/../models/VehicleModel.php';
        
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de vehicle no vàlid',
                'type' => 'danger'
            ];
        }
        
        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->getVehicleById($id);
        
        // Verificar que el vehículo pertenece al usuario
        if (!$vehicle || $vehicle['user_id'] != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'No tens permís per eliminar aquest vehicle',
                'type' => 'danger'
            ];
        }
        
        $success = $vehicleModel->delete($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Vehicle eliminat correctament' : 'Error al eliminar el vehicle',
            'type' => $success ? 'success' : 'danger'
        ];
    }

    private function uploadVehicleImages() {
        require_once __DIR__ . '/../models/VehicleModel.php';

        $vehicleId = intval($_POST['vehicle_id'] ?? 0);
        if (!$vehicleId || empty($_FILES['images'])) {
            return [
                'success' => false,
                'message' => 'Dades no vàlides',
                'type' => 'danger'
            ];
        }

        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->getVehicleById($vehicleId);

        // Verificar que el vehículo pertenece al usuario
        if (!$vehicle || $vehicle['user_id'] != $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'No tens permís per pujar imatges a aquest vehicle',
                'type' => 'danger'
            ];
        }

        // Directorio de subida
        $uploadDir = __DIR__ . '/../public/uploads/vehicles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $success = true;
        $uploadedFiles = 0;
        $errors = [];

        // Eliminar imágenes existentes (opcional, si quieres reemplazar)
        $existingImages = $vehicleModel->getVehicleImages($vehicleId);
        foreach ($existingImages as $img) {
            $filePath = __DIR__ . '/../public/' . ltrim($img['url'], '/'); // ruta física correcta
            if (file_exists($filePath)) {
                @unlink($filePath); // eliminar archivo físico
            }
            $vehicleModel->deleteVehicleImage($img['id']); // eliminar registro DB
        }

        // Subir nuevas imágenes
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $filePath)) {
                    // Guardar ruta relativa para la vista
                    $vehicleModel->addVehicleImage($vehicleId, 'uploads/vehicles/' . $fileName);
                    $uploadedFiles++;
                } else {
                    $errors[] = $_FILES['images']['name'][$key];
                    $success = false;
                }
            }
        }

        if ($uploadedFiles > 0) {
            return [
                'success' => true,
                'message' => $uploadedFiles . ' imatges pujades correctament',
                'type' => 'success'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al pujar les imatges: ' . implode(', ', $errors),
                'type' => 'danger'
            ];
        }
    }

}
?>