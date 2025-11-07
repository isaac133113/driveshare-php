<?php
// Verificar que se accede desde el controlador
if (!isset($vehicle)) {
    // Si no hay variable $vehicle, redirigir al controlador
    $vehicleId = intval($_GET['id'] ?? 0);
    if ($vehicleId) {
        header("Location: ../../controllers/VehicleController.php?action=details&id=" . $vehicleId);
    } else {
        header("Location: ../../controllers/VehicleController.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vehicle['nombre']); ?> - DriveShare</title>
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
                <a class="nav-link" href="../../controllers/VehicleController.php">
                    <i class="bi bi-arrow-left"></i> Volver a Coches
                </a>
                <a class="nav-link" href="../../dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
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
                <!-- Breadcrumb Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="../../controllers/VehicleController.php">Ver Coches</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($vehicle['nombre']); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="row">
                    <!-- Imagen del vehículo -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-lg rounded-4 mb-4">
                            <div class="card-body p-0">
                                <img src="<?php echo $vehicle['imagen']; ?>" 
                                     class="w-100 rounded-4" 
                                     style="height: 400px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($vehicle['nombre']); ?>">
                            </div>
                        </div>

                        <!-- Descripción detallada -->
                        <div class="card border-0 shadow-lg rounded-4 mb-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Descripción</h5>
                            </div>
                            <div class="card-body p-4">
                                <p class="card-text"><?php echo htmlspecialchars($vehicle['descripcion']); ?></p>
                                
                                <h6 class="mt-4 fw-bold">Características destacadas:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Vehículo modelo <?php echo $vehicle['año']; ?></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Transmisión <?php echo $vehicle['transmision']; ?></li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Combustible: <?php echo $vehicle['combustible']; ?></li>
                                    <?php if ($vehicle['aire_acondicionado']): ?>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Aire acondicionado incluido</li>
                                    <?php endif; ?>
                                    <?php if ($vehicle['gps']): ?>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Sistema GPS navegación</li>
                                    <?php endif; ?>
                                    <?php if ($vehicle['bluetooth']): ?>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Conectividad Bluetooth</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Especificaciones técnicas -->
                        <div class="card border-0 shadow-lg rounded-4 mb-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Especificaciones Técnicas</h5>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 40%; font-weight: 600;">Marca</th>
                                            <td><?php echo htmlspecialchars($vehicle['marca']); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Modelo</th>
                                            <td><?php echo htmlspecialchars($vehicle['modelo']); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Año</th>
                                            <td><?php echo $vehicle['año']; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Tipo</th>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($vehicle['tipo']); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Combustible</th>
                                            <td><?php echo htmlspecialchars($vehicle['combustible']); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Transmisión</th>
                                            <td><?php echo htmlspecialchars($vehicle['transmision']); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Pasajeros</th>
                                            <td><?php echo $vehicle['pasajeros']; ?> personas</td>
                                        </tr>
                                        <tr>
                                            <th style="font-weight: 600;">Puertas</th>
                                            <td><?php echo $vehicle['puertas']; ?> puertas</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Panel lateral -->
                    <div class="col-lg-4">
                        <!-- Información del vehículo -->
                        <div class="card border-0 shadow-lg rounded-4 mb-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0"><?php echo htmlspecialchars($vehicle['nombre']); ?></h5>
                                <span class="badge bg-<?php echo $vehicle['disponible'] ? 'success' : 'danger'; ?>">
                                    <?php echo $vehicle['disponible'] ? 'Disponible' : 'No disponible'; ?>
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <h6 class="text-muted"><?php echo $vehicle['marca']; ?> <?php echo $vehicle['modelo']; ?> (<?php echo $vehicle['año']; ?>)</h6>
                            </div>
                        </div>

                        <!-- Precios -->
                        <div class="card border-0 shadow-lg rounded-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="card-body text-center p-4">
                                <h5 class="card-title mb-3">Precios de Alquiler</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <h3>€<?php echo number_format($vehicle['precio_hora'], 0); ?></h3>
                                        <small>por hora</small>
                                    </div>
                                    <div class="col-6">
                                        <h3>€<?php echo number_format($vehicle['precio_dia'], 0); ?></h3>
                                        <small>por día</small>
                                    </div>
                                </div>
                                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                                <small class="text-light">Precios incluyen seguro básico</small>
                            </div>
                        </div>

                        <!-- Características -->
                        <div class="card border-0 shadow-lg rounded-4 mb-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h6 class="mb-0"><i class="bi bi-star me-2"></i>Características</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded-3 bg-light" style="transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                            <i class="bi bi-people text-primary fs-4"></i>
                                            <div class="small mt-1"><?php echo $vehicle['pasajeros']; ?> pasajeros</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded-3 bg-light" style="transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                            <i class="bi bi-door-open text-primary fs-4"></i>
                                            <div class="small mt-1"><?php echo $vehicle['puertas']; ?> puertas</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded-3 bg-light" style="transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                            <i class="bi bi-gear text-primary fs-4"></i>
                                            <div class="small mt-1"><?php echo $vehicle['transmision']; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded-3 bg-light" style="transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                            <i class="bi bi-fuel-pump text-primary fs-4"></i>
                                            <div class="small mt-1"><?php echo $vehicle['combustible']; ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Extras -->
                                <div class="mt-3">
                                    <h6 class="small text-muted mb-2">EXTRAS INCLUIDOS:</h6>
                                    <div class="row g-1">
                                        <?php if ($vehicle['aire_acondicionado']): ?>
                                            <div class="col-12">
                                                <span class="badge bg-success w-100 py-2 rounded-3">
                                                    <i class="bi bi-snow me-1"></i>Aire Acondicionado
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($vehicle['gps']): ?>
                                            <div class="col-12">
                                                <span class="badge bg-info w-100 py-2 rounded-3">
                                                    <i class="bi bi-geo-alt me-1"></i>Sistema GPS
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($vehicle['bluetooth']): ?>
                                            <div class="col-12">
                                                <span class="badge bg-primary w-100 py-2 rounded-3">
                                                    <i class="bi bi-bluetooth me-1"></i>Conectividad Bluetooth
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-grid gap-2">
                            <?php if ($vehicle['disponible']): ?>
                                <button class="btn btn-primary btn-lg rounded-3" onclick="openReservaModal(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                                    <i class="bi bi-calendar-plus me-2"></i>Reservar Ahora
                                </button>
                                <a href="../../controllers/VehicleController.php" class="btn btn-outline-secondary rounded-3">
                                    <i class="bi bi-arrow-left me-2"></i>Volver a la Lista
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg rounded-3" disabled>
                                    <i class="bi bi-x-circle me-2"></i>No Disponible
                                </button>
                                <a href="../../controllers/VehicleController.php" class="btn btn-primary rounded-3">
                                    <i class="bi bi-search me-2"></i>Ver Otros Vehículos
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
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
                        <i class="bi bi-calendar-plus me-2"></i>Reservar: <span id="modal-vehicle-name"></span>
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
        let currentVehicle = <?php echo json_encode($vehicle); ?>;

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

        // Inicializar pricing
        updatePricing();
    </script>
</body>
</html>