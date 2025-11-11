<?php
// No iniciar sesión si ya está activa (la inicia el controlador/config)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$userVehicles = $userVehicles ?? [];
// La vista recibe las variables preparadas por el controlador:
// $currentUser, $userStats, $horarisStats, $upcomingHoraris, $favoriteVehicles,
// $favoriteRoutes, $vehicles, $recentActivity, $generalStats,
// $message, $messageType, $driveCoinsBalance, $userPreferences

// Valores por defecto para evitar warnings si el controlador no los hubiera inicializado
$message = $message ?? '';
$messageType = $messageType ?? '';
$driveCoinsBalance = $driveCoinsBalance ?? 0;
$userPreferences = $userPreferences ?? [
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'default_vehicle' => '',
    'saldo' => 0.00
];
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                                <a href="/public/index.php?controller=auth&action=logout" class="btn btn-outline-danger rounded-3">
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
                                    <small class="text-muted"><i class="bi bi-wallet2 me-1"></i>Saldo Disponible</small>
                                    <div class="fw-bold text-success">
                                        <span id="userSaldo"><?php echo number_format($userPreferences['saldo'], 2, ',', '.'); ?> €</span>
                                    </div>
                                    <small class="text-muted">Saldo de la teva compte</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-3 p-3 bg-warning bg-opacity-25">
                                    <small class="text-muted"><i class="bi bi-coin me-1"></i>DriveCoins</small>
                                    <div class="fw-bold text-warning">
                                        <span id="driveCoinsBalance"><?php echo number_format($driveCoinsBalance, 2, ',', '.'); ?></span> DC
                                    </div>
                                    <small class="text-muted">Monedes del sistema</small>
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
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a href="/public/index.php?controller=horaris&action=index" class="btn btn-outline-secondary btn-lg rounded-3">
                                        <i class="bi bi-calendar-week me-2"></i>Gestionar Rutes
                                        <small class="d-block text-muted">Organitza les teves rutes</small>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a href="/public/index.php?controller=vehicle&action=index" class="btn btn-outline-primary btn-lg rounded-3">
                                        <i class="bi bi-car-front me-2"></i>Els Meus Vehicles
                                        <small class="d-block text-muted">Gestiona els teus vehicles</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segunda fila de botones -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-success btn-lg rounded-3" data-bs-toggle="modal" data-bs-target="#newRouteModal">
                                        <i class="bi bi-map me-2"></i>Nova Ruta
                                        <small class="d-block text-muted">Programa un nou viatge</small>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-info btn-lg rounded-3" data-bs-toggle="modal" data-bs-target="#routesListModal">
                                        <i class="bi bi-list-check me-2"></i>Les Meves Rutes
                                        <small class="d-block text-muted">Historial i rutes actives</small>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tercera fila de botones -->
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
                                    <button class="btn btn-outline-dark btn-lg rounded-3" onclick="viewReservations()">
                                        <i class="bi bi-clipboard-check me-2"></i>Mis Reservas
                                        <small class="d-block text-muted">Historial y reservas activas</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Nova Ruta -->
                <div class="modal fade" id="newRouteModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-map me-2"></i>Nova Ruta
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="newRouteForm">
                                    <input type="hidden" name="action" value="create_route">
                                    <input type="hidden" id="origen_lat" name="origen_lat">
                                    <input type="hidden" id="origen_lng" name="origen_lng">
                                    <input type="hidden" id="desti_lat" name="desti_lat">
                                    <input type="hidden" id="desti_lng" name="desti_lng">

                                    <div class="row g-3">
                                        <!-- Data i Hora -->
                                        <div class="col-md-4">
                                            <label class="form-label">Data</label>
                                            <input type="date" class="form-control" name="data_ruta" required
                                                min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Hora Inici</label>
                                            <input type="time" class="form-control" name="hora_inici" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Hora Fi</label>
                                            <input type="time" class="form-control" name="hora_fi" required>
                                        </div>

                                        <!-- Vehicle i Places -->
                                        <div class="col-md-6">
                                            <label class="form-label">Vehicle</label>

                                            <select class="form-select" name="vehicle_id" required>
                                                <option value="">Selecciona un vehicle...</option>
                                                <?php foreach ($userVehicles as $vehicle): ?>
                                                    <option value="<?php echo $vehicle['id']; ?>">
                                                        <?php echo htmlspecialchars($vehicle['marca_model']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Places Disponibles</label>
                                            <input type="number" class="form-control" name="plazas_disponibles" 
                                                min="1" max="8" value="4" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Preu (€)</label>
                                            <input type="number" class="form-control" name="precio_euros" 
                                                min="0" step="0.01" required>
                                        </div>

                                        <!-- Mapa i Ubicacions -->
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Origen</label>
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control" id="origenInput" 
                                                            placeholder="Cerca una ubicació..." required>
                                                        <button class="btn btn-outline-primary" type="button" 
                                                                onclick="searchLocation('origen')">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Destí</label>
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control" id="destiInput" 
                                                            placeholder="Cerca una ubicació..." required>
                                                        <button class="btn btn-outline-primary" type="button" 
                                                                onclick="searchLocation('desti')">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="routeMap" style="height: 300px;" class="rounded-3 mb-3"></div>
                                        </div>

                                        <!-- Comentaris -->
                                        <div class="col-12">
                                            <label class="form-label">Comentaris</label>
                                            <textarea class="form-control" name="comentaris" rows="3" 
                                                    placeholder="Afegeix detalls addicionals sobre la ruta..."></textarea>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Crear Ruta
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Les Meves Rutes -->
                <div class="modal fade" id="routesListModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-list-check me-2"></i>Les Meves Rutes
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($userRoutes)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Horari</th>
                                                    <th>Ruta</th>
                                                    <th>Vehicle</th>
                                                    <th>Places</th>
                                                    <th>Preu</th>
                                                    <th>Estat</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($userRoutes as $ruta): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($ruta['data_ruta'])); ?></td>
                                                        <td>
                                                            <?php echo substr($ruta['hora_inici'], 0, 5); ?> - 
                                                            <?php echo substr($ruta['hora_fi'], 0, 5); ?>
                                                        </td>
                                                        <td>
                                                            <small class="d-block">
                                                                <i class="bi bi-geo-alt text-success"></i> 
                                                                <?php echo htmlspecialchars($ruta['origen']); ?>
                                                            </small>
                                                            <small class="d-block">
                                                                <i class="bi bi-geo-alt-fill text-danger"></i> 
                                                                <?php echo htmlspecialchars($ruta['desti']); ?>
                                                            </small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($ruta['vehicle_name']); ?></td>
                                                        <td class="text-center"><?php echo $ruta['plazas_disponibles']; ?></td>
                                                        <td><?php echo number_format($ruta['precio_euros'], 2); ?>€</td>
                                                        <td>
                                                            <?php
                                                            switch($ruta['estado']) {
                                                                case 1:
                                                                    $badgeClass = 'bg-warning';   // Pendent
                                                                    break;
                                                                case 2:
                                                                    $badgeClass = 'bg-success';   // Confirmada
                                                                    break;
                                                                case 3:
                                                                    $badgeClass = 'bg-info';      // Completada
                                                                    break;
                                                                case 4:
                                                                    $badgeClass = 'bg-danger';    // Cancel·lada
                                                                    break;
                                                                default:
                                                                    $badgeClass = 'bg-secondary';
                                                                    break;
                                                            }
                                                            $estados = (new HorariRutaModel())->getEstados();
                                                            ?>
                                                            <span class="badge <?php echo $badgeClass; ?>">
                                                                <?php echo $estados[$ruta['estado']] ?? 'Desconegut'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                    onclick="viewRouteDetails(<?php echo $ruta['id']; ?>)">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Encara no tens cap ruta programada.
                                    </div>
                                <?php endif; ?>
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
        // Rent Vehicle Function
        function rentVehicle(vehicleName, pricePerHour) {
            const currentHour = new Date().getHours();
            
            if (currentHour >= 22 || currentHour < 6) {
                showInfoModal('Servei No Disponible', '❌ El servei de lloguer no està disponible entre les 22:00 i les 06:00');
                return;
            }
            
            // Mostrar modal de alquiler con precio en euros
            showRentModal(vehicleName, pricePerHour);
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
                            <p><strong>Cost:</strong> ${cost.toFixed(2)} €</p>
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
        
        // View Reservations
        function viewReservations() {
            const modal = new bootstrap.Modal(document.getElementById('rentalHistoryModal'));
            modal.show();
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

        function showRentModal(vehicleName, pricePerHour) {
            const userSaldo = <?php echo $userPreferences['saldo']; ?>; // Saldo real en euros
            const content = `
                <div class="card">
                    <div class="card-body">
                        <h5><i class="bi bi-car-front me-2"></i>${vehicleName}</h5>
                        <p class="text-muted">Preu: ${pricePerHour.toFixed(2)} €/hora</p>
                        <p class="text-success">Saldo disponible: ${userSaldo.toFixed(2)} €</p>
                        
                        <div class="mb-3">
                            <label for="rentHours" class="form-label">Quantes hores vols llogar?</label>
                            <input type="number" class="form-control" id="rentHours" min="1" max="24" value="1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cost calculat:</label>
                            <div class="h5 text-primary" id="calculatedCost">${pricePerHour.toFixed(2)} €</div>
                        </div>
                        
                        <button class="btn btn-primary w-100" onclick="confirmRental('${vehicleName}', ${pricePerHour}, ${userSaldo})">
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
                const cost = (parseFloat(pricePerHour) * parseFloat(hours)).toFixed(2);
                document.getElementById('calculatedCost').innerHTML = cost + ' €';
            });
        }

        function confirmRental(vehicleName, pricePerHour, userSaldo) {
            const hours = document.getElementById('rentHours').value;
            const totalCost = parseFloat(pricePerHour) * parseFloat(hours);
            
            if (totalCost > userSaldo) {
                showInfoModal('Saldo Insuficient', `
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle text-warning display-4"></i>
                        <h5 class="mt-3">No tens suficient saldo!</h5>
                        <div class="card mt-3">
                            <div class="card-body">
                                <p><strong>Cost:</strong> ${totalCost.toFixed(2)} €</p>
                                <p><strong>Saldo disponible:</strong> ${userSaldo.toFixed(2)} €</p>
                                <p class="text-danger"><strong>Necessites:</strong> ${(totalCost - userSaldo).toFixed(2)} € més</p>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> Contacta amb l'administració per recarregar el teu saldo
                        </div>
                    </div>
                `);
            } else {
                bootstrap.Modal.getInstance(document.getElementById('rentModal')).hide();
                setTimeout(() => showRentalSuccess(vehicleName, hours, totalCost), 300);
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

    <!-- Scripts para el mapa de rutas -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let routeMap;
        let originMarker = null;
        let destinationMarker = null;

        // Inicializar mapa cuando se abre el modal
        document.getElementById('newRouteModal').addEventListener('shown.bs.modal', function () {
            if (!routeMap) {
                const mollerussaCoords = [41.6231, 0.8825];
                routeMap = L.map('routeMap').setView(mollerussaCoords, 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(routeMap);

                // Forzar actualización del mapa
                setTimeout(() => routeMap.invalidateSize(), 100);
            }
        });

        // Buscar ubicación
        function searchLocation(type) {
            const input = type === 'origen' ? 
                        document.getElementById('origenInput').value : 
                        document.getElementById('destiInput').value;

            // Simulación de geocoding con ubicaciones predefinidas
            const locations = {
                'barcelona': { lat: 41.3851, lng: 2.1734, name: 'Barcelona' },
                'lleida': { lat: 41.6175, lng: 0.6200, name: 'Lleida' },
                'tarrega': { lat: 41.6469, lng: 1.1394, name: 'Tàrrega' },
                'mollerussa': { lat: 41.6231, lng: 0.8825, name: 'Mollerussa' },
                'balaguer': { lat: 41.7889, lng: 0.8028, name: 'Balaguer' }
            };

            const location = locations[input.toLowerCase()];
            if (location) {
                setLocation(type, location.lat, location.lng, location.name);
            } else {
                showInfoModal('Ubicació no trobada', 
                    'Prova amb: Barcelona, Lleida, Tàrrega, Mollerussa, Balaguer');
            }
        }

        // Establecer ubicación en el mapa
        function setLocation(type, lat, lng, name) {
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="background-color: ${type === 'origen' ? '#28a745' : '#dc3545'}; 
                                    width: 24px; height: 24px; 
                                    border-radius: 50%; 
                                    display: flex; 
                                    align-items: center; 
                                    justify-content: center; 
                                    color: white; 
                                    font-size: 14px;">
                        ${type === 'origen' ? 'A' : 'B'}
                       </div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                })
            });

            if (type === 'origen') {
                if (originMarker) routeMap.removeLayer(originMarker);
                originMarker = marker;
                document.getElementById('origen_lat').value = lat;
                document.getElementById('origen_lng').value = lng;
                document.getElementById('origenInput').value = name;
            } else {
                if (destinationMarker) routeMap.removeLayer(destinationMarker);
                destinationMarker = marker;
                document.getElementById('desti_lat').value = lat;
                document.getElementById('desti_lng').value = lng;
                document.getElementById('destiInput').value = name;
            }

            marker.addTo(routeMap);
            
            // Si tenemos ambos marcadores, ajustar la vista
            if (originMarker && destinationMarker) {
                const bounds = L.latLngBounds(
                    [originMarker.getLatLng(), destinationMarker.getLatLng()]
                );
                routeMap.fitBounds(bounds, { padding: [50, 50] });
            } else {
                routeMap.setView([lat, lng], 13);
            }
        }

        // Ver detalles de ruta
        function viewRouteDetails(routeId) {
            // Aquí puedes implementar la lógica para mostrar los detalles
            showInfoModal('Detalls de la Ruta', 'Implementació pendent...');
        }
    </script>
</body>
</html>