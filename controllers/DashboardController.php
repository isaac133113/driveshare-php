<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/HorariModel.php';
require_once __DIR__ . '/../models/DriveCoinModel.php';
require_once __DIR__ . '/../models/VehicleModel.php';
require_once __DIR__ . '/../models/HorariRutaModel.php';
require_once __DIR__ . '/../models/ValoracionModel.php';

class DashboardController extends BaseController {
    protected $horariModel;
    protected $vehicleModel;
    protected $horariRutaModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->horariModel = new HorariModel();
        $this->vehicleModel = new VehicleModel();
        $this->horariRutaModel = new HorariRutaModel();
    }
    
    public function index() {
        $this->requireAuth();

        // -----------------------------
        // Variables que la vista espera
        // -----------------------------
        $message = $_SESSION['message'] ?? '';
        $messageType = $_SESSION['messageType'] ?? '';
        unset($_SESSION['message'], $_SESSION['messageType']);

        // -----------------------------
        // Obtener vehículos del usuario
        // -----------------------------
        $userVehicles = $this->vehicleModel->getAllVehicles([
            'user_id' => $_SESSION['user_id']
        ]);

        // -----------------------------
        // Obtener rutas del usuario
        // -----------------------------
        $userRoutes = $this->horariRutaModel->getByUserId($_SESSION['user_id']);

        // -----------------------------
        // Procesamiento de nueva ruta
        // -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_route') {
            try {
                $routeData = [
                    'user_id' => $_SESSION['user_id'],
                    'data_ruta' => $_POST['data_ruta'],
                    'hora_inici' => $_POST['hora_inici'],
                    'hora_fi' => $_POST['hora_fi'],
                    'origen' => $_POST['origenInput'],
                    'desti' => $_POST['destiInput'],
                    'vehicle_id' => $_POST['vehicle_id'],
                    'comentaris' => $_POST['comentaris'] ?? '',
                    'origen_lat' => $_POST['origen_lat'],
                    'origen_lng' => $_POST['origen_lng'],
                    'desti_lat' => $_POST['desti_lat'],
                    'desti_lng' => $_POST['desti_lng'],
                    'plazas_disponibles' => $_POST['plazas_disponibles'],
                    'precio_euros' => $_POST['precio_euros'],
                    'estado' => 1
                ];

                if ($this->horariRutaModel->create($routeData)) {
                    $message = "Ruta creada correctament!";
                    $messageType = "success";
                    // Recargar rutas
                    $userRoutes = $this->horariRutaModel->getByUserId($_SESSION['user_id']);
                } else {
                    $message = "Error al crear la ruta";
                    $messageType = "danger";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = "danger";
            }
        }

        // -----------------------------
        // Procesamiento de preferencias
        // -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'save_preferences') {
                $emailNotifications = isset($_POST['emailNotifications']) ? 1 : 0;
                $smsNotifications = isset($_POST['smsNotifications']) ? 1 : 0;
                $defaultVehicle = trim($_POST['defaultVehicle']);

                $conn = Database::getInstance()->getConnection();
                $stmt = $conn->prepare("UPDATE usuaris SET email_notifications = ?, sms_notifications = ?, default_vehicle = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("iisi", $emailNotifications, $smsNotifications, $defaultVehicle, $_SESSION['user_id']);
                    $stmt->execute();
                    $message = $stmt->affected_rows ? 'Preferències guardades correctament!' : 'No s\'ha fet cap canvi.';
                    $messageType = 'success';
                } else {
                    $message = 'Error intern en preparar la consulta de preferències.';
                    $messageType = 'danger';
                }
            }
        }

        // -----------------------------
        // Datos adicionales para la vista
        // -----------------------------
        $currentUser = $this->userModel->getById($_SESSION['user_id']);
        $userStats = $this->userModel->getUserStats($_SESSION['user_id']);
        $horarisStats = $this->horariModel->getHorarisStats($_SESSION['user_id']);
        $upcomingHoraris = $this->horariModel->getUpcomingHoraris($_SESSION['user_id'], 7);
        $favoriteVehicles = $this->horariModel->getUserMostUsedVehicles($_SESSION['user_id'], 3);
        $favoriteRoutes = $this->horariModel->getUserFavoriteRoutes($_SESSION['user_id'], 3);
        $vehicles = $this->horariModel->getVehiclesList();
        $recentActivity = $this->horariModel->getRecentActivity(5);
        $generalStats = $this->getGeneralStats();

        // Saldo DriveCoins
        $driveCoinsBalance = 0;
        try {
            $driveModel = new DriveCoinModel();
            $driveCoinsBalance = $driveModel->getBalance($_SESSION['user_id']);
        } catch (Exception $e) {
            $driveCoinsBalance = 0;
        }

        // Preferencias del usuario
        $userPreferences = [
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'default_vehicle' => '',
            'saldo' => 0.00
        ];
        try {
            $conn = Database::getInstance()->getConnection();
            $stmt = $conn->prepare("SELECT email_notifications, sms_notifications, default_vehicle, saldo FROM usuaris WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $userPreferences = $result->fetch_assoc();
                }
            }
        } catch (Throwable $e) {
            // ignorar, usar valores por defecto
        }
        
        // Valoraciones recibidas
        $valoracionModel = new ValoracionModel();
        $valoracionesRecibidas = $valoracionModel->getByConductor($_SESSION['user_id']);
        $mediaValoracion = $valoracionModel->getPromedioByConductor($_SESSION['user_id']);

        // -----------------------------
        // Cargar la vista
        // -----------------------------
        include __DIR__ . '/../views/dashboard/index.php';
    }

    
    public function profile() {
        $this->requireAuth();
        
        $message = '';
        $messageType = '';
        $user = $this->userModel->getById($_SESSION['user_id']);
        $userActivity = $this->userModel->getUserActivity($_SESSION['user_id'], 10);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update_profile':
                        $result = $this->updateProfile();
                        $message = $result['message'];
                        $messageType = $result['type'];
                        if ($result['success']) {
                            $user = $this->userModel->getById($_SESSION['user_id']); // Recargar datos
                        }
                        break;
                    
                    case 'change_password':
                        $result = $this->changePassword();
                        $message = $result['message'];
                        $messageType = $result['type'];
                        break;
                }
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/auth/profile.php';
    }
    
    public function stats() {
        $this->requireAuth();
        
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        
        // Estadísticas mensuales
        $monthlyStats = $this->horariModel->getMonthlyStats($year, $month, $_SESSION['user_id']);
        
        // Estadísticas anuales
        $yearlyStats = $this->getYearlyStats($_SESSION['user_id'], $year);
        
        // Comparación con otros usuarios
        $userRanking = $this->getUserRanking($_SESSION['user_id']);
        
        // Rutas más populares del usuario
        $popularRoutes = $this->horariModel->getUserFavoriteRoutes($_SESSION['user_id'], 10);
        
        // Vehículos más utilizados
        $vehicleUsage = $this->horariModel->getUserMostUsedVehicles($_SESSION['user_id'], 10);
        
        // Cargar la vista
        include __DIR__ . '/../views/dashboard/stats.php';
    }
    
    public function rentVehicle() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicleName = $this->sanitizeInput($_POST['vehicle']);
            $hours = (int)$_POST['hours'];
            $startTime = $this->sanitizeInput($_POST['start_time']);
            
            $vehicles = $this->horariModel->getVehiclesList();
            
            if (!isset($vehicles[$vehicleName])) {
                echo json_encode(['success' => false, 'message' => 'Vehicle no vàlid']);
                return;
            }
            
            $vehicle = $vehicles[$vehicleName];
            $totalCost = $vehicle['precio'] * $hours;
            
            // Simular alquiler (en una aplicación real, aquí se procesaría el pago)
            $rentalCode = 'DRS' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Registrar el alquiler en logs (opcional)
            $this->userModel->logUserActivity(
                $_SESSION['user_id'], 
                "rental_$vehicleName", 
                $_SERVER['REMOTE_ADDR']
            );
            
            echo json_encode([
                'success' => true,
                'rental_code' => $rentalCode,
                'vehicle' => $vehicleName,
                'hours' => $hours,
                'cost' => $totalCost,
                'start_time' => $startTime,
                'end_time' => date('Y-m-d H:i:s', strtotime($startTime . " +$hours hours"))
            ]);
            return;
        }
        
        // Si no es POST, redirigir al dashboard
        $this->redirect('dashboard.php');
    }
    
    public function search() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'all';
        
        $results = [];
        
        switch ($type) {
            case 'users':
                $results = $this->searchUsers($query);
                break;
            case 'routes':
                $results = $this->searchRoutes($query);
                break;
            case 'vehicles':
                $results = $this->searchVehicles($query);
                break;
            default:
                $results = $this->searchAll($query);
        }
        
        echo json_encode($results);
        exit;
    }

    public function addSaldo() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = floatval($_POST['amount'] ?? 0);
            $userId = $_SESSION['user_id'];

            $userModel = new UserModel();

            if ($amount <= 0) {
                $_SESSION['message'] = "Quantitat no vàlida";
                $_SESSION['messageType'] = "danger";
            } else {
                $currentSaldo = $userModel->getById($userId)['saldo'];
                $newSaldo = $currentSaldo + $amount;

                if ($userModel->updateSaldo($userId, $newSaldo)) {
                    $userModel->logUserActivity($userId, 'Afegit saldo', $amount);
                    $_SESSION['message'] = "Saldo afegit correctament! Nou saldo: " . number_format($newSaldo, 2, ',', '.') . " €";
                    $_SESSION['messageType'] = "success";
                } else {
                    $_SESSION['message'] = "Error actualitzant el saldo";
                    $_SESSION['messageType'] = "danger";
                }
            }

            header('Location: ../../public/index.php?controller=dashboard&action=index');
            exit;
        }
    }
    
    private function updateProfile() {
        $data = [
            'nom' => $this->sanitizeInput($_POST['nom']),
            'cognoms' => $this->sanitizeInput($_POST['cognoms']),
            'email' => $this->sanitizeInput($_POST['email'])
        ];
        
        // Validaciones
        $errors = [];
        
        if (empty($data['nom'])) {
            $errors[] = 'El nom és obligatori.';
        }
        
        if (empty($data['cognoms'])) {
            $errors[] = 'Els cognoms són obligatoris.';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'L\'email és obligatori.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email no és vàlid.';
        } elseif ($data['email'] !== $_SESSION['user_email'] && $this->userModel->emailExists($data['email'])) {
            $errors[] = 'Aquest email ja està en ús.';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('<br>', $errors),
                'type' => 'danger'
            ];
        }
        
        if ($this->userModel->updateProfile($_SESSION['user_id'], $data)) {
            // Actualizar sesión
            $_SESSION['user_nom'] = $data['nom'];
            $_SESSION['user_cognoms'] = $data['cognoms'];
            $_SESSION['user_email'] = $data['email'];
            
            return [
                'success' => true,
                'message' => 'Perfil actualitzat correctament.',
                'type' => 'success'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualitzar el perfil.',
                'type' => 'danger'
            ];
        }
    }
    
    private function changePassword() {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return [
                'success' => false,
                'message' => 'Si us plau, omple tots els camps.',
                'type' => 'danger'
            ];
        }
        
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Les contrasenyes noves no coincideixen.',
                'type' => 'danger'
            ];
        }
        
        if (!$this->userModel->validatePassword($newPassword)) {
            return [
                'success' => false,
                'message' => 'La contrasenya nova ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.',
                'type' => 'danger'
            ];
        }
        
        if (!$this->userModel->verifyCurrentPassword($_SESSION['user_id'], $currentPassword)) {
            return [
                'success' => false,
                'message' => 'La contrasenya actual és incorrecta.',
                'type' => 'danger'
            ];
        }
        
        if ($this->userModel->updatePassword($_SESSION['user_id'], $newPassword)) {
            return [
                'success' => true,
                'message' => 'Contrasenya canviada correctament.',
                'type' => 'success'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al canviar la contrasenya.',
                'type' => 'danger'
            ];
        }
    }
    
    private function getGeneralStats() {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_horaris' => $this->horariModel->getHorarisStats()['total'],
            'popular_routes' => $this->horariModel->getPopularRoutes(3),
            'popular_vehicles' => $this->horariModel->getPopularVehicles(3)
        ];
    }
    
    private function getTotalUsers() {
        $userModel = new UserModel();
        $conn = Database::getInstance()->getConnection();
        $result = $conn->query("SELECT COUNT(*) as total FROM usuaris");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
    
    private function getYearlyStats($userId, $year) {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthStats = $this->horariModel->getMonthlyStats($year, $i, $userId);
            $months[$i] = array_sum(array_column($monthStats, 'total_horaris'));
        }
        return $months;
    }
    
    private function getUserRanking($userId) {
        // Obtener ranking del usuario basado en número de horarios
        $conn = Database::getInstance()->getConnection();
        $stmt = $conn->prepare("
            SELECT 
                u.id,
                u.nom,
                u.cognoms,
                COUNT(hr.id) as total_horaris,
                RANK() OVER (ORDER BY COUNT(hr.id) DESC) as ranking
            FROM usuaris u
            LEFT JOIN horaris_rutes hr ON u.id = hr.user_id
            WHERE hr.data_ruta >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
            GROUP BY u.id
            ORDER BY total_horaris DESC
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    private function searchUsers($query) {
        $conn = Database::getInstance()->getConnection();
        $stmt = $conn->prepare(
            "SELECT id, nom, cognoms, correu FROM usuaris 
             WHERE nom LIKE ? OR cognoms LIKE ? OR correu LIKE ?
             LIMIT 10"
        );
        $searchTerm = "%$query%";
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    private function searchRoutes($query) {
        return $this->horariModel->getHorarisByLocation($query);
    }
    
    private function searchVehicles($query) {
        $vehicles = $this->horariModel->getVehiclesList();
        $filtered = [];
        
        foreach ($vehicles as $name => $details) {
            if (stripos($name, $query) !== false) {
                $filtered[$name] = $details;
            }
        }
        
        return $filtered;
    }
    
    private function searchAll($query) {
        return [
            'users' => $this->searchUsers($query),
            'routes' => array_slice($this->searchRoutes($query), 0, 5),
            'vehicles' => $this->searchVehicles($query)
        ];
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('login.php');
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'DashboardController.php') {
    $controller = new DashboardController();
    
    $action = $_GET['action'] ?? 'index';
    
    switch ($action) {
        case 'profile':
            $controller->profile();
            break;
        case 'stats':
            $controller->stats();
            break;
        case 'rent-vehicle':
            $controller->rentVehicle();
            break;
        case 'search':
            $controller->search();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            $controller->index();
    }
}
?>