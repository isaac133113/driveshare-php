<!-- Els Meus Vehicles -->
<div class="card border-0 shadow-lg rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-car-front me-2"></i>Els Meus Vehicles
            </h5>
        </div>

        <div class="row g-4" id="vehiclesContainer">
            <?php if (!empty($userVehicles)): ?>
                <?php foreach ($userVehicles as $vehicle): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm vehicle-card">
                            <div class="position-relative">
                                <?php if (!empty($vehicle['images'])): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['images'][0]); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($vehicle['marca_model']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-car-front display-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo htmlspecialchars($vehicle['tipus']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">
                                    <?php echo htmlspecialchars($vehicle['marca_model']); ?>
                                </h5>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-people me-2"></i><?php echo $vehicle['places']; ?> places
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-gear me-2"></i><?php echo $vehicle['transmissio']; ?>
                                    </small>
                                    <small class="text-success fw-bold">
                                        <i class="bi bi-currency-euro me-1"></i><?php echo number_format($vehicle['preu_hora'], 2); ?>/hora
                                    </small>
                                </div>

                                <p class="card-text small text-muted">
                                    <?php echo htmlspecialchars($vehicle['descripcio']); ?>
                                </p>
                            </div>

                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="../vehicles/details.php?id=<?php echo $vehicle['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-eye me-2"></i>Veure Detalls
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Encara no tens cap vehicle registrat.
                        <a href="../vehicles/index.php" class="alert-link">Registra el teu primer vehicle</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Nova Ruta -->
<div class="card border-0 shadow-lg rounded-4 mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-map me-2"></i>Nova Ruta
        </h5>

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
                    
                    <div id="routeMap" style="height: 400px;" class="rounded-3 mb-3"></div>
                </div>

                <!-- Comentaris -->
                <div class="col-12">
                    <label class="form-label">Comentaris</label>
                    <textarea class="form-control" name="comentaris" rows="3" 
                              placeholder="Afegeix detalls addicionals sobre la ruta..."></textarea>
                </div>

                <!-- Submit -->
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Crear Ruta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Les Meves Rutes -->
<div class="card border-0 shadow-lg rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-calendar3 me-2"></i>Les Meves Rutes
        </h5>

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
                                    $badgeClass = match($ruta['estado']) {
                                        1 => 'bg-warning',   // Pendent
                                        2 => 'bg-success',   // Confirmada
                                        3 => 'bg-info',      // Completada
                                        4 => 'bg-danger',    // Cancel·lada
                                        default => 'bg-secondary'
                                    };
                                    $estados = (new HorariRutaModel())->getEstados();
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $estados[$ruta['estado']] ?? 'Desconegut'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="viewRouteDetails(<?php echo $ruta['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
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

<!-- Scripts para el mapa -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let routeMap;
let originMarker = null;
let destinationMarker = null;

// Inicializar mapa
function initRouteMap() {
    const mollerussaCoords = [41.6231, 0.8825];
    
    routeMap = L.map('routeMap').setView(mollerussaCoords, 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(routeMap);
}

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
        alert('Ubicació no trobada. Prova amb: Barcelona, Lleida, Tàrrega, Mollerussa, Balaguer');
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
    
    // Si tenemos ambos marcadores, ajustar la vista para mostrar ambos
    if (originMarker && destinationMarker) {
        const bounds = L.latLngBounds(
            [originMarker.getLatLng(), destinationMarker.getLatLng()]
        );
        routeMap.fitBounds(bounds, { padding: [50, 50] });
    } else {
        routeMap.setView([lat, lng], 13);
    }
}

// Inicializar mapa cuando la página cargue
document.addEventListener('DOMContentLoaded', function() {
    initRouteMap();
});

// Ver detalles de ruta
function viewRouteDetails(routeId) {
    // Aquí puedes implementar la lógica para mostrar los detalles
    // Por ejemplo, redirigir a una página de detalles o mostrar un modal
    alert('Ver detalles de la ruta ' + routeId);
}
</script>