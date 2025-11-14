<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/PHP');
}
?>
<!DOCTYPE html>
<html lang="ca" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Coche Cercano - DriveShare</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="../../public/css/modern-styles.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%) !important;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15), transparent 40%);
            z-index: -1;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Header Card -->
                <div class="glass-card shadow-lg rounded-4 mb-4 fade-in" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-geo-alt-fill display-4 pulse-icon" style="color: #6366f1;"></i>
                            <h2 class="fw-bold mt-3" style="font-size: 2.5rem; color: #1f2937;">Buscar Coche Cercano</h2>
                            <p style="font-size: 1.1rem; color: #6b7280;">Encuentra el vehículo más cercano a tu ubicación</p>
                        </div>
                    </div>
                </div>

                <!-- Mensajes -->
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Panel de controles -->
                    <div class="col-lg-3 mb-4">
                        <!-- Selector de modo de búsqueda -->
                        <div class="glass-card shadow-lg rounded-4 mb-3" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                            <div class="card-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-right-circle me-2"></i>Modo de Búsqueda</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="searchMode" id="nearbyMode" value="nearby" checked>
                                    <label class="btn btn-outline-primary" for="nearbyMode">
                                        <i class="bi bi-geo-alt"></i> Cercanos
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="searchMode" id="routeMode" value="route">
                                    <label class="btn btn-outline-success" for="routeMode">
                                        <i class="bi bi-arrow-right"></i> Por Ruta
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de rutas (oculto por defecto) -->
                        <div class="glass-card shadow-lg rounded-4 mb-3" id="routePanel" style="display: none; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                            <div class="card-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-map me-2"></i>Seleccionar Ruta</h5>
                            </div>
                            <div class="card-body p-3" style="color: #1f2937;">
                                <!-- Destino manual -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="color: #1f2937;">Destino</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="destinoInput" placeholder="Escribe tu destino...">
                                        <button class="btn btn-outline-secondary" id="searchDestinoBtn">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Ejemplo: Barcelona, Lleida, Aeropuerto...</small>
                                </div>

                                <!-- Rutas populares -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rutas Populares</label>
                                    <div id="popularRoutes" class="d-grid gap-2">
                                        <?php if (isset($popularRoutes)): ?>
                                            <?php foreach ($popularRoutes as $route): ?>
                                                <button class="btn btn-outline-primary btn-sm text-start popular-route-btn" 
                                                        data-lat="<?php echo $route['destino']['lat']; ?>"
                                                        data-lng="<?php echo $route['destino']['lng']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($route['destino']['nombre']); ?>"
                                                        data-distancia="<?php echo $route['distancia']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($route['nombre']); ?></strong>
                                                            <br><small class="text-muted"><?php echo $route['distancia']; ?> km • <?php echo $route['duracion_estimada']; ?> min</small>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-success"><?php echo $route['vehiculos_disponibles']; ?> coches</small>
                                                        </div>
                                                    </div>
                                                </button>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Información de ruta seleccionada -->
                                <div id="selectedRouteInfo" class="alert alert-info" style="display: none;">
                                    <h6><i class="bi bi-route me-2"></i>Ruta Seleccionada</h6>
                                    <p class="mb-1"><strong>Destino:</strong> <span id="selectedDestinationName"></span></p>
                                    <p class="mb-1"><strong>Distancia:</strong> <span id="selectedDistance"></span> km</p>
                                    <p class="mb-0"><strong>Vehículos encontrados:</strong> <span id="foundVehicles">0</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card shadow-lg rounded-4" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                            <div class="card-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-sliders me-2"></i>Filtros de Búsqueda</h5>
                            </div>
                            <div class="card-body p-4" style="color: #1f2937;">
                                
                                <!-- Ubicación actual -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="color: #1f2937;">Mi Ubicación</label>
                                    <div class="d-grid">
                                        <button id="getLocationBtn" class="btn btn-primary rounded-3">
                                            <i class="bi bi-crosshair me-2"></i>Detectar Ubicación
                                        </button>
                                    </div>
                                    <div id="locationStatus" class="small mt-1" style="color: #6b7280;"></div>
                                </div>

                                <!-- Radio de búsqueda -->
                                <div class="mb-3">
                                    <label for="radiusSlider" class="form-label fw-bold" style="color: #1f2937;">Radio de Búsqueda: <span id="radiusValue"><?php echo $radius; ?></span> km</label>
                                    <input type="range" class="form-range" id="radiusSlider" min="1" max="20" value="<?php echo $radius; ?>">
                                </div>

                                <!-- Tipo de vehículo -->
                                <div class="mb-3">
                                    <label for="vehicleTypeFilter" class="form-label fw-bold" style="color: #1f2937;">Tipo de Vehículo</label>
                                    <select class="form-select rounded-3" id="vehicleTypeFilter">
                                        <option value="">Todos los tipos</option>
                                        <option value="city">Urbano</option>
                                        <option value="compacto">Compacto</option>
                                        <option value="sedan">Sedan</option>
                                        <option value="electrico">Eléctrico</option>
                                        <option value="furgoneta">Furgoneta</option>
                                        <option value="moto">Motocicleta</option>
                                    </select>
                                </div>

                                <!-- Servicios adicionales -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="color: #1f2937;">Mostrar en Mapa</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showGasStations" checked>
                                        <label class="form-check-label" for="showGasStations" style="color: #1f2937;">
                                            <i class="bi bi-fuel-pump me-1 text-warning"></i>Gasolineras
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showParkings" checked>
                                        <label class="form-check-label" for="showParkings" style="color: #1f2937;">
                                            <i class="bi bi-p-square me-1 text-primary"></i>Parkings
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <!-- Leyenda -->
                                <h6 class="fw-bold" style="color: #1f2937;"><i class="bi bi-info-circle me-2 text-primary"></i>Leyenda</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #6366f1; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                    <small class="fw-semibold" style="color: #1f2937;">Vehículo disponible</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #10b981; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                    <small class="fw-semibold" style="color: #1f2937;">Vehículo eléctrico</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #f59e0b; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                    <small class="fw-semibold" style="color: #1f2937;">Motocicleta</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #ef4444; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                    <small class="fw-semibold" style="color: #1f2937;">No disponible</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa y Reserva Rápida (juntos) -->
                    <div class="col-lg-9 mb-4">
                        <div class="row">
                            <!-- Mapa -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-lg rounded-4">
                                    <div class="card-body p-0">
                                        <div id="map" style="height: 700px; width: 100%; border-radius: 10px;"></div>
                                    </div>
                                </div>
                                
                                <!-- Información rápida -->
                                <div class="mt-3">
                                    <div class="row text-center g-3">
                                        <div class="col-4">
                                            <div class="rounded-3 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 1rem;">
                                                <h5 class="mb-1 fw-bold" id="totalVehicles"><?php echo count($vehicles ?? []); ?></h5>
                                                <small class="fw-semibold">Vehículos</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="rounded-3 shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem;">
                                                <h5 class="mb-1 fw-bold" id="availableVehicles"><?php echo count(array_filter($vehicles ?? [], function($v) { return $v['disponible']; })); ?></h5>
                                                <small class="fw-semibold">Disponibles</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="rounded-3 shadow-sm" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; padding: 1rem;">
                                                <h5 class="mb-1 fw-bold" id="nearestDistance"><?php echo !empty($vehicles) ? $vehicles[0]['distancia'] : '0'; ?> km</h5>
                                                <small class="fw-semibold">Más cercano</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reserva Rápida (a la derecha del mapa) -->
                            <div class="col-lg-4">
                        <div class="glass-card shadow-lg rounded-4 mb-3" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); max-height: 800px; overflow-y: auto;">
                            <div class="card-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill me-2"></i>Reserva Rápida</h5>
                            </div>
                            <div class="card-body p-3" style="color: #1f2937;">
                                <!-- Lista de vehículos cercanos -->
                                <div id="nearbyVehiclesList" class="mb-3">
                                    <h6 class="fw-bold mb-3" style="color: #1f2937;"><i class="bi bi-geo-alt-fill me-2"></i>Vehículos Cercanos</h6>
                                    <div id="vehicleListContainer">
                                        <!-- Se llenará dinámicamente con JavaScript -->
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <!-- Contenido dinámico cuando se selecciona un vehículo -->
                                <div id="selectedVehicleInfo" style="display: none;">
                                    <div class="text-center mb-3">
                                        <img id="quickVehicleImage" src="" class="img-fluid rounded-3 mb-2" style="max-height: 150px;" alt="Vehicle">
                                        <h5 class="fw-bold mb-1" id="quickVehicleName"></h5>
                                        <p class="text-muted mb-0"><span id="quickVehicleMarca"></span></p>
                                        <span class="badge rounded-pill mt-2" id="quickVehicleBadge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"></span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>Distancia:</span>
                                            <strong id="quickVehicleDistance"></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted"><i class="bi bi-cash me-1"></i>Precio:</span>
                                            <strong class="text-primary" id="quickVehiclePrice"></strong>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" id="quickReserveForm">
                                        <input type="hidden" name="action" value="quick_reserve">
                                        <input type="hidden" name="vehicle_id" id="quickVehicleId">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: #1f2937;">Duración</label>
                                            <select class="form-select" name="duracion" id="quickDuracion" required>
                                                <option value="1">1 hora</option>
                                                <option value="2">2 horas</option>
                                                <option value="3">3 horas</option>
                                                <option value="4">4 horas</option>
                                                <option value="6">6 horas</option>
                                                <option value="8">8 horas</option>
                                                <option value="12">12 horas</option>
                                                <option value="24">24 horas</option>
                                            </select>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <strong>Total: €<span id="quickTotalPrice">0</span></strong>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-lg rounded-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; font-weight: bold;">
                                                <i class="bi bi-check-circle-fill me-2"></i>Confirmar Reserva
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary rounded-3" onclick="clearQuickReserve()">
                                                <i class="bi bi-x-circle me-2"></i>Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let userMarker;
        let userLocation = null;
        let vehicleMarkers = [];
        let gasStationMarkers = [];
        let parkingMarkers = [];
        let selectedVehicleId = null;
        let currentVehicles = <?php echo json_encode($vehicles ?? []); ?>;
        let gasStations = <?php echo json_encode($gasStations); ?>;
        let parkings = <?php echo json_encode($parkings); ?>;

        // Inicializar mapa
        function initMap() {
            // Coordenadas de Mollerussa
            const mollerussaCoords = [41.6231, 0.8825];
            
            map = L.map('map').setView(mollerussaCoords, 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Cargar marcadores iniciales
            loadVehicleMarkers();
            loadGasStationMarkers();
            loadParkingMarkers();
        }

        // Cargar marcadores de vehículos
        function loadVehicleMarkers() {
            // Limpiar marcadores existentes
            vehicleMarkers.forEach(marker => map.removeLayer(marker));
            vehicleMarkers = [];
            
            currentVehicles.forEach(vehicle => {
                const icon = getVehicleIcon(vehicle);
                const marker = L.marker([vehicle.ubicacion.lat, vehicle.ubicacion.lng], {
                    icon: icon
                }).addTo(map);
                
                const popupContent = `
                    <div class="text-center">
                        <h6>${vehicle.nombre}</h6>
                        <img src="${vehicle.imagen}" style="width: 100px; height: auto;" class="mb-2">
                        <p class="mb-1"><strong>€${vehicle.precio_hora}/hora</strong></p>
                        <p class="mb-1">${vehicle.ubicacion.descripcion}</p>
                        <p class="mb-1">Distancia: ${vehicle.distancia} km</p>
                        <span class="badge bg-${vehicle.disponible ? 'success' : 'danger'}">${vehicle.disponible ? 'Disponible' : 'No disponible'}</span>
                        ${vehicle.disponible ? `<br><button class="btn btn-primary btn-sm mt-2" onclick="selectVehicle(${vehicle.id})">Reservar</button>` : ''}
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                marker.on('click', () => selectVehicle(vehicle.id));
                
                vehicleMarkers.push(marker);
            });
            
            // Actualizar lista de vehículos cercanos
            updateNearbyVehiclesList();
        }
        
        // Actualizar lista de vehículos cercanos en el panel
        function updateNearbyVehiclesList() {
            const container = document.getElementById('vehicleListContainer');
            if (!container) return;
            
            if (currentVehicles.length === 0) {
                container.innerHTML = '<p class="text-muted small">No hay vehículos disponibles</p>';
                return;
            }
            
            let html = '';
            currentVehicles.slice(0, 10).forEach(vehicle => {
                if (!vehicle.disponible) return;
                
                const badgeColor = vehicle.disponible ? '#10b981' : '#ef4444';
                html += `
                    <div class="vehicle-list-item mb-2 p-2 rounded-3 shadow-sm" 
                         style="cursor: pointer; background: rgba(255, 255, 255, 0.5); border: 2px solid transparent; transition: all 0.3s;"
                         onmouseover="this.style.borderColor='#f59e0b'; this.style.background='rgba(245, 158, 11, 0.1)'"
                         onmouseout="this.style.borderColor='transparent'; this.style.background='rgba(255, 255, 255, 0.5)'"
                         onclick="selectVehicle(${vehicle.id})">
                        <div class="d-flex align-items-center">
                            <img src="${vehicle.imagen}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded me-2" alt="${vehicle.nombre}">
                            <div class="flex-grow-1">
                                <div class="fw-bold small mb-0" style="color: #1f2937;">${vehicle.nombre}</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">${vehicle.distancia} km</small>
                                    <span class="badge badge-sm" style="background: ${badgeColor}; font-size: 0.65rem;">€${vehicle.precio_hora}/h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        // Obtener icono según tipo de vehículo
        function getVehicleIcon(vehicle) {
            let color = vehicle.disponible ? '#007bff' : '#dc3545';
            if (vehicle.tipo === 'electrico') color = '#28a745';
            if (vehicle.tipo === 'moto') color = '#fd7e14';
            
            let iconSymbol = '🚗';
            if (vehicle.tipo === 'moto') iconSymbol = '🏍️';
            if (vehicle.tipo === 'furgoneta') iconSymbol = '🚐';
            if (vehicle.tipo === 'electrico') iconSymbol = '⚡';
            
            return L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="vehicle-marker" style="background: ${color};">${iconSymbol}</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
        }

        // Cargar gasolineras
        function loadGasStationMarkers() {
            gasStations.forEach(station => {
                const marker = L.marker([station.lat, station.lng], {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: '<div style="background: #dc3545; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">⛽</div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                });
                
                const popupContent = `
                    <div>
                        <h6>${station.nombre}</h6>
                        <p class="mb-1">${station.direccion}</p>
                        ${station.precio_gasolina ? `<p class="mb-1">Gasolina: €${station.precio_gasolina}</p>` : ''}
                        ${station.precio_diesel ? `<p class="mb-1">Diesel: €${station.precio_diesel}</p>` : ''}
                        ${station.servicios ? `<p class="mb-0"><small>${station.servicios.join(', ')}</small></p>` : ''}
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                gasStationMarkers.push(marker);
                
                if (document.getElementById('showGasStations').checked) {
                    marker.addTo(map);
                }
            });
        }

        // Cargar parkings
        function loadParkingMarkers() {
            parkings.forEach(parking => {
                const marker = L.marker([parking.lat, parking.lng], {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: '<div style="background: #6c757d; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">🅿️</div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                });
                
                const popupContent = `
                    <div>
                        <h6>${parking.nombre}</h6>
                        <p class="mb-1">${parking.direccion}</p>
                        <p class="mb-1">€${parking.precio_hora}/hora</p>
                        <p class="mb-1">Plazas: ${parking.plazas_libres}/${parking.plazas_totales}</p>
                        ${parking.servicios ? `<p class="mb-0"><small>${parking.servicios.join(', ')}</small></p>` : ''}
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                parkingMarkers.push(marker);
                
                if (document.getElementById('showParkings').checked) {
                    marker.addTo(map);
                }
            });
        }

        // Detectar ubicación del usuario
        function getUserLocation() {
            const btn = document.getElementById('getLocationBtn');
            const status = document.getElementById('locationStatus');
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detectando...';
            btn.disabled = true;
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Guardar ubicación del usuario
                        userLocation = { lat: lat, lng: lng };
                        
                        // Añadir marcador del usuario
                        if (userMarker) {
                            map.removeLayer(userMarker);
                        }
                        
                        userMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'custom-div-icon',
                                html: '<div style="background: #28a745; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">📍</div>',
                                iconSize: [20, 20],
                                iconAnchor: [10, 10]
                            })
                        }).addTo(map);
                        
                        map.setView([lat, lng], 15);
                        
                        btn.innerHTML = '<i class="fas fa-check"></i> Ubicación detectada';
                        status.textContent = 'Ubicación detectada correctamente';
                        status.className = 'small text-success mt-1';
                        
                        // Actualizar vehículos basado en la nueva ubicación
                        updateVehiclesByLocation(lat, lng);
                    },
                    function(error) {
                        btn.innerHTML = '<i class="fas fa-crosshairs"></i> Detectar Ubicación';
                        btn.disabled = false;
                        status.textContent = 'No se pudo detectar la ubicación';
                        status.className = 'small text-danger mt-1';
                    }
                );
            } else {
                btn.innerHTML = '<i class="fas fa-crosshairs"></i> Detectar Ubicación';
                btn.disabled = false;
                status.textContent = 'Geolocalización no soportada';
                status.className = 'small text-danger mt-1';
            }
        }

        // Seleccionar vehículo (inline quick reserve)
        function selectVehicle(vehicleId) {
            // Encontrar vehículo en la lista
            const vehicle = currentVehicles.find(v => v.id === vehicleId);
            if (!vehicle || !vehicle.disponible) {
                return;
            }
            
            // Centrar mapa en el vehículo
            map.setView([vehicle.ubicacion.lat, vehicle.ubicacion.lng], 16);
            
            // Abrir popup del marcador
            const marker = vehicleMarkers.find(m => 
                m.getLatLng().lat === vehicle.ubicacion.lat && 
                m.getLatLng().lng === vehicle.ubicacion.lng
            );
            if (marker) {
                marker.openPopup();
            }
            
            // Mostrar información en panel de reserva rápida
            const quickContent = document.getElementById('quickReserveContent');
            if (quickContent) {
                quickContent.style.display = 'none';
            }
            
            const selectedInfo = document.getElementById('selectedVehicleInfo');
            if (selectedInfo) {
                selectedInfo.style.display = 'block';
            }
            
            // Actualizar información del vehículo
            const imgElement = document.getElementById('quickVehicleImage');
            const nameElement = document.getElementById('quickVehicleName');
            const marcaElement = document.getElementById('quickVehicleMarca');
            const distanceElement = document.getElementById('quickVehicleDistance');
            const priceElement = document.getElementById('quickVehiclePrice');
            const badgeElement = document.getElementById('quickVehicleBadge');
            const vehicleIdInput = document.getElementById('quickVehicleId');
            
            if (imgElement) imgElement.src = vehicle.imagen;
            if (nameElement) nameElement.textContent = vehicle.nombre;
            if (marcaElement) marcaElement.textContent = vehicle.marca || vehicle.tipo || '';
            if (distanceElement) distanceElement.textContent = vehicle.distancia + ' km';
            if (priceElement) priceElement.textContent = '€' + vehicle.precio_hora.toFixed(2) + '/hora';
            if (badgeElement) badgeElement.textContent = vehicle.disponible ? 'Disponible' : 'No disponible';
            if (vehicleIdInput) vehicleIdInput.value = vehicleId;
            
            // Calcular precio inicial
            updateQuickReservePrice();
            
            // Scroll al formulario de reserva
            selectedInfo?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            selectedVehicleId = vehicleId;
        }

        // Reserva rápida (callback para botones en popups)
        function quickReserve(vehicleId) {
            selectVehicle(vehicleId);
        }

        // Actualizar precio de reserva rápida
        function updateQuickReservePrice() {
            const vehicleId = document.getElementById('quickVehicleId')?.value;
            const duracionElement = document.getElementById('quickDuracion');
            const duracion = duracionElement ? parseInt(duracionElement.value) : 1;
            const vehicle = currentVehicles.find(v => v.id == vehicleId);
            
            if (vehicle) {
                const total = vehicle.precio_hora * duracion;
                const totalElement = document.getElementById('quickTotalPrice');
                if (totalElement) {
                    totalElement.textContent = total.toFixed(2);
                }
            }
        }

        // Limpiar reserva rápida
        function clearQuickReserve() {
            const quickContent = document.getElementById('quickReserveContent');
            const selectedInfo = document.getElementById('selectedVehicleInfo');
            const vehicleIdInput = document.getElementById('quickVehicleId');
            const duracionElement = document.getElementById('quickDuracion');
            
            if (quickContent) quickContent.style.display = 'block';
            if (selectedInfo) selectedInfo.style.display = 'none';
            if (vehicleIdInput) vehicleIdInput.value = '';
            if (duracionElement) duracionElement.value = '1';
            
            selectedVehicleId = null;
        }

        // Event listeners
        document.getElementById('getLocationBtn').addEventListener('click', getUserLocation);
        
        document.getElementById('radiusSlider').addEventListener('input', function() {
            document.getElementById('radiusValue').textContent = this.value;
        });
        
        document.getElementById('showGasStations').addEventListener('change', function() {
            gasStationMarkers.forEach(marker => {
                if (this.checked) {
                    marker.addTo(map);
                } else {
                    map.removeLayer(marker);
                }
            });
        });
        
        document.getElementById('showParkings').addEventListener('change', function() {
            parkingMarkers.forEach(marker => {
                if (this.checked) {
                    marker.addTo(map);
                } else {
                    map.removeLayer(marker);
                }
            });
        });
        
        // Event listener para cambio de duración en reserva rápida
        const quickDuracion = document.getElementById('quickDuracion');
        if (quickDuracion) {
            quickDuracion.addEventListener('change', updateQuickReservePrice);
        }

        // Variables para manejo de rutas
        let currentSearchMode = 'nearby';
        let selectedDestination = null;
        let routeMarkers = [];

        // Event listeners para rutas
        document.getElementsByName('searchMode').forEach(radio => {
            radio.addEventListener('change', function() {
                currentSearchMode = this.value;
                toggleRoutePanel();
                updateSearch();
            });
        });

        // Mostrar/ocultar panel de rutas
        function toggleRoutePanel() {
            const routePanel = document.getElementById('routePanel');
            if (currentSearchMode === 'route') {
                routePanel.style.display = 'block';
            } else {
                routePanel.style.display = 'none';
                clearRouteSelection();
            }
        }

        // Manejar rutas populares
        document.querySelectorAll('.popular-route-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                const nombre = this.dataset.nombre;
                const distancia = this.dataset.distancia;
                
                selectDestination(lat, lng, nombre, distancia);
            });
        });

        // Búsqueda de destino manual
        document.getElementById('searchDestinoBtn').addEventListener('click', function() {
            const destinoInput = document.getElementById('destinoInput').value.trim();
            if (destinoInput) {
                searchDestination(destinoInput);
            }
        });

        document.getElementById('destinoInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const destinoInput = this.value.trim();
                if (destinoInput) {
                    searchDestination(destinoInput);
                }
            }
        });

        // Buscar destino usando geocoding simple
        function searchDestination(query) {
            // Destinos predefinidos para búsqueda rápida
            const predefinedDestinations = {
                'barcelona': { lat: 41.3851, lng: 2.1734, nombre: 'Barcelona Centro' },
                'lleida': { lat: 41.6175, lng: 0.6200, nombre: 'Lleida Centro' },
                'tarrega': { lat: 41.6469, lng: 1.1394, nombre: 'Tàrrega Centro' },
                'balaguer': { lat: 41.7889, lng: 0.8028, nombre: 'Balaguer Centro' },
                'aeropuerto lleida': { lat: 41.7282, lng: 0.5358, nombre: 'Aeroport Lleida-Alguaire' },
                'girona': { lat: 41.9794, lng: 2.8214, nombre: 'Girona Centro' },
                'tarragona': { lat: 41.1189, lng: 1.2445, nombre: 'Tarragona Centro' }
            };

            const lowerQuery = query.toLowerCase();
            const destination = predefinedDestinations[lowerQuery];

            if (destination) {
                selectDestination(destination.lat, destination.lng, destination.nombre);
            } else {
                // Mostrar mensaje si no se encuentra
                alert('Destino no encontrado. Prueba con: Barcelona, Lleida, Tàrrega, Balaguer, Aeropuerto Lleida, Girona, Tarragona');
            }
        }

        // Seleccionar destino
        function selectDestination(lat, lng, nombre, distancia = null) {
            selectedDestination = { lat, lng, nombre };
            
            // Actualizar información de ruta seleccionada
            document.getElementById('selectedDestinationName').textContent = nombre;
            if (distancia) {
                document.getElementById('selectedDistance').textContent = distancia;
            } else {
                // Calcular distancia si tenemos ubicación del usuario
                if (userLocation) {
                    const dist = calculateDistance(userLocation.lat, userLocation.lng, lat, lng);
                    document.getElementById('selectedDistance').textContent = dist.toFixed(1);
                } else {
                    document.getElementById('selectedDistance').textContent = '---';
                }
            }
            
            document.getElementById('selectedRouteInfo').style.display = 'block';
            
            // Agregar marcador de destino en el mapa
            clearRouteMarkers();
            const destinationMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">🎯</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                })
            }).addTo(map);
            
            routeMarkers.push(destinationMarker);
            
            // Actualizar búsqueda de vehículos para la ruta
            updateSearchForRoute();
        }

        // Limpiar selección de ruta
        function clearRouteSelection() {
            selectedDestination = null;
            document.getElementById('selectedRouteInfo').style.display = 'none';
            document.getElementById('destinoInput').value = '';
            clearRouteMarkers();
            updateSearch();
        }

        // Limpiar marcadores de ruta
        function clearRouteMarkers() {
            routeMarkers.forEach(marker => {
                map.removeLayer(marker);
            });
            routeMarkers = [];
        }

        // Actualizar búsqueda para ruta
        function updateSearchForRoute() {
            if (!userLocation || !selectedDestination) {
                return;
            }

            const params = new URLSearchParams({
                action: 'api',
                search_mode: 'route',
                lat: userLocation.lat,
                lng: userLocation.lng,
                destino_lat: selectedDestination.lat,
                destino_lng: selectedDestination.lng,
                destino_nombre: selectedDestination.nombre,
                vehicle_type: document.getElementById('vehicleTypeFilter').value
            });

            fetch(`../../controllers/MapController.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateVehicleDisplay(data.vehicles);
                        document.getElementById('foundVehicles').textContent = data.vehicles.length;
                    }
                })
                .catch(error => {
                    console.error('Error al buscar vehículos para la ruta:', error);
                });
        }

        // Actualizar búsqueda general
        function updateSearch() {
            if (currentSearchMode === 'route' && selectedDestination && userLocation) {
                updateSearchForRoute();
            } else if (currentSearchMode === 'nearby' && userLocation) {
                updateVehiclesByLocation(userLocation.lat, userLocation.lng);
            }
        }

        // Función auxiliar para calcular distancia
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Inicializar mapa cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>
</html>