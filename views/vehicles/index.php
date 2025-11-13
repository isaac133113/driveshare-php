<!DOCTYPE html>
<html lang="ca" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Els Meus Vehicles - DriveShare</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/modern-styles.css" rel="stylesheet">
</head>
<body class="gradient-bg" style="min-height: 100vh;">
    <!-- Navbar -->
<?php include_once __DIR__ . "/../templates/navbar.php" ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Card -->
                <div class="glass-card shadow-lg rounded-4 mb-4 fade-in">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h2 class="fw-bold mb-0 text-gradient" style="font-size: 2rem;">
                                    <i class="bi bi-car-front-fill text-primary me-2 pulse-icon"></i>Els Meus Vehicles
                                </h2>
                                <p class="text-muted mb-0 mt-2" style="font-size: 1.1rem;">Gestiona els teus vehicles registrats</p>
                            </div>
                            <button class="btn btn-primary btn-modern shadow" data-bs-toggle="modal" data-bs-target="#vehicleModal">
                                <i class="bi bi-plus-lg me-2"></i>Afegir Vehicle
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mensajes -->
                <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Lista de vehículos -->
                <div class="row g-4">
                    <?php if (!empty($userVehicles)): ?>
                        <?php foreach ($userVehicles as $index => $vehicle): ?>
                            <div class="col-md-6 col-lg-4 stagger-item">
                                <div class="modern-card route-card h-100">
                                    <!-- Imagen del vehículo -->
                                    <?php if (!empty($vehicle['images'])): ?>
                                        <img src="<?php echo htmlspecialchars('/' . $vehicle['images'][0]); ?>" 
                                             class="card-img-top rounded-top-4"
                                             alt="<?php echo htmlspecialchars($vehicle['marca_model']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="bi bi-car-front display-1 text-muted"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Botón para subir imagen -->
                                    <div class="position-absolute top-0 end-0 p-3">
                                        <button class="btn btn-light btn-sm rounded-circle shadow" 
                                                onclick="openImageUpload(<?php echo $vehicle['id']; ?>)">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>

                                    <div class="card-body p-4">
                                        <h5 class="card-title fw-bold mb-2">
                                            <?php echo htmlspecialchars($vehicle['marca_model']); ?>
                                        </h5>
                                        
                                        <!-- Etiqueta de tipo -->
                                        <span class="badge bg-primary mb-3">
                                            <?php echo htmlspecialchars($vehicle['tipus']); ?>
                                        </span>

                                        <!-- Detalles en grid -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="bg-light rounded p-2 text-center">
                                                    <small class="text-muted d-block">Places</small>
                                                    <strong><?php echo $vehicle['places']; ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-light rounded p-2 text-center">
                                                    <small class="text-muted d-block">Transmissió</small>
                                                    <strong><?php echo $vehicle['transmissio']; ?></strong>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Descripción -->
                                        <div class="col-12">
                                            <div class="bg-light rounded p-2 text-center" style="height: 80px; overflow: hidden; text-overflow: ellipsis;">
                                                <small class="text-muted d-block">Descripció</small>
                                                <span class="d-block text-truncate"><?php echo htmlspecialchars($vehicle['descripcio']); ?></span>
                                            </div>
                                        </div>

                                        <!-- Botones de acción -->
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary flex-grow-1" 
                                                    onclick="editVehicle(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteVehicle(<?php echo $vehicle['id']; ?>, '<?php echo htmlspecialchars($vehicle['marca_model']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
                                    <h4 class="text-muted mb-3">Encara no tens cap vehicle registrat</h4>
                                    <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#vehicleModal">
                                        <i class="bi bi-plus-lg me-2"></i>Afegir el Meu Primer Vehicle
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Vehículo (Crear/Editar) -->
    <div class="modal fade" id="vehicleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-car-front me-2"></i><span id="modalTitle">Nou Vehicle</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="vehicleForm" method="POST" action="../../public/index.php?controller=vehicle&action=index">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" id="vehicleId">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Marca i Model</label>
                                <input type="text" class="form-control" name="marca_model" id="marca_model" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipus</label>
                                <select class="form-select" name="tipus" id="tipus" required>
                                    <option value="">Selecciona un tipus...</option>
                                    <?php foreach ($tiposVehiculos as $key => $nombre): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Places</label>
                                <input type="number" class="form-control" name="places" id="places" min="1" max="9" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Transmissió</label>
                                <select class="form-select" name="transmissio" id="transmissio" required>
                                    <option value="">Selecciona...</option>
                                    <option value="Manual">Manual</option>
                                    <option value="Automàtic">Automàtic</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripció</label>
                                <textarea class="form-control" name="descripcio" id="descripcio" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Subir Imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-camera me-2"></i>Pujar Imatges
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../../public/index.php?controller=vehicle&action=index" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_image">
                        <input type="hidden" name="vehicle_id" id="imageVehicleId">
                        
                        <div class="mb-3">
                            <label class="form-label">Selecciona les imatges</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Pots seleccionar múltiples imatges. Formats acceptats: JPG, PNG.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i>Pujar Imatges
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirmar Eliminació
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Estàs segur que vols eliminar el vehicle <strong id="deleteVehicleName"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Aquesta acció no es pot desfer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <form id="deleteForm" method="POST" class="d-inline" action="../../public/index.php?controller=vehicle&action=index">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteVehicleId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle Dark Mode">
        <i class="bi bi-moon-stars fs-4" id="darkModeIcon"></i>
    </button>

    <!-- Bootstrap Bundle & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const html = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateDarkModeIcon(savedTheme);
        
        darkModeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateDarkModeIcon(newTheme);
        });
        
        function updateDarkModeIcon(theme) {
            if (theme === 'dark') {
                darkModeIcon.classList.remove('bi-moon-stars');
                darkModeIcon.classList.add('bi-sun-fill');
            } else {
                darkModeIcon.classList.remove('bi-sun-fill');
                darkModeIcon.classList.add('bi-moon-stars');
            }
        }
    </script>
    <script>
        // Editar vehículo
        function editVehicle(vehicle) {
            document.getElementById('modalTitle').textContent = 'Editar Vehicle';
            document.getElementById('vehicleId').value = vehicle.id;
            document.getElementById('marca_model').value = vehicle.marca_model;
            document.getElementById('tipus').value = vehicle.tipus;
            document.getElementById('places').value = vehicle.places;
            document.getElementById('transmissio').value = vehicle.transmissio;
            document.getElementById('descripcio').value = vehicle.descripcio;
            
            new bootstrap.Modal(document.getElementById('vehicleModal')).show();
        }

        // Eliminar vehículo
        function deleteVehicle(id, name) {
            document.getElementById('deleteVehicleId').value = id;
            document.getElementById('deleteVehicleName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Subir imágenes
        function openImageUpload(vehicleId) {
            document.getElementById('imageVehicleId').value = vehicleId;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Reset form al abrir modal de nuevo vehículo
        document.getElementById('vehicleModal').addEventListener('show.bs.modal', function (event) {
            if (!event.relatedTarget) return; // Si se abre para editar, no resetear
            
            document.getElementById('modalTitle').textContent = 'Nou Vehicle';
            document.getElementById('vehicleForm').reset();
            document.getElementById('vehicleId').value = '';
        });
    </script>
</body>
</html>