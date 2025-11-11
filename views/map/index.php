<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Coche Cercano - DriveShare</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <?php include '../templates/navbar.php'; ?>

    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Header Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-geo-alt-fill text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Buscar Coche Cercano</h2>
                            <p class="text-muted">Encuentra el vehículo más cercano a tu ubicación</p>
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
                        <div class="card border-0 shadow-lg rounded-4 mb-3">
                            <div class="card-header bg-success text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-arrow-right-circle me-2"></i>Modo de Búsqueda</h5>
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
                        <div class="card border-0 shadow-lg rounded-4 mb-3" id="routePanel" style="display: none;">
                            <div class="card-header bg-info text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-map me-2"></i>Seleccionar Ruta</h5>
                            </div>
                            <div class="card-body p-3">
                                <!-- Destino manual -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Destino</label>
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

                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Filtros de Búsqueda</h5>
                            </div>
                            <div class="card-body p-4">
                                
                                <!-- Ubicación actual -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mi Ubicación</label>
                                    <div class="d-grid">
                                        <button id="getLocationBtn" class="btn btn-outline-primary rounded-3">
                                            <i class="bi bi-crosshair me-2"></i>Detectar Ubicación
                                        </button>
                                    </div>
                                    <div id="locationStatus" class="small text-muted mt-1"></div>
                                </div>

                                <!-- Radio de búsqueda -->
                                <div class="mb-3">
                                    <label for="radiusSlider" class="form-label fw-bold">Radio de Búsqueda: <span id="radiusValue"><?php echo $radius; ?></span> km</label>
                                    <input type="range" class="form-range" id="radiusSlider" min="1" max="20" value="<?php echo $radius; ?>">
                                </div>

                                <!-- Tipo de vehículo -->
                                <div class="mb-3">
                                    <label for="vehicleTypeFilter" class="form-label fw-bold">Tipo de Vehículo</label>
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
                                    <label class="form-label fw-bold">Mostrar en Mapa</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showGasStations" checked>
                                        <label class="form-check-label" for="showGasStations">
                                            <i class="bi bi-fuel-pump me-1"></i>Gasolineras
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showParkings" checked>
                                        <label class="form-check-label" for="showParkings">
                                            <i class="bi bi-p-square me-1"></i>Parkings
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <!-- Leyenda -->
                                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Leyenda</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #007bff; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>
                                    <small>Vehículo disponible</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #28a745; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>
                                    <small>Vehículo eléctrico</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #fd7e14; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>
                                    <small>Motocicleta</small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle me-2" style="width: 20px; height: 20px; background: #dc3545; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>
                                    <small>No disponible</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body p-0">
                                <div id="map" style="height: 500px; width: 100%; border-radius: 10px;"></div>
                            </div>
                        </div>
                        
                        <!-- Información rápida -->
                        <div class="mt-3">
                            <div class="row text-center g-2">
                                <div class="col-4">
                                    <div class="card bg-primary text-white border-0 rounded-3">
                                        <div class="card-body py-3">
                                            <h6 class="mb-1" id="totalVehicles"><?php echo count($vehicles ?? []); ?></h6>
                                            <small>Vehículos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-success text-white border-0 rounded-3">
                                        <div class="card-body py-3">
                                            <h6 class="mb-1" id="availableVehicles"><?php echo count(array_filter($vehicles ?? [], function($v) { return $v['disponible']; })); ?></h6>
                                            <small>Disponibles</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-info text-white border-0 rounded-3">
                                        <div class="card-body py-3">
                                            <h6 class="mb-1" id="nearestDistance"><?php echo !empty($vehicles) ? $vehicles[0]['distancia'] : '0'; ?> km</h6>
                                            <small>Más cercano</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de vehículos -->
                    <div class="col-lg-3">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehículos Cercanos</h5>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <div id="vehiclesList">
                                    <?php foreach ($vehicles ?? [] as $vehicle): ?>
                                        <div class="vehicle-card border-bottom p-3" data-vehicle-id="<?php echo $vehicle['id']; ?>" onclick="selectVehicle(<?php echo $vehicle['id']; ?>)" style="cursor: pointer; transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($vehicle['nombre']); ?></h6>
                                                <span class="badge bg-primary rounded-pill"><?php echo $vehicle['distancia']; ?> km</span>
                                            </div>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <img src="<?php echo $vehicle['imagen']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($vehicle['nombre']); ?>">
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted"><?php echo $vehicle['marca']; ?></small><br>
                                            <strong class="text-primary">€<?php echo number_format($vehicle['precio_hora'], 0); ?>/h</strong><br>
                                            <span class="badge bg-<?php echo $vehicle['disponible'] ? 'success' : 'danger'; ?> text-white">
                                                <?php echo $vehicle['disponible'] ? 'Disponible' : 'No disponible'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vehicle['ubicacion']['descripcion']); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small>Combustible/Batería:</small>
                                        <div class="progress" style="width: 60px; height: 8px;">
                                            <div class="progress-bar battery-level" style="width: <?php echo $vehicle['bateria']; ?>%"></div>
                                        </div>
                                        <small><?php echo $vehicle['bateria']; ?>%</small>
                                    </div>
                                    
                                    <?php if ($vehicle['disponible']): ?>
                                        <div class="d-grid">
                                            <button class="btn btn-primary btn-sm" onclick="quickReserve(<?php echo $vehicle['id']; ?>)" data-bs-toggle="modal" data-bs-target="#quickReserveModal">
                                                <i class="fas fa-bolt"></i> Reserva Rápida
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reserva Rápida -->
    <div class="modal fade" id="quickReserveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bolt"></i> Reserva Rápida: <span id="modalVehicleName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="quick_reserve">
                        <input type="hidden" name="vehicle_id" id="modalVehicleId">
                        
                        <div class="mb-3">
                            <label for="duracion" class="form-label">Duración (horas)</label>
                            <select class="form-select" name="duracion" id="duracion" onchange="updateQuickPrice()" required>
                                <option value="1">1 hora</option>
                                <option value="2">2 horas</option>
                                <option value="3">3 horas</option>
                                <option value="4">4 horas</option>
                                <option value="6">6 horas</option>
                                <option value="8">8 horas</option>
                                <option value="12">12 horas</option>
                                <option value="24">24 horas (1 día)</option>
                            </select>
                        </div>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Resumen</h6>
                                <p class="mb-1">Precio por hora: €<span id="modalPricePerHour">0</span></p>
                                <p class="mb-1">Duración: <span id="modalDuration">1</span> hora(s)</p>
                                <p class="mb-1">Ubicación: <span id="modalLocation"></span></p>
                                <hr>
                                <h6 class="mb-0">Total: €<span id="modalTotalPrice">0</span></h6>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmar Reserva
                        </button>
                    </div>
                </form>
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
                        ${vehicle.disponible ? `<br><button class="btn btn-primary btn-sm mt-2" onclick="quickReserve(${vehicle.id})" data-bs-toggle="modal" data-bs-target="#quickReserveModal">Reservar</button>` : ''}
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                marker.on('click', () => selectVehicle(vehicle.id));
                
                vehicleMarkers.push(marker);
            });
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
                        <p class="mb-0"><small>${station.servicios.join(', ')}</small></p>
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
                        <p class="mb-0"><small>${parking.servicios.join(', ')}</small></p>
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

        // Seleccionar vehículo
        function selectVehicle(vehicleId) {
            // Remover selección anterior
            document.querySelectorAll('.vehicle-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Seleccionar nuevo vehículo
            const vehicleCard = document.querySelector(`[data-vehicle-id="${vehicleId}"]`);
            if (vehicleCard) {
                vehicleCard.classList.add('selected');
            }
            
            // Encontrar vehículo en la lista
            const vehicle = currentVehicles.find(v => v.id === vehicleId);
            if (vehicle) {
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
            }
            
            selectedVehicleId = vehicleId;
        }

        // Reserva rápida
        function quickReserve(vehicleId) {
            const vehicle = currentVehicles.find(v => v.id === vehicleId);
            if (vehicle) {
                document.getElementById('modalVehicleId').value = vehicleId;
                document.getElementById('modalVehicleName').textContent = vehicle.nombre;
                document.getElementById('modalPricePerHour').textContent = vehicle.precio_hora.toFixed(2);
                document.getElementById('modalLocation').textContent = vehicle.ubicacion.descripcion;
                updateQuickPrice();
            }
        }

        // Actualizar precio de reserva rápida
        function updateQuickPrice() {
            const vehicleId = document.getElementById('modalVehicleId').value;
            const duracion = parseInt(document.getElementById('duracion').value);
            const vehicle = currentVehicles.find(v => v.id == vehicleId);
            
            if (vehicle) {
                const total = vehicle.precio_hora * duracion;
                document.getElementById('modalDuration').textContent = duracion;
                document.getElementById('modalTotalPrice').textContent = total.toFixed(2);
            }
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