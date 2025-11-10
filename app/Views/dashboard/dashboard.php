<?php
session_start();

// Nota: mover includes y lógica a los controladores.
// Esta vista debe recibir variables preparadas por el controlador (ej. $_SESSION y $driveCoinsBalance, $userPreferences, $message, $messageType).
// Proveer valores por defecto seguros para evitar warnings si se accede directamente.
if (!isset($_SESSION['user_id'])) {
    // Si se abre directamente, redirigir al login central.
    header('Location: login.php');
    exit;
}

$message = $message ?? '';
$messageType = $messageType ?? '';
$driveCoinsBalance = $driveCoinsBalance ?? 0;
$userPreferences = $userPreferences ?? ['email_notifications' => 1, 'sms_notifications' => 0, 'default_vehicle' => '', 'saldo' => 0.00];

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Variables para mensajes
$message = '';
$messageType = '';

// Procesamiento de formularios de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new UserModel();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $nom = trim($_POST['nom']);
                $cognoms = trim($_POST['cognoms']);
                $email = trim($_POST['email']);
                
                if (!empty($nom) && !empty($cognoms) && !empty($email)) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Verificar si el email ya existe (si es diferente al actual)
                        if ($email !== $_SESSION['user_email'] && $userModel->emailExists($email)) {
                            $message = 'Aquest email ja està registrat per un altre usuari.';
                            $messageType = 'danger';
                        } else {
                            // Actualizar perfil en la base de datos
                            $updateData = [
                                'nom' => $nom,
                                'cognoms' => $cognoms,
                                'correu' => $email
                            ];
                            
                            $updateResult = $userModel->updateProfile($_SESSION['user_id'], $updateData);
                            
                            if ($updateResult) {
                                // Actualizar variables de sesión
                                $_SESSION['user_nom'] = $nom;
                                $_SESSION['user_cognoms'] = $cognoms;
                                $_SESSION['user_email'] = $email;
                                
                                $message = 'Perfil actualitzat correctament!';
                                $messageType = 'success';
                            } else {
                                $message = 'Error al actualitzar el perfil a la base de dades.';
                                $messageType = 'danger';
                            }
                        }
                    } else {
                        $message = 'Email no vàlid.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Tots els camps són obligatoris.';
                    $messageType = 'danger';
                }
                break;
                
            case 'change_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
                    if ($newPassword === $confirmPassword) {
                        if (strlen($newPassword) >= 6) {
                            // Verificar contraseña actual en la base de datos
                            $result = $userModel->verifyCurrentPassword($_SESSION['user_id'], $currentPassword);
                            
                            if ($result) {
                                // Actualizar la contraseña en la base de datos
                                $updateResult = $userModel->updatePassword($_SESSION['user_id'], $newPassword);
                                
                                if ($updateResult) {
                                    $message = 'Contrasenya canviada correctament!';
                                    $messageType = 'success';
                                } else {
                                    $message = 'Error al actualitzar la contrasenya a la base de dades.';
                                    $messageType = 'danger';
                                }
                            } else {
                                $message = 'La contrasenya actual és incorrecta.';
                                $messageType = 'danger';
                            }
                        } else {
                            $message = 'La nova contrasenya ha de tenir mínim 6 caràcters.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Les contrasenyes no coincideixen.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Tots els camps són obligatoris.';
                    $messageType = 'danger';
                }
                break;
                
            case 'save_preferences':
                $emailNotifications = isset($_POST['emailNotifications']) ? 1 : 0;
                $smsNotifications = isset($_POST['smsNotifications']) ? 1 : 0;
                $defaultVehicle = trim($_POST['defaultVehicle']);
                
                // Actualizar preferencias en la tabla usuaris
                $conn = Database::getInstance()->getConnection();
                
                // Verificar si las columnas existen, si no, añadirlas
                $columnsToAdd = [
                    'email_notifications' => "ALTER TABLE usuaris ADD COLUMN email_notifications TINYINT(1) DEFAULT 1",
                    'sms_notifications' => "ALTER TABLE usuaris ADD COLUMN sms_notifications TINYINT(1) DEFAULT 0",
                    'default_vehicle' => "ALTER TABLE usuaris ADD COLUMN default_vehicle VARCHAR(50) DEFAULT ''",
                    'saldo' => "ALTER TABLE usuaris ADD COLUMN saldo DECIMAL(10,2) DEFAULT 0.00"
                ];
                
                foreach ($columnsToAdd as $columnName => $alterQuery) {
                    // Verificar si la columna existe
                    $checkColumn = $conn->query("SHOW COLUMNS FROM usuaris LIKE '$columnName'");
                    if ($checkColumn->num_rows == 0) {
                        // La columna no existe, añadirla
                        $conn->query($alterQuery);
                    }
                }
                
                // Actualizar preferencias del usuario
                $stmt = $conn->prepare("UPDATE usuaris SET email_notifications = ?, sms_notifications = ?, default_vehicle = ? WHERE id = ?");
                $stmt->bind_param("iisi", $emailNotifications, $smsNotifications, $defaultVehicle, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $message = 'Preferències guardades correctament!';
                    $messageType = 'success';
                } else {
                    $message = 'Error al guardar les preferències.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Cargar preferencias del usuario y DriveCoins
$userPreferences = [
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'default_vehicle' => '',
    'saldo' => 0.00
];

// Obtener saldo de DriveCoins
$driveCoinModel = new DriveCoinModel();
$driveCoinsBalance = $driveCoinModel->getBalance($_SESSION['user_id']);

$conn = Database::getInstance()->getConnection();

// Verificar si las columnas de preferencias existen, si no, añadirlas
$columnsToAdd = [
    'email_notifications' => "ALTER TABLE usuaris ADD COLUMN email_notifications TINYINT(1) DEFAULT 1",
    'sms_notifications' => "ALTER TABLE usuaris ADD COLUMN sms_notifications TINYINT(1) DEFAULT 0",
    'default_vehicle' => "ALTER TABLE usuaris ADD COLUMN default_vehicle VARCHAR(50) DEFAULT ''",
    'saldo' => "ALTER TABLE usuaris ADD COLUMN saldo DECIMAL(10,2) DEFAULT 0.00"
];

foreach ($columnsToAdd as $columnName => $alterQuery) {
    // Verificar si la columna existe
    $checkColumn = $conn->query("SHOW COLUMNS FROM usuaris LIKE '$columnName'");
    if ($checkColumn->num_rows == 0) {
        // La columna no existe, añadirla
        $conn->query($alterQuery);
    }
}

$stmt = $conn->prepare("SELECT email_notifications, sms_notifications, default_vehicle, saldo FROM usuaris WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userPreferences = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Renting i Carsharing</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Header Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-car-front-fill text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">DriveShare</h2>
                            <p class="text-muted">Renting i Carsharing de vehicles</p>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold text-dark mb-1">
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    Benvingut, <?php echo htmlspecialchars($_SESSION['user_nom']); ?>!
                                </h3>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-envelope me-2"></i>
                                    <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary rounded-3" data-bs-toggle="modal" data-bs-target="#configModal">
                                    <i class="bi bi-gear me-2"></i>Configuració
                                </button>
                                <a href="?logout=1" class="btn btn-outline-danger rounded-3">
                                    <i class="bi bi-box-arrow-right me-2"></i>Tancar Sessió
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- User Info Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-header bg-primary text-white rounded-top-4">
                        <h5 class="mb-0">
                            <i class="bi bi-person-vcard me-2"></i>El teu Perfil de Conductor
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-3 p-3 bg-light">
                                    <small class="text-muted"><i class="bi bi-person me-1"></i>Nom Complet</small>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars($_SESSION['user_nom']) . ' ' . htmlspecialchars($_SESSION['user_cognoms']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-3 p-3 bg-light">
                                    <small class="text-muted"><i class="bi bi-envelope me-1"></i>Email</small>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-3 p-3 bg-success bg-opacity-25">
                                    <small class="text-muted"><i class="bi bi-coin me-1"></i>DriveCoins Disponibles</small>
                                    <div class="fw-bold text-success">
                                        <span id="userDriveCoins"><i class="bi bi-coin"></i> <?php echo number_format($driveCoinsBalance, 0, ',', '.'); ?> DC</span>
                                    </div>
                                    <small class="text-muted">Moneda virtual DriveShare</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-3 p-3 bg-info bg-opacity-25">
                                    <small class="text-muted"><i class="bi bi-speedometer2 me-1"></i>Quilòmetres Recorreguts</small>
                                    <div class="fw-bold text-info">
                                        1,847 km
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Fleet -->
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-dark text-white rounded-top-4">
                        <h5 class="mb-0">
                            <i class="bi bi-car-front me-2"></i>Menú
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-grid">
                                    <a href="horaris.php" class="btn btn-outline-primary btn-lg rounded-3">
                                        <i class="bi bi-calendar-week me-2"></i>Gestionar Horaris
                                        <small class="d-block text-muted">Organitza les teves rutes</small>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid">
                                    <a href="../../comprar-drivecoins.php" class="btn btn-outline-success btn-lg rounded-3">
                                        <i class="bi bi-coin me-2"></i>Comprar DriveCoins
                                        <small class="d-block text-muted">Moneda virtual DriveShare</small>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid">
                                    <a href="../../ver-coches.php" class="btn btn-outline-info btn-lg rounded-3">
                                        <i class="bi bi-car-front me-2"></i>Ver Coches
                                        <small class="d-block text-muted">Explorar y reservar</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segunda fila de botones -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a href="../../buscar-coche.php" class="btn btn-outline-warning btn-lg rounded-3">
                                        <i class="bi bi-geo-alt me-2"></i>Buscar Coche Cercano
                                        <small class="d-block text-muted">Mapa interactivo con ubicaciones</small>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-secondary btn-lg rounded-3" onclick="viewReservations()">
                                        <i class="bi bi-clipboard-check me-2"></i>Mis Reservas
                                        <small class="d-block text-muted">Historial y reservas activas</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DriveShare Interactive Functions -->
    <script>
        // DriveCoins Interactive Functions
        function buyDriveCoins() {
            window.location.href = '../../comprar-drivecoins.php';
        }
        
        // Update DriveCoins balance
        function updateDriveCoinsBalance() {
            fetch('../../controllers/DriveCoinController.php?action=get_balance')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('userDriveCoins').innerHTML = 
                            `<i class="bi bi-coin"></i> ${data.formatted_balance}`;
                        
                        // Update global variable for other functions
                        window.userDriveCoins = data.balance;
                    }
                })
                .catch(error => console.error('Error updating DriveCoins balance:', error));
        }
        
        // Rent Vehicle Function
        function rentVehicle(vehicleName, pricePerHour) {
            window.userDriveCoins = <?php echo $driveCoinsBalance; ?>; // Saldo real de DriveCoins (variable global)
            const currentHour = new Date().getHours();
            
            if (currentHour >= 22 || currentHour < 6) {
                showInfoModal('Servei No Disponible', '❌ El servei de lloguer no està disponible entre les 22:00 i les 06:00');
                return;
            }
            
            // Convertir precio por hora a DriveCoins (1€ = 10 DC)
            const priceInDriveCoins = pricePerHour * 10;
            
            // Mostrar modal de alquiler
            showRentModal(vehicleName, priceInDriveCoins, window.userDriveCoins);
        }
        
        // Show Rental Success
        function showRentalSuccess(vehicle, hours, cost) {
            const rentalCode = 'DRS' + Math.floor(Math.random() * 10000);
            const startTime = new Date();
            const endTime = new Date(startTime.getTime() + (hours * 60 * 60 * 1000));
            
            const content = `
                <div class="text-center">
                    <i class="bi bi-check-circle text-success display-4"></i>
                    <h4 class="mt-3">Lloguer Confirmat!</h4>
                    <div class="card mt-3">
                        <div class="card-body">
                            <p><strong>Vehicle:</strong> ${vehicle}</p>
                            <p><strong>Codi de reserva:</strong> ${rentalCode}</p>
                            <p><strong>Inici:</strong> ${startTime.toLocaleString()}</p>
                            <p><strong>Fi:</strong> ${endTime.toLocaleString()}</p>
                            <p><strong>Cost:</strong> <i class="bi bi-coin"></i> ${cost.toFixed(0)} DC</p>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-phone"></i> Rebràs un SMS amb les instruccions per desbloquejar el vehicle.
                    </div>
                </div>
            `;
            showInfoModal('🎉 Lloguer Confirmat', content);
        }
        
        // Show My Rentals
        function showMyRentals() {
            const modal = new bootstrap.Modal(document.getElementById('rentalHistoryModal'));
            modal.show();
        }
        
        // Find Nearest Car
        function findNearestCar() {
            // Mostrar modal de carga primero
            showInfoModal('🔍 Cercant vehicles...', 'Cercant vehicles propers a la teva ubicació...');
            
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('infoModal')).hide();
                setTimeout(() => {
                    const modal = new bootstrap.Modal(document.getElementById('nearbyVehiclesModal'));
                    modal.show();
                }, 300);
            }, 1000);
        }
    </script>

    <!-- Modal de Configuración -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalLabel">
                        <i class="bi bi-gear me-2"></i>Configuració del Compte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs de Configuración -->
                    <ul class="nav nav-tabs" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="bi bi-person me-1"></i>Perfil
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                <i class="bi bi-key me-1"></i>Contrasenya
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                <i class="bi bi-sliders me-1"></i>Preferències
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="configTabsContent">
                        <!-- Tab Perfil -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form id="profileForm" method="post" action="">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cognoms" class="form-label">Cognoms</label>
                                        <input type="text" class="form-control" id="cognoms" name="cognoms" value="<?php echo htmlspecialchars($_SESSION['user_cognoms']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Actualitzar Perfil
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Contrasenya -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form id="passwordForm" method="post" action="">
                                <input type="hidden" name="action" value="change_password">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Per canviar la contrasenya, introdueix la contrasenya actual i la nova contrasenya.
                                </div>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="bi bi-shield-lock me-1"></i>Contrasenya Actual
                                    </label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">
                                        <i class="bi bi-key me-1"></i>Nova Contrasenya
                                    </label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                    <small class="text-muted">Mínim 6 caràcters</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-check-circle me-1"></i>Confirmar Nova Contrasenya
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key me-1"></i>Canviar Contrasenya
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Preferències -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel">
                            <form id="preferencesForm" method="post" action="">
                                <input type="hidden" name="action" value="save_preferences">
                                <div class="mb-3">
                                    <label class="form-label">Notificacions</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications" name="emailNotifications" <?php echo $userPreferences['email_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="emailNotifications">
                                            Rebre notificacions per email
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="smsNotifications" name="smsNotifications" <?php echo $userPreferences['sms_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="smsNotifications">
                                            Rebre notificacions per SMS
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="defaultVehicle" class="form-label">Vehicle Preferit</label>
                                    <select class="form-control" id="defaultVehicle" name="defaultVehicle">
                                        <option value="">Selecciona un vehicle</option>
                                        <option value="Seat Ibiza" <?php echo $userPreferences['default_vehicle'] === 'Seat Ibiza' ? 'selected' : ''; ?>>Seat Ibiza</option>
                                        <option value="Ford Focus" <?php echo $userPreferences['default_vehicle'] === 'Ford Focus' ? 'selected' : ''; ?>>Ford Focus</option>
                                        <option value="Tesla Model 3" <?php echo $userPreferences['default_vehicle'] === 'Tesla Model 3' ? 'selected' : ''; ?>>Tesla Model 3</option>
                                        <option value="BMW X5" <?php echo $userPreferences['default_vehicle'] === 'BMW X5' ? 'selected' : ''; ?>>BMW X5</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-gear me-1"></i>Guardar Preferències
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para reemplazar alerts -->
    
    <!-- Modal de Información General -->
    <div class="modal fade" id="infoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalTitle">Informació</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="infoModalBody">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">D'acord</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Lloguer de Vehicle -->
    <div class="modal fade" id="rentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-car-front me-2"></i>Llogar Vehicle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rentModalContent">
                        <!-- Contenido dinámico del alquiler -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Historial de Lloguers -->
    <div class="modal fade" id="rentalHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-check me-2"></i>Historial de Lloguers
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Ford Focus</h6>
                                    <p class="mb-1 text-muted">25/09/2024 - 3 hores</p>
                                </div>
                                <span class="badge bg-success">€54.00</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Tesla Model 3</h6>
                                    <p class="mb-1 text-muted">20/09/2024 - 2 hores</p>
                                </div>
                                <span class="badge bg-success">€70.00</span>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Seat Ibiza</h6>
                                    <p class="mb-1 text-muted">15/09/2024 - 4 hores</p>
                                </div>
                                <span class="badge bg-success">€48.00</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <strong>Total gastat aquest mes: €172.00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Vehicles Propers -->
    <div class="modal fade" id="nearbyVehiclesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-geo-alt me-2"></i>Vehicles Propers
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Seat Ibiza</h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Carrer Major, 45
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success">150m</span><br>
                                    <button class="btn btn-sm btn-primary mt-1" onclick="reserveNearbyVehicle('Seat Ibiza')">Reservar</button>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Ford Focus</h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Av. Diagonal, 123
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning">280m</span><br>
                                    <button class="btn btn-sm btn-primary mt-1" onclick="reserveNearbyVehicle('Ford Focus')">Reservar</button>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Tesla Model 3</h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Plaça Catalunya, 7
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary">450m</span><br>
                                    <button class="btn btn-sm btn-primary mt-1" onclick="reserveNearbyVehicle('Tesla Model 3')">Reservar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestión de formularios en el modal de configuración
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            // Permitir que el formulario se envíe normalmente (no preventDefault)
            return true;
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showInfoModal('Error', '❌ Les contrasenyes no coincideixen');
                return false;
            }
            
            // Permitir que el formulario se envíe normalmente
            return true;
        });

        document.getElementById('preferencesForm').addEventListener('submit', function(e) {
            // Permitir que el formulario se envíe normalmente
            return true;
        });

        // Validación en tiempo real de contraseñas
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Les contrasenyes no coincideixen');
            } else {
                this.setCustomValidity('');
            }
        });

        // Funciones auxiliares para modales
        function showInfoModal(title, content) {
            document.getElementById('infoModalTitle').textContent = title;
            document.getElementById('infoModalBody').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('infoModal'));
            modal.show();
        }

        function showRentModal(vehicleName, pricePerHourDC, userDriveCoins) {
            const content = `
                <div class="card">
                    <div class="card-body">
                        <h5><i class="bi bi-car-front me-2"></i>${vehicleName}</h5>
                        <p class="text-muted">Preu: <i class="bi bi-coin"></i> ${pricePerHourDC} DC/hora</p>
                        <p class="text-success">DriveCoins disponibles: <i class="bi bi-coin"></i> ${userDriveCoins.toFixed(0)} DC</p>
                        
                        <div class="mb-3">
                            <label for="rentHours" class="form-label">Quantes hores vols llogar?</label>
                            <input type="number" class="form-control" id="rentHours" min="1" max="24" value="1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cost calculat:</label>
                            <div class="h5 text-primary" id="calculatedCost"><i class="bi bi-coin"></i> ${pricePerHourDC} DC</div>
                        </div>
                        
                        <button class="btn btn-primary w-100" onclick="confirmRental('${vehicleName}', ${pricePerHourDC}, ${userDriveCoins})">
                            <i class="bi bi-check-circle me-1"></i>Confirmar Lloguer
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('rentModalContent').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('rentModal'));
            modal.show();
            
            // Actualizar costo en tiempo real
            document.getElementById('rentHours').addEventListener('input', function() {
                const hours = this.value;
                const cost = (parseFloat(pricePerHourDC) * parseFloat(hours)).toFixed(0);
                document.getElementById('calculatedCost').innerHTML = '<i class="bi bi-coin"></i> ' + cost + ' DC';
            });
        }

        function confirmRental(vehicleName, pricePerHourDC, userDriveCoins) {
            const hours = document.getElementById('rentHours').value;
            const totalCost = parseFloat(pricePerHourDC) * parseFloat(hours);
            
            if (totalCost > userDriveCoins) {
                showInfoModal('DriveCoins Insuficients', `
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle text-warning display-4"></i>
                        <h5 class="mt-3">No tens suficients DriveCoins!</h5>
                        <div class="card mt-3">
                            <div class="card-body">
                                <p><strong>Cost:</strong> <i class="bi bi-coin"></i> ${totalCost.toFixed(0)} DC</p>
                                <p><strong>DriveCoins disponibles:</strong> <i class="bi bi-coin"></i> ${userDriveCoins.toFixed(0)} DC</p>
                                <p class="text-danger"><strong>Necessites:</strong> <i class="bi bi-coin"></i> ${(totalCost - userDriveCoins).toFixed(0)} DC més</p>
                            </div>
                        </div>
                        <a href="../../comprar-drivecoins.php" class="btn btn-primary mt-3">Comprar DriveCoins</a>
                    </div>
                `);
            } else {
                bootstrap.Modal.getInstance(document.getElementById('rentModal')).hide();
                setTimeout(() => showRentalSuccess(vehicleName, hours, totalCost), 300);
                
                // Actualizar saldo (simulado)
                window.userDriveCoins -= totalCost;
                updateDriveCoinsBalance();
            }
        }

        function reserveNearbyVehicle(vehicleName) {
            bootstrap.Modal.getInstance(document.getElementById('nearbyVehiclesModal')).hide();
            setTimeout(() => {
                showInfoModal('Navegació', `
                    <div class="text-center">
                        <i class="bi bi-map text-primary display-4"></i>
                        <h5 class="mt-3">Obrint Google Maps</h5>
                        <p class="mt-3">Navegant fins al <strong>${vehicleName}</strong></p>
                        <div class="alert alert-info">
                            <i class="bi bi-phone me-2"></i>Funcionalitat completa disponible a l'app mòbil
                        </div>
                    </div>
                `);
            }, 300);
        }
    </script>
</body>
</html>