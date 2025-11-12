<?php

$message = $message ?? '';
$messageType = $messageType ?? '';
$editingHorari = $editingHorari ?? null;
$myHoraris = $myHoraris ?? [];
$allHoraris = $allHoraris ?? [];
$myReservations = $myReservations ?? [];
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Gestió d'Horaris i Rutes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- DataTables CSS con tema Bootstrap -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Estilos personalizados para Horaris -->
    <link href="../../public/css/horaris.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold text-dark mb-1">
                                    <i class="bi bi-calendar-week text-primary me-2"></i>
                                    Gestió i Rutes
                                </h2>
                                <p class="text-muted mb-0">
                                    Organitza els teus desplaçaments i consulta els d'altres usuaris
                                </p>
                            </div>
                            <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#horariModal">
                                <i class="bi bi-plus-circle me-2"></i>Nou Horari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs nav-fill mb-4" id="horariTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="my-horaris-tab" data-bs-toggle="tab" data-bs-target="#my-horaris" type="button">
                    <i class="bi bi-plus-circle me-2"></i>Rutes creades
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-horaris-tab" data-bs-toggle="tab" data-bs-target="#all-horaris" type="button">
                    <i class="bi bi-bookmark-check me-2"></i>Rutes reservades
                </button>
            </li>
        </ul>

        <!-- Filtros Dinámicos -->
        <div class="card filter-card rounded-4 mb-4">
            <div class="card-header bg-transparent border-0 p-3">
                <div class="d-flex justify-content-between align-items-center filter-toggle" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-funnel me-2 text-primary"></i>Filtres Avançats
                    </h6>
                    <i class="bi bi-chevron-down text-muted"></i>
                </div>
            </div>
            <div class="collapse show" id="filterPanel">
                <div class="card-body pt-0">
                    <div class="row g-3 filter-row">
                        <div class="col-md-4 filter-col">
                            <label class="form-label small fw-semibold text-muted">📅 Data</label>
                            <input type="date" class="form-control form-control-sm" id="filterDate" placeholder="Selecciona data">
                        </div>
                        <div class="col-md-4 filter-col">
                            <label class="form-label small fw-semibold text-muted">🚗 Vehicle</label>
                            <select class="form-select form-select-sm" id="filterVehicle">
                                <option value="">Tots els vehicles</option>
                                <?php
                                // Obtener vehículos desde la base de datos
                                require_once __DIR__ . '/../../config/Database.php';
                                $database = Database::getInstance();
                                $conn = $database->getConnection();
                                $result = $conn->query("SELECT DISTINCT marca_model FROM vehicles WHERE user_id = {$_SESSION['user_id']} ORDER BY marca_model");
                                while ($vehicle = $result->fetch_assoc()) {
                                    echo "<option value=\"{$vehicle['marca_model']}\">{$vehicle['marca_model']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 filter-col">
                            <label class="form-label small fw-semibold text-muted">� Ubicació</label>
                            <input type="text" class="form-control form-control-sm" id="filterLocation" placeholder="Origen o destí">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="clearFilters">
                                    <i class="bi bi-x-circle me-1"></i>Netejar
                                </button>
                                <div class="ms-auto d-flex gap-2 align-items-center">
                                    <small class="text-muted">Resultats: <span id="resultsCount" class="fw-bold">0</span></small>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoFilter" checked>
                                        <label class="form-check-label small" for="autoFilter">Auto-filtrar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="horariTabsContent">
            <!-- Rutes creades -->
            <div class="tab-pane fade show active" id="my-horaris" role="tabpanel">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Les teves Rutes creades
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($myHoraris) > 0): ?>
                            <div class="table-responsive">
                                <table id="myHorarisTable" class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Horari</th>
                                            <th>Ruta</th>
                                            <th>Vehicle</th>
                                            <th>Comentaris</th>
                                            <th>Accions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myHoraris as $row): ?>
                                            <tr class="horari-row" 
                                                data-date="<?php echo $row['data_ruta']; ?>"
                                                data-vehicle="<?php echo htmlspecialchars($row['vehicle']); ?>"
                                                data-origen="<?php echo htmlspecialchars($row['origen']); ?>"
                                                data-desti="<?php echo htmlspecialchars($row['desti']); ?>"
                                                data-user="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['cognoms']); ?>"
                                                data-tab="my">
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo date('d/m/Y', strtotime($row['data_ruta'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('H:i', strtotime($row['hora_inici'])); ?> - 
                                                        <?php echo date('H:i', strtotime($row['hora_fi'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['origen']); ?></strong>
                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                    <strong><?php echo htmlspecialchars($row['desti']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($row['vehicle']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($row['comentaris']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../../public/index.php?controller=horaris&action=index&edit=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="../../public/index.php?controller=horaris&action=index&delete=<?php echo $row['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                           onclick="return confirm('Estàs segur que vols eliminar aquest horari?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x text-muted display-4"></i>
                                <h5 class="text-muted mt-3">No tens cap horari creat</h5>
                                <p class="text-muted">Fes clic a "Nou Horari" per crear el teu primer horari</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rutes reservades -->
            <div class="tab-pane fade" id="all-horaris" role="tabpanel">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-bookmark-check me-2"></i>Les teves Rutes reservades
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (isset($myReservations) && count($myReservations) > 0): ?>
                            <div class="table-responsive">
                                <table id="reservedHorarisTable" class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Horari</th>
                                            <th>Ruta</th>
                                            <th>Vehicle</th>
                                            <th>Conductor</th>
                                            <th>Places reservades</th>
                                            <th>Comentaris</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myReservations as $reservation): ?>
                                            <tr class="horari-row" 
                                                data-date="<?php echo $reservation['data_ruta']; ?>"
                                                data-vehicle="<?php echo htmlspecialchars($reservation['vehicle']); ?>"
                                                data-origen="<?php echo htmlspecialchars($reservation['origen']); ?>"
                                                data-desti="<?php echo htmlspecialchars($reservation['desti']); ?>"
                                                data-user="<?php echo htmlspecialchars($reservation['nom'] . ' ' . $reservation['cognoms']); ?>"
                                                data-tab="reserved">
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo date('d/m/Y', strtotime($reservation['data_ruta'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('H:i', strtotime($reservation['hora_inici'])); ?> - 
                                                        <?php echo date('H:i', strtotime($reservation['hora_fi'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($reservation['origen']); ?></strong>
                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                    <strong><?php echo htmlspecialchars($reservation['desti']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($reservation['vehicle']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle me-2"></i>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($reservation['nom'] . ' ' . $reservation['cognoms']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($reservation['email']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo $reservation['plazas']; ?> places
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($reservation['comentaris']); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bookmark-x text-muted display-4"></i>
                                <h5 class="text-muted mt-3">No tens cap ruta reservada</h5>
                                <p class="text-muted mb-4">Explora les rutes disponibles i reserva la que millor s'adapti a les teves necessitats</p>
                                <a href="../../public/index.php?controller=rutes&action=index" class="btn btn-warning btn-lg px-4">
                                    <i class="bi bi-search me-2"></i>
                                    Veure rutes disponibles
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Horario -->
    <div class="modal fade" id="horariModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>
                        <?php echo $editingHorari ? 'Editar Horari' : 'Nou Horari'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="../../public/index.php?controller=horaris&action=index">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $editingHorari ? 'update' : 'create'; ?>">
                        <?php if ($editingHorari): ?>
                            <input type="hidden" name="id" value="<?php echo $editingHorari['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date me-1"></i>Data de la Ruta
                                </label>
                                <input type="date" class="form-control" name="data_ruta" 
                                       value="<?php echo $editingHorari ? $editingHorari['data_ruta'] : ''; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Hora Inici
                                </label>
                                <input type="time" class="form-control" name="hora_inici" 
                                       value="<?php echo $editingHorari ? $editingHorari['hora_inici'] : ''; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock-fill me-1"></i>Hora Fi
                                </label>
                                <input type="time" class="form-control" name="hora_fi" 
                                       value="<?php echo $editingHorari ? $editingHorari['hora_fi'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt me-1"></i>Origen
                                </label>
                                <div class="map-input-group">
                                    <div id="map-origen" class="map-container"></div>
                                    <input type="hidden" name="origen" id="origen-input" 
                                           value="<?php echo $editingHorari ? htmlspecialchars($editingHorari['origen']) : ''; ?>" required>
                                    <div class="location-display" id="origen-display">
                                        <i class="bi bi-geo-alt text-muted me-2"></i>
                                        <span><?php echo $editingHorari ? htmlspecialchars($editingHorari['origen']) : 'Fes clic al mapa per seleccionar l\'origen'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Destí
                                </label>
                                <div class="map-input-group">
                                    <div id="map-desti" class="map-container"></div>
                                    <input type="hidden" name="desti" id="desti-input" 
                                           value="<?php echo $editingHorari ? htmlspecialchars($editingHorari['desti']) : ''; ?>" required>
                                    <div class="location-display" id="desti-display">
                                        <i class="bi bi-geo-alt-fill text-muted me-2"></i>
                                        <span><?php echo $editingHorari ? htmlspecialchars($editingHorari['desti']) : 'Fes clic al mapa per seleccionar el destí'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-car-front me-1"></i>Vehicle
                            </label>
                            <select class="form-control" name="vehicle" required>
                                <option value="">Selecciona un vehicle</option>
                                <?php
                                // Obtener vehículos del usuario desde la base de datos
                                if (!isset($conn)) {
                                    require_once __DIR__ . '/../../config/Database.php';
                                    $database = Database::getInstance();
                                    $conn = $database->getConnection();
                                }
                                $result = $conn->query("SELECT id, marca_model, tipus FROM vehicles WHERE user_id = {$_SESSION['user_id']} ORDER BY marca_model");
                                while ($vehicle = $result->fetch_assoc()) {
                                    $selected = ($editingHorari && $editingHorari['vehicle'] == $vehicle['marca_model']) ? 'selected' : '';
                                    echo "<option value=\"{$vehicle['marca_model']}\" data-vehicle-id=\"{$vehicle['id']}\" $selected>{$vehicle['marca_model']} ({$vehicle['tipus']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-chat-text me-1"></i>Comentaris
                            </label>
                            <textarea class="form-control" name="comentaris" rows="3" 
                                      placeholder="Descripció opcional del viatge..."><?php echo $editingHorari ? htmlspecialchars($editingHorari['comentaris']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            <?php echo $editingHorari ? 'Actualitzar' : 'Crear'; ?> Horari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- jQuery PRIMERO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables JS con tema Bootstrap -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    
    <!-- JSZip y PDFMake para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    

    <script>
        // ==========================================
        // VARIABLES GLOBALES
        // ==========================================
        let mapOrigen, mapDesti;
        let markerOrigen, markerDesti;

        // ==========================================
        // INICIALIZACIÓN AL CARGAR EL DOM
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM cargado - Inicializando sistema');
            console.log('✓ Bootstrap Icons:', document.querySelector('link[href*="bootstrap-icons"]') ? 'Cargado' : 'No encontrado');
            
            // Delay para asegurar que el modal esté en el DOM
            setTimeout(initMaps, 500);
            
            // Inicializar filtros
            initFilters();
            
            // Inicializar DataTables para ambas tablas
            initDataTables();
            
            console.log('🎯 Sistema inicializado completamente');
        });

        // ==========================================
        // FUNCIONES DE MAPAS (LEAFLET)
        // ==========================================

        function initMaps() {
            // Configuración por defecto de Barcelona
            const defaultCenter = [41.3851, 2.1734];
            
            // Inicializar mapa de origen
            mapOrigen = L.map('map-origen').setView(defaultCenter, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapOrigen);

            // Inicializar mapa de destino
            mapDesti = L.map('map-desti').setView(defaultCenter, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapDesti);

            // Event listeners para los mapas
            mapOrigen.on('click', function(e) {
                setLocation(e.latlng, 'origen');
            });

            mapDesti.on('click', function(e) {
                setLocation(e.latlng, 'desti');
            });

            // Cargar ubicaciones existentes si estamos editando
            <?php if ($editingHorari): ?>
                if ('<?php echo $editingHorari['origen']; ?>') {
                    geocodeAddress('<?php echo addslashes($editingHorari['origen']); ?>', 'origen');
                }
                if ('<?php echo $editingHorari['desti']; ?>') {
                    geocodeAddress('<?php echo addslashes($editingHorari['desti']); ?>', 'desti');
                }
            <?php endif; ?>
        }

        function setLocation(latlng, type) {
            const map = type === 'origen' ? mapOrigen : mapDesti;
            let marker = type === 'origen' ? markerOrigen : markerDesti;
            
            // Eliminar marcador anterior si existe
            if (marker) {
                map.removeLayer(marker);
            }
            
            // Crear nuevo marcador
            const iconColor = type === 'origen' ? 'green' : 'red';
            marker = L.marker([latlng.lat, latlng.lng], {
                icon: L.icon({
                    iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${iconColor}.png`,
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            
            if (type === 'origen') {
                markerOrigen = marker;
            } else {
                markerDesti = marker;
            }
            
            // Obtener dirección usando geocoding inverso (Nominatim)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        const address = data.display_name;
                        document.getElementById(type + '-input').value = address;
                        document.getElementById(type + '-display').querySelector('span').textContent = address;
                    }
                })
                .catch(error => {
                    console.error('Error en geocoding:', error);
                    const coords = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
                    document.getElementById(type + '-input').value = coords;
                    document.getElementById(type + '-display').querySelector('span').textContent = coords;
                });
        }

        function geocodeAddress(address, type) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        const map = type === 'origen' ? mapOrigen : mapDesti;
                        
                        map.setView([lat, lon], 15);
                        setLocation({lat: lat, lng: lon}, type);
                    }
                })
                .catch(error => {
                    console.error('Error en geocoding de dirección:', error);
                });
        }

        // Reinicializar mapas cuando se abre el modal
        document.getElementById('horariModal').addEventListener('shown.bs.modal', function () {
            setTimeout(() => {
                mapOrigen.invalidateSize();
                mapDesti.invalidateSize();
                
                // Recentrar mapas
                const defaultCenter = [41.3851, 2.1734];
                mapOrigen.setView(defaultCenter, 12);
                mapDesti.setView(defaultCenter, 12);
            }, 100);
        });


        
        // ==========================================
        // INICIALIZACIÓN DE DATATABLES
        // ==========================================
        function initDataTables() {
            // Inicializar DataTable para Els meus Horaris
            initMyHorarisTable();
            
            // Inicializar DataTable para Rutes reservades
            initReservedHorarisTable();
        }
        
        function initMyHorarisTable() {
            const table = $('#myHorarisTable');
            if (table.length === 0) {
                console.log('❌ Tabla myHorarisTable no encontrada');
                return;
            }
            
            // Configuración de DataTables para Els meus Horaris
            const dataTable = table.DataTable({
                // Idioma en catalán/español
                language: {
                    search: "🔍 Buscar en les teves rutes:",
                    lengthMenu: "📄 Mostrar _MENU_ horaris per pàgina",
                    info: "📊 Mostrant _START_ a _END_ de _TOTAL_ rutes",
                    infoEmpty: "🚧 No hi ha rutes per mostrar",
                    infoFiltered: "(filtrat de _MAX_ rutes totals)",
                    zeroRecords: "🔍 No s'han trobat rutes que coincideixin",
                    emptyTable: "📅 No tens cap ruta creada encara",
                    paginate: {
                        first: "⏪ Primer",
                        last: "⏩ Últim",
                        next: "▶️ Següent",
                        previous: "◀️ Anterior"
                    },
                    aria: {
                        sortAscending: ": activar per ordenar la columna de manera ascendent",
                        sortDescending: ": activar per ordenar la columna de manera descendent"
                    },
                    searchPlaceholder: "Escriu per buscar..."
                },
                
                // Configuración de páginas
                pageLength: 8,
                lengthMenu: [[5, 8, 15, 25, -1], [5, 8, 15, 25, "📅 Tots"]],
                
                // Características
                searching: true,
                ordering: true,
                paging: true,
                info: true,
                responsive: true,
                stateSave: true,
                
                // Configuración de columnas
                columnDefs: [
                    {
                        targets: [0], // Data
                        orderable: true,
                        type: 'date'
                    },
                    {
                        targets: [1, 2, 3, 4], // Horari, Ruta, Vehicle, Comentaris
                        orderable: true
                    },
                    {
                        targets: 5, // Columna Accions
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                
                // Ordenación inicial por fecha descendente
                order: [[0, 'desc']],
                
                // DOM layout personalizado
                dom: '<"row mb-3"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-8"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row mt-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                
                // Efectos de animación
                drawCallback: function() {
                    $(this.api().table().body()).find('tr').addClass('fade-in');
                },
                
                // Callback de inicialización
                initComplete: function() {
                    console.log('✅ DataTable inicializada correctamente para Rutes creades');
                    
                    // Personalizar controles
                    $('#myHorarisTable_wrapper .dataTables_filter input').attr('placeholder', '🔍 Buscar els meus horaris...');
                    $('#myHorarisTable_wrapper .dataTables_length label').prepend('<i class="bi bi-list-ul me-2 text-primary"></i>');
                    $('#myHorarisTable_wrapper .dataTables_filter label').prepend('<i class="bi bi-search me-2 text-primary"></i>');
                }
            });
            
            return dataTable;
        }
        
        function initReservedHorarisTable() {
            const table = $('#reservedHorarisTable');
            if (table.length === 0) {
                console.log('❌ Tabla reservedHorarisTable no encontrada');
                return;
            }
            
            // Configuración de DataTables para Rutes reservades
            const dataTable = table.DataTable({
                // Idioma en catalán/español
                language: {
                    search: "🔖 Buscar en rutes reservades:",
                    lengthMenu: "📄 Mostrar _MENU_ rutes per pàgina",
                    info: "📊 Mostrant _START_ a _END_ de _TOTAL_ rutes reservades",
                    infoEmpty: "🚧 No hi ha rutes per mostrar",
                    infoFiltered: "(filtrat de _MAX_ rutes totals)",
                    zeroRecords: "🔍 No s'han trobat rutes que coincideixin",
                    emptyTable: "🔖 No tens cap ruta reservada",
                    paginate: {
                        first: "⏪ Primer",
                        last: "⏩ Últim",
                        next: "▶️ Següent",
                        previous: "◀️ Anterior"
                    },
                    aria: {
                        sortAscending: ": activar per ordenar la columna de manera ascendent",
                        sortDescending: ": activar per ordenar la columna de manera descendent"
                    },
                    searchPlaceholder: "Escriu per buscar..."
                },
                
                // Configuración de páginas
                pageLength: 10,
                lengthMenu: [[5, 10, 15, 25, 50, -1], [5, 10, 15, 25, 50, "📖 Totes"]],
                
                // Características
                searching: true,
                ordering: true,
                paging: true,
                info: true,
                responsive: true,
                stateSave: false,
                
                // Configuración de columnas
                columnDefs: [
                    {
                        targets: [0], // Data
                        orderable: true,
                        type: 'date'
                    },
                    {
                        targets: [1, 2, 3, 4, 5, 6], // Horari, Ruta, Vehicle, Conductor, Places, Comentaris
                        orderable: true
                    }
                ],
                
                // Ordenación inicial por fecha descendente
                order: [[0, 'desc']],
                
                // DOM layout personalizado
                dom: '<"row mb-3"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-8"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row mt-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                
                // Efectos de animación
                drawCallback: function() {
                    $(this.api().table().body()).find('tr').addClass('fade-in');
                },
                
                // Callback de inicialización
                initComplete: function() {
                    console.log('✅ DataTable inicializada correctamente para Rutes reservades');
                    
                    // Personalizar controles
                    $('#reservedHorarisTable_wrapper .dataTables_filter input').attr('placeholder', '🔖 Buscar les teves reserves...');
                    $('#reservedHorarisTable_wrapper .dataTables_length label').prepend('<i class="bi bi-bookmark-check me-2 text-success"></i>');
                    $('#reservedHorarisTable_wrapper .dataTables_filter label').prepend('<i class="bi bi-search me-2 text-success"></i>');
                }
            });
            
            return dataTable;
        }

        // ==========================================
        // SISTEMA DE FILTROS DINÁMICOS INTEGRADO CON DATATABLES
        // ==========================================
        function initFilters() {
            const filterDate = document.getElementById('filterDate');
            const filterVehicle = document.getElementById('filterVehicle');
            const filterLocation = document.getElementById('filterLocation');
            const clearFiltersBtn = document.getElementById('clearFilters');
            const autoFilterCheck = document.getElementById('autoFilter');
            const resultsCount = document.getElementById('resultsCount');

            // Event listeners para filtros automáticos
            [filterDate, filterVehicle, filterLocation].forEach(filter => {
                filter.addEventListener('input', () => {
                    if (autoFilterCheck.checked) {
                        applyDataTableFilters();
                    }
                });
                filter.addEventListener('change', () => {
                    if (autoFilterCheck.checked) {
                        applyDataTableFilters();
                    }
                });
            });

            // Limpiar filtros
            clearFiltersBtn.addEventListener('click', () => {
                filterDate.value = '';
                filterVehicle.value = '';
                filterLocation.value = '';
                applyDataTableFilters();
            });

            // Aplicar filtros iniciales
            setTimeout(() => applyDataTableFilters(), 500);
        }

        function applyDataTableFilters() {
            const filterDate = document.getElementById('filterDate').value;
            const filterVehicle = document.getElementById('filterVehicle').value;
            const filterLocation = document.getElementById('filterLocation').value;
            
            // Obtener tab activo
            const activeTab = document.querySelector('.nav-link.active').id;
            const isMyHoraris = activeTab === 'my-horaris-tab';
            const isReserved = activeTab === 'all-horaris-tab';
            
            // Obtener la tabla DataTable correspondiente
            let table;
            if (isMyHoraris) {
                table = $('#myHorarisTable').DataTable();
            } else if (isReserved) {
                table = $('#reservedHorarisTable').DataTable();
            }
            
            if (!table) return;
            
            // Limpiar filtros previos
            table.columns().search('').draw();
            
            // Aplicar filtros por columna
            if (filterDate) {
                table.column(0).search(filterDate); // La columna de fecha es siempre la 0
            }
            
            if (filterVehicle) {
                table.column(isMyHoraris ? 3 : 3).search(filterVehicle); // Columna vehicle
            }
            
            if (filterLocation) {
                table.column(isMyHoraris ? 2 : 2).search(filterLocation); // Columna ruta
            }
            
            // Redraw de la tabla
            table.draw();
            
            // Actualizar contador de resultados
            const info = table.page.info();
            document.getElementById('resultsCount').textContent = info.recordsDisplay;
        }

        // Aplicar filtros al cambiar de tab
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('nav-link')) {
                setTimeout(() => applyDataTableFilters(), 100);
            }
        });

        // Filtros rápidos con teclas
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F para enfocar filtro de ubicación
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('filterLocation').focus();
            }
        });

        // ==========================================
        // FUNCIONES DE WHATSAPP
        // ==========================================

        // Función para iniciar chat en WhatsApp con otro usuario sobre un horario específico
        // Función simplificada para WhatsApp
        function startChat(userId, horariId, userName, routeInfo) {
            const message = `Hola ${userName}! M'interessaria unir-me a la teva ruta: ${routeInfo}. Tens places disponibles?`;
            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
            window.open(whatsappUrl, '_blank');
        }

        // Función simplificada para gestión de chat
        function openChatManagement(horariId, routeInfo) {
            alert('Els usuaris et contactaran per WhatsApp quan estiguin interessats en aquesta ruta: ' + routeInfo);
        }

    </script>
    
    <?php if ($editingHorari): ?>
    <script>
        // Mostrar modal si estamos editando
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('horariModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
