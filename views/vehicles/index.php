<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Coches - DriveShare</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../../dashboard.php">
                <i class="bi bi-car-front-fill"></i> DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link" href="../../controllers/AuthController.php?action=profile">
                    <i class="bi bi-person"></i> Perfil
                </a>
                <a class="nav-link" href="../../controllers/AuthController.php?action=logout">
                    <i class="bi bi-box-arrow-right"></i> Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-car-front-fill text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Ver Coches Disponibles</h2>
                            <p class="text-muted">Explora nuestra flota de vehículos y encuentra el perfecto para tu viaje</p>
                        </div>
                    </div>
                </div>

                <!-- Mostrar mensajes -->
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filtros Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-header bg-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="tipo" class="form-label fw-bold">Tipo de Vehículo</label>
                                <select class="form-select rounded-3" id="tipo" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tiposVehicles as $key => $nombre): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($filtros['tipo'] === $key) ? 'selected' : ''; ?>>
                                            <?php echo $nombre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="precio_max" class="form-label fw-bold">Precio máximo/día</label>
                                <select class="form-select rounded-3" id="precio_max" name="precio_max">
                                    <option value="">Sin límite</option>
                                    <option value="100" <?php echo ($filtros['precio_max'] === '100') ? 'selected' : ''; ?>>Hasta €100</option>
                                    <option value="150" <?php echo ($filtros['precio_max'] === '150') ? 'selected' : ''; ?>>Hasta €150</option>
                                    <option value="200" <?php echo ($filtros['precio_max'] === '200') ? 'selected' : ''; ?>>Hasta €200</option>
                                    <option value="300" <?php echo ($filtros['precio_max'] === '300') ? 'selected' : ''; ?>>Hasta €300</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="disponible" class="form-label fw-bold">Disponibilidad</label>
                                <select class="form-select rounded-3" id="disponible" name="disponible">
                                    <option value="si" <?php echo ($filtros['disponible'] === 'si') ? 'selected' : ''; ?>>Solo disponibles</option>
                                    <option value="todos" <?php echo ($filtros['disponible'] === 'todos') ? 'selected' : ''; ?>>Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary rounded-3">
                                        <i class="bi bi-search me-2"></i>Buscar
                                    </button>
                                    <a href="?" class="btn btn-outline-secondary rounded-3">
                                        <i class="bi bi-x-lg me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de vehículos -->
                <div class="row">
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-lg rounded-4 h-100 <?php echo !$vehicle['disponible'] ? 'opacity-75' : ''; ?>" style="transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">
                                    <div class="position-relative">
                                        <img src="<?php echo $vehicle['imagen']; ?>" 
                                             class="card-img-top rounded-top-4" 
                                             style="height: 200px; object-fit: cover;"
                                             alt="<?php echo htmlspecialchars($vehicle['nombre']); ?>">
                                        <div class="position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-2">
                                                €<?php echo number_format($vehicle['precio_dia'], 0); ?>/día
                                            </span>
                                        </div>
                                        <?php if (!$vehicle['disponible']): ?>
                                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-top-4" style="background: rgba(0,0,0,0.6);">
                                                <span class="badge bg-danger fs-6 px-4 py-2">NO DISPONIBLE</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-body p-4">
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($vehicle['nombre']); ?></h5>
                                        <p class="text-muted mb-2">
                                            <small><i class="bi bi-tag me-1"></i><?php echo $vehicle['marca']; ?> • <?php echo $vehicle['año']; ?> • <?php echo $vehicle['combustible']; ?></small>
                                        </p>
                                        <p class="card-text text-truncate"><?php echo htmlspecialchars($vehicle['descripcion']); ?></p>
                                        
                                        <!-- Características del vehículo -->
                                        <div class="mb-3">
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <span class="badge bg-light text-dark w-100 py-2 rounded-3">
                                                        <i class="bi bi-people me-1"></i><?php echo $vehicle['pasajeros']; ?> pasajeros
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <span class="badge bg-light text-dark w-100 py-2 rounded-3">
                                                        <i class="bi bi-door-open me-1"></i><?php echo $vehicle['puertas']; ?> puertas
                                                    </span>
                                                </div>
                                                <div class="col-12">
                                                    <span class="badge bg-light text-dark w-100 py-2 rounded-3">
                                                        <i class="bi bi-gear me-1"></i><?php echo $vehicle['transmision']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Extras -->
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($vehicle['aire_acondicionado']): ?>
                                                    <span class="badge bg-info rounded-pill">
                                                        <i class="bi bi-snow me-1"></i>A/C
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($vehicle['gps']): ?>
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="bi bi-geo-alt me-1"></i>GPS
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($vehicle['bluetooth']): ?>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <i class="bi bi-bluetooth me-1"></i>Bluetooth
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Precios -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <small class="text-muted">€<?php echo number_format($vehicle['precio_hora'], 0); ?>/hora</small><br>
                                                <strong class="text-primary fs-5">€<?php echo number_format($vehicle['precio_dia'], 0); ?>/día</strong>
                                            </div>
                                            <span class="badge bg-<?php echo $vehicle['disponible'] ? 'success' : 'danger'; ?> rounded-pill px-3 py-2">
                                                <?php echo $vehicle['disponible'] ? 'Disponible' : 'No disponible'; ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Botones -->
                                        <div class="d-grid gap-2">
                                            <?php if ($vehicle['disponible']): ?>
                                                <button class="btn btn-primary rounded-3" onclick="openReservaModal(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                                                    <i class="bi bi-calendar-plus me-2"></i>Reservar Ahora
                                                </button>
                                                <a href="?action=details&id=<?php echo $vehicle['id']; ?>" class="btn btn-outline-primary rounded-3">
                                                    <i class="bi bi-info-circle me-2"></i>Ver Detalles
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary rounded-3" disabled>
                                                    <i class="bi bi-x-circle me-2"></i>No Disponible
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-lg rounded-4">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-car-front text-muted display-1 mb-3"></i>
                                    <h4 class="text-muted fw-bold">No se encontraron vehículos</h4>
                                    <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                                    <a href="?" class="btn btn-primary rounded-3">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Ver Todos los Vehículos
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reserva -->
    <div class="modal fade" id="reservaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>Reservar Vehículo: <span id="modal-vehicle-name"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="reservar">
                        <input type="hidden" name="vehicle_id" id="modal-vehicle-id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_inicio" class="form-label fw-bold">Fecha de Inicio</label>
                                <input type="datetime-local" class="form-control rounded-3" id="fecha_inicio" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_fin" class="form-label fw-bold">Fecha de Fin</label>
                                <input type="datetime-local" class="form-control rounded-3" id="fecha_fin" name="fecha_fin" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo_renta" class="form-label fw-bold">Tipo de Renta</label>
                                <select class="form-select rounded-3" id="tipo_renta" name="tipo_renta" onchange="updatePricing()" required>
                                    <option value="dias">Por Días</option>
                                    <option value="horas">Por Horas</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="cantidad" class="form-label fw-bold">Cantidad</label>
                                <input type="number" class="form-control rounded-3" id="cantidad" name="cantidad" min="1" value="1" onchange="updatePricing()" required>
                            </div>
                        </div>
                        
                        <!-- Resumen de precio -->
                        <div class="card bg-light border-0 rounded-3">
                            <div class="card-body">
                                <h6 class="card-title fw-bold">Resumen de Reserva</h6>
                                <div id="pricing-summary">
                                    <p class="mb-1">Precio por <span id="precio-tipo">día</span>: €<span id="precio-unitario">0</span></p>
                                    <p class="mb-1">Cantidad: <span id="cantidad-display">1</span> <span id="cantidad-tipo">día(s)</span></p>
                                    <hr>
                                    <h6 class="mb-0 fw-bold">Total: €<span id="precio-total">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="bi bi-check-lg me-2"></i>Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentVehicle = null;

        function openReservaModal(vehicle) {
            currentVehicle = vehicle;
            
            document.getElementById('modal-vehicle-name').textContent = vehicle.nombre;
            document.getElementById('modal-vehicle-id').value = vehicle.id;
            
            // Establecer fecha mínima como ahora
            const now = new Date();
            const minDateTime = now.toISOString().slice(0, 16);
            document.getElementById('fecha_inicio').min = minDateTime;
            document.getElementById('fecha_fin').min = minDateTime;
            
            updatePricing();
            
            new bootstrap.Modal(document.getElementById('reservaModal')).show();
        }

        function updatePricing() {
            if (!currentVehicle) return;
            
            const tipoRenta = document.getElementById('tipo_renta').value;
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            
            const precioUnitario = tipoRenta === 'dias' ? currentVehicle.precio_dia : currentVehicle.precio_hora;
            const total = precioUnitario * cantidad;
            
            document.getElementById('precio-tipo').textContent = tipoRenta === 'dias' ? 'día' : 'hora';
            document.getElementById('precio-unitario').textContent = precioUnitario.toFixed(2);
            document.getElementById('cantidad-display').textContent = cantidad;
            document.getElementById('cantidad-tipo').textContent = tipoRenta === 'dias' ? 'día(s)' : 'hora(s)';
            document.getElementById('precio-total').textContent = total.toFixed(2);
        }

        // Auto-calcular cantidad basada en fechas
        document.getElementById('fecha_inicio').addEventListener('change', calculateDuration);
        document.getElementById('fecha_fin').addEventListener('change', calculateDuration);

        function calculateDuration() {
            const inicio = new Date(document.getElementById('fecha_inicio').value);
            const fin = new Date(document.getElementById('fecha_fin').value);
            
            if (inicio && fin && fin > inicio) {
                const tipoRenta = document.getElementById('tipo_renta').value;
                const diffMs = fin - inicio;
                
                let cantidad;
                if (tipoRenta === 'dias') {
                    cantidad = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
                } else {
                    cantidad = Math.ceil(diffMs / (1000 * 60 * 60));
                }
                
                document.getElementById('cantidad').value = cantidad;
                updatePricing();
            }
        }
    </script>
</body>
</html>