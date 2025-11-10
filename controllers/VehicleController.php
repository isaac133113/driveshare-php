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
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;
                case 'delete':
                    $result = $this->deleteVehicle();
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;
                case 'upload_image':
                    $result = $this->uploadVehicleImages();
                    $message = $result['message'];
                    $messageType = $result['type'];
                    break;
            }
        }
        $message = '';
        $messageType = '';
        
        // Manejar reserva
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reservar') {
            $result = $this->reservarVehicle();
            $message = $result['message'];
            $messageType = $result['type'];
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/vehicles/index.php';
    }
        
    private function getVehiclesList($filtros = []) {
        require_once __DIR__ . '/../models/VehicleModel.php';
        $vehicleModel = new VehicleModel();
        return $vehicleModel->getAllVehicles($filtros);
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
    
    private function reservarVehicle() {
        $vehicleId = intval($_POST['vehicle_id']);
        $fechaInicio = $_POST['fecha_inicio'];
        $fechaFin = $_POST['fecha_fin'];
        $tipoRenta = $_POST['tipo_renta']; // 'horas' o 'dias'
        $cantidad = intval($_POST['cantidad']);
        
        // Validaciones básicas
        if (empty($vehicleId) || empty($fechaInicio) || empty($fechaFin) || empty($cantidad)) {
            return [
                'success' => false,
                'message' => 'Tots els camps són obligatoris.',
                'type' => 'danger'
            ];
        }
        
        // Verificar que las fechas sean válidas
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $ahora = new DateTime();
        
        if ($inicio < $ahora) {
            return [
                'success' => false,
                'message' => 'La data d\'inici no pot ser anterior a avui.',
                'type' => 'danger'
            ];
        }
        
        if ($fin <= $inicio) {
            return [
                'success' => false,
                'message' => 'La data de fi ha de ser posterior a la data d\'inici.',
                'type' => 'danger'
            ];
        }
        
        // Obtener datos del vehículo
        $vehicles = $this->getVehiclesList();
        $vehicle = null;
        foreach ($vehicles as $v) {
            if ($v['id'] == $vehicleId) {
                $vehicle = $v;
                break;
            }
        }
        
        if (!$vehicle) {
            return [
                'success' => false,
                'message' => 'Vehicle no trobat.',
                'type' => 'danger'
            ];
        }
        
        if (!$vehicle['disponible']) {
            return [
                'success' => false,
                'message' => 'Aquest vehicle no està disponible.',
                'type' => 'danger'
            ];
        }
        
        // Calcular precio en euros y convertir a DriveCoins
        $precioEuros = $tipoRenta === 'horas' ? $vehicle['precio_hora'] : $vehicle['precio_dia'];
        $totalEuros = $precioEuros * $cantidad;
        
        // Convertir a DriveCoins (1 Euro = 10 DriveCoins)
        require_once __DIR__ . '/../models/DriveCoinModel.php';
        $driveCoinModel = new DriveCoinModel();
        $totalDriveCoins = DriveCoinModel::eurosToDriveCoins($totalEuros);
        
        // Verificar saldo de DriveCoins del usuario
        $currentBalance = $driveCoinModel->getBalance($_SESSION['user_id']);
        
        if ($currentBalance < $totalDriveCoins) {
            return [
                'success' => false,
                'message' => "Saldo insuficient de DriveCoins. Necessites: " . number_format($totalDriveCoins, 0, ',', '.') . " DC<br>
                            Saldo actual: " . number_format($currentBalance, 0, ',', '.') . " DC<br>
                            <a href='../comprar-drivecoins.php' class='btn btn-primary btn-sm mt-2'>Comprar DriveCoins</a>",
                'type' => 'warning'
            ];
        }
        
        // Generar código de reserva
        $codigoReserva = 'DRS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Procesar pago con DriveCoins
        $descripcionTransaccion = "Reserva vehicle {$vehicle['nombre']} - {$codigoReserva}";
        $resultPago = $driveCoinModel->spendDriveCoins($_SESSION['user_id'], $totalDriveCoins, $descripcionTransaccion, $codigoReserva);
        
        if (!$resultPago['success']) {
            return [
                'success' => false,
                'message' => 'Error al processar el pagament: ' . $resultPago['message'],
                'type' => 'danger'
            ];
        }
        
        // En una aplicación real, aquí guardarías la reserva en la base de datos
        // Por ahora, simularemos una reserva exitosa
        
        // Log de la actividad
        $this->userModel->logUserActivity(
            $_SESSION['user_id'], 
            "reserva_vehicle_" . $vehicle['nombre'], 
            $_SERVER['REMOTE_ADDR']
        );
        
        return [
            'success' => true,
            'message' => "Reserva realitzada correctament! Codi de reserva: <strong>$codigoReserva</strong><br>
                         Vehicle: {$vehicle['nombre']}<br>
                         Període: $fechaInicio - $fechaFin<br>
                         Cost: <i class='bi bi-coin'></i> " . number_format($totalDriveCoins, 0, ',', '.') . " DC<br>
                         Nou saldo: <i class='bi bi-coin'></i> " . number_format($resultPago['new_balance'], 0, ',', '.') . " DC",
            'type' => 'success',
            'codigo_reserva' => $codigoReserva,
            'new_balance' => $resultPago['new_balance']
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

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'VehicleController.php') {
    $controller = new VehicleController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'details':
            $controller->details();
            break;
        default:
            $controller->index();
    }
}
?>