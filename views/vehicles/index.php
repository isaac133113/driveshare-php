<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Coches - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .vehicle-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .vehicle-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .price-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .vehicle-features {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        .feature-badge {
            font-size: 0.75em;
            padding: 2px 6px;
        }
        .unavailable {
            opacity: 0.6;
            position: relative;
        }
        .unavailable::after {
            content: 'NO DISPONIBLE';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 10;
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
                <a class="nav-link" href="../../controllers/AuthController.php?action=profile">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a class="nav-link" href="../../controllers/AuthController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6"><i class="fas fa-car"></i> Ver Coches Disponibles</h1>
                <p class="lead text-muted">Explora nuestra flota de vehículos y encuentra el perfecto para tu viaje</p>
            </div>
        </div>

        <!-- Mostrar mensajes -->
        <?php if (isset($message) && !empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter"></i> Filtros</h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="tipo" class="form-label">Tipo de Vehículo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tiposVehicles as $key => $nombre): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($filtros['tipo'] === $key) ? 'selected' : ''; ?>>
                                            <?php echo $nombre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="precio_max" class="form-label">Precio máximo/día</label>
                                <select class="form-select" id="precio_max" name="precio_max">
                                    <option value="">Sin límite</option>
                                    <option value="100" <?php echo ($filtros['precio_max'] === '100') ? 'selected' : ''; ?>>Hasta €100</option>
                                    <option value="150" <?php echo ($filtros['precio_max'] === '150') ? 'selected' : ''; ?>>Hasta €150</option>
                                    <option value="200" <?php echo ($filtros['precio_max'] === '200') ? 'selected' : ''; ?>>Hasta €200</option>
                                    <option value="300" <?php echo ($filtros['precio_max'] === '300') ? 'selected' : ''; ?>>Hasta €300</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="disponible" class="form-label">Disponibilidad</label>
                                <select class="form-select" id="disponible" name="disponible">
                                    <option value="si" <?php echo ($filtros['disponible'] === 'si') ? 'selected' : ''; ?>>Solo disponibles</option>
                                    <option value="todos" <?php echo ($filtros['disponible'] === 'todos') ? 'selected' : ''; ?>>Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="?" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de vehículos -->
        <div class="row">
            <?php if (!empty($vehicles)): ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card vehicle-card <?php echo !$vehicle['disponible'] ? 'unavailable' : ''; ?>">
                            <div class="position-relative">
                                <img src="<?php echo $vehicle['imagen']; ?>" 
                                     class="card-img-top vehicle-image" 
                                     alt="<?php echo htmlspecialchars($vehicle['nombre']); ?>">
                                <div class="price-badge">
                                    €<?php echo number_format($vehicle['precio_dia'], 0); ?>/día
                                </div>
                                <?php if (!$vehicle['disponible']): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5);">
                                        <span class="badge bg-danger fs-6">NO DISPONIBLE</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['nombre']); ?></h5>
                                <p class="text-muted mb-2">
                                    <small><?php echo $vehicle['marca']; ?> • <?php echo $vehicle['año']; ?> • <?php echo $vehicle['combustible']; ?></small>
                                </p>
                                <p class="card-text text-truncate"><?php echo htmlspecialchars($vehicle['descripcion']); ?></p>
                                
                                <!-- Características del vehículo -->
                                <div class="vehicle-features mb-3">
                                    <span class="badge bg-secondary feature-badge">
                                        <i class="fas fa-users"></i> <?php echo $vehicle['pasajeros']; ?> pasajeros
                                    </span>
                                    <span class="badge bg-secondary feature-badge">
                                        <i class="fas fa-door-open"></i> <?php echo $vehicle['puertas']; ?> puertas
                                    </span>
                                    <span class="badge bg-secondary feature-badge">
                                        <i class="fas fa-cog"></i> <?php echo $vehicle['transmision']; ?>
                                    </span>
                                    <?php if ($vehicle['aire_acondicionado']): ?>
                                        <span class="badge bg-info feature-badge">
                                            <i class="fas fa-snowflake"></i> A/C
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($vehicle['gps']): ?>
                                        <span class="badge bg-success feature-badge">
                                            <i class="fas fa-map-marked-alt"></i> GPS
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($vehicle['bluetooth']): ?>
                                        <span class="badge bg-primary feature-badge">
                                            <i class="fab fa-bluetooth"></i> Bluetooth
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Precios -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <small class="text-muted">€<?php echo number_format($vehicle['precio_hora'], 0); ?>/hora</small><br>
                                        <strong class="text-primary">€<?php echo number_format($vehicle['precio_dia'], 0); ?>/día</strong>
                                    </div>
                                    <span class="badge bg-<?php echo $vehicle['disponible'] ? 'success' : 'danger'; ?>">
                                        <?php echo $vehicle['disponible'] ? 'Disponible' : 'No disponible'; ?>
                                    </span>
                                </div>
                                
                                <!-- Botones -->
                                <div class="d-grid gap-2">
                                    <?php if ($vehicle['disponible']): ?>
                                        <button class="btn btn-primary" onclick="openReservaModal(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                                            <i class="fas fa-calendar-plus"></i> Reservar Ahora
                                        </button>
                                        <a href="?action=details&id=<?php echo $vehicle['id']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-info-circle"></i> Ver Detalles
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-ban"></i> No Disponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-car fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No se encontraron vehículos</h4>
                        <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        <a href="?" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> Ver Todos los Vehículos
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Reserva -->
    <div class="modal fade" id="reservaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus"></i> Reservar Vehículo: <span id="modal-vehicle-name"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reservar">
                        <input type="hidden" name="vehicle_id" id="modal-vehicle-id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo_renta" class="form-label">Tipo de Renta</label>
                                <select class="form-select" id="tipo_renta" name="tipo_renta" onchange="updatePricing()" required>
                                    <option value="dias">Por Días</option>
                                    <option value="horas">Por Horas</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" onchange="updatePricing()" required>
                            </div>
                        </div>
                        
                        <!-- Resumen de precio -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Resumen de Reserva</h6>
                                <div id="pricing-summary">
                                    <p class="mb-1">Precio por <span id="precio-tipo">día</span>: €<span id="precio-unitario">0</span></p>
                                    <p class="mb-1">Cantidad: <span id="cantidad-display">1</span> <span id="cantidad-tipo">día(s)</span></p>
                                    <hr>
                                    <h6 class="mb-0">Total: €<span id="precio-total">0</span></h6>
                                </div>
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