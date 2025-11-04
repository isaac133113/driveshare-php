<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Coche Cercano - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }
        .vehicle-marker {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .vehicle-marker.unavailable {
            background: #dc3545;
        }
        .vehicle-marker.electric {
            background: #28a745;
        }
        .vehicle-marker.moto {
            background: #fd7e14;
        }
        .vehicle-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .vehicle-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .vehicle-card.selected {
            border: 2px solid #007bff;
            background: #f8f9fa;
        }
        .distance-badge {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        .battery-level {
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        .controls-panel {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .info-panel {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../dashboard.php">
                <i class="fas fa-car"></i> DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="../../controllers/VehicleController.php">
                    <i class="fas fa-list"></i> Ver Coches
                </a>
                <a class="nav-link" href="../../controllers/AuthController.php?action=profile">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a class="nav-link" href="../../controllers/AuthController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6"><i class="fas fa-map-marked-alt"></i> Buscar Coche Cercano</h1>
                <p class="lead text-muted">Encuentra el vehículo más cercano a tu ubicación</p>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (isset($message) && !empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Panel de controles -->
            <div class="col-lg-3 mb-4">
                <div class="controls-panel p-4">
                    <h5><i class="fas fa-sliders-h"></i> Filtros de Búsqueda</h5>
                    
                    <!-- Ubicación actual -->
                    <div class="mb-3">
                        <label class="form-label">Mi Ubicación</label>
                        <div class="d-grid">
                            <button id="getLocationBtn" class="btn btn-outline-primary">
                                <i class="fas fa-crosshairs"></i> Detectar Ubicación
                            </button>
                        </div>
                        <div id="locationStatus" class="small text-muted mt-1"></div>
                    </div>

                    <!-- Radio de búsqueda -->
                    <div class="mb-3">
                        <label for="radiusSlider" class="form-label">Radio de Búsqueda: <span id="radiusValue"><?php echo $radius; ?></span> km</label>
                        <input type="range" class="form-range" id="radiusSlider" min="1" max="20" value="<?php echo $radius; ?>">
                    </div>

                    <!-- Tipo de vehículo -->
                    <div class="mb-3">
                        <label for="vehicleTypeFilter" class="form-label">Tipo de Vehículo</label>
                        <select class="form-select" id="vehicleTypeFilter">
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
                        <label class="form-label">Mostrar en Mapa</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showGasStations" checked>
                            <label class="form-check-label" for="showGasStations">
                                Gasolineras
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showParkings" checked>
                            <label class="form-check-label" for="showParkings">
                                Parkings
                            </label>
                        </div>
                    </div>

                    <hr>

                    <!-- Leyenda -->
                    <h6><i class="fas fa-info-circle"></i> Leyenda</h6>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #007bff;"></div>
                        <small>Vehículo disponible</small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #28a745;"></div>
                        <small>Vehículo eléctrico</small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #fd7e14;"></div>
                        <small>Motocicleta</small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #dc3545;"></div>
                        <small>No disponible</small>
                    </div>
                </div>
            </div>

            <!-- Mapa -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>
                
                <!-- Información rápida -->
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h6 class="mb-1" id="totalVehicles"><?php echo count($nearbyVehicles); ?></h6>
                                    <small>Vehículos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <h6 class="mb-1" id="availableVehicles"><?php echo count(array_filter($nearbyVehicles, function($v) { return $v['disponible']; })); ?></h6>
                                    <small>Disponibles</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-info text-white">
                                <div class="card-body py-2">
                                    <h6 class="mb-1" id="nearestDistance"><?php echo !empty($nearbyVehicles) ? $nearbyVehicles[0]['distancia'] : '0'; ?> km</h6>
                                    <small>Más cercano</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de vehículos -->
            <div class="col-lg-3">
                <div class="info-panel">
                    <h5><i class="fas fa-list"></i> Vehículos Cercanos</h5>
                    <div id="vehiclesList">
                        <?php foreach ($nearbyVehicles as $vehicle): ?>
                            <div class="vehicle-card card mb-2" data-vehicle-id="<?php echo $vehicle['id']; ?>" onclick="selectVehicle(<?php echo $vehicle['id']; ?>)">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($vehicle['nombre']); ?></h6>
                                        <span class="badge distance-badge text-white"><?php echo $vehicle['distancia']; ?> km</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let userMarker;
        let vehicleMarkers = [];
        let gasStationMarkers = [];
        let parkingMarkers = [];
        let selectedVehicleId = null;
        let currentVehicles = <?php echo json_encode($nearbyVehicles); ?>;
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

        // Inicializar mapa cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>
</html>