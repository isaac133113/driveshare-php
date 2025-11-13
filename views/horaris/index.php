<!DOCTYPE html>
<html lang="ca" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horaris - DriveShare</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/modern-styles.css" rel="stylesheet">
</head>
<body class="gradient-bg" style="min-height: 100vh;">
    <?php include '../templates/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Card -->
                <div class="glass-card shadow-lg rounded-4 mb-4 fade-in">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h2 class="fw-bold mb-0 text-gradient" style="font-size: 2rem;">
                                    <i class="bi bi-calendar-event text-primary me-2 pulse-icon"></i>Gestió d'Horaris
                                </h2>
                                <p class="text-muted mb-0 mt-2" style="font-size: 1.1rem;">Organitza els teus viatges i rutes</p>
                            </div>
                            <button class="btn btn-primary btn-modern shadow" data-bs-toggle="modal" data-bs-target="#addHorariModal">
                                <i class="bi bi-plus-lg me-2"></i>Nou Horari
                            </button>
                        </div>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="filterDate" class="form-label">Data</label>
                                <input type="date" class="form-control" id="filterDate" name="date" 
                                       value="<?php echo $_GET['date'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filterOrigin" class="form-label">Origen</label>
                                <input type="text" class="form-control" id="filterOrigin" name="origin" 
                                       placeholder="Ciutat d'origen" value="<?php echo $_GET['origin'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filterDestination" class="form-label">Destí</label>
                                <input type="text" class="form-control" id="filterDestination" name="destination" 
                                       placeholder="Ciutat de destí" value="<?php echo $_GET['destination'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filterVehicle" class="form-label">Vehicle</label>
                                <select class="form-select" id="filterVehicle" name="vehicle">
                                    <option value="">Tots els vehicles</option>
                                    <option value="cotxe" <?php echo ($_GET['vehicle'] ?? '') === 'cotxe' ? 'selected' : ''; ?>>Cotxe</option>
                                    <option value="moto" <?php echo ($_GET['vehicle'] ?? '') === 'moto' ? 'selected' : ''; ?>>Moto</option>
                                    <option value="bicicleta" <?php echo ($_GET['vehicle'] ?? '') === 'bicicleta' ? 'selected' : ''; ?>>Bicicleta</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Netejar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de horarios -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Horaris Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($horaris)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Ruta</th>
                                            <th>Vehicle</th>
                                            <th>Conductor</th>
                                            <th>Places</th>
                                            <th>Estat</th>
                                            <th>Accions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($horaris as $horari): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($horari['data_ruta'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($horari['hora_sortida'])); ?></td>
                                                <td>
                                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                                    <?php echo htmlspecialchars($horari['origen']); ?>
                                                    <i class="fas fa-arrow-right mx-1"></i>
                                                    <i class="fas fa-map-marker-alt text-success"></i>
                                                    <?php echo htmlspecialchars($horari['desti']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($horari['vehicle']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($horari['conductor_nom']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $horari['places_disponibles'] > 0 ? 'success' : 'danger'; ?>">
                                                        <?php echo $horari['places_disponibles']; ?>/<?php echo $horari['places_totals']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($horari['estat'] === 'actiu'): ?>
                                                        <span class="badge bg-success">Actiu</span>
                                                    <?php elseif ($horari['estat'] === 'complet'): ?>
                                                        <span class="badge bg-warning">Complet</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Cancel·lat</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($horari['estat'] === 'actiu' && $horari['places_disponibles'] > 0): ?>
                                                        <button class="btn btn-sm btn-primary" onclick="joinHorari(<?php echo $horari['id']; ?>)">
                                                            <i class="fas fa-plus"></i> Unir-se
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($horari['user_id'] == $_SESSION['user_id']): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="editHorari(<?php echo $horari['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteHorari(<?php echo $horari['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hi ha horaris disponibles</h5>
                                <p class="text-muted">Sigues el primer en crear un horari!</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHorariModal">
                                    <i class="fas fa-plus"></i> Crear Horari
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Horario -->
    <div class="modal fade" id="addHorariModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Nou Horari</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_ruta" class="form-label">Data de la Ruta</label>
                                <input type="date" class="form-control" id="data_ruta" name="data_ruta" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_sortida" class="form-label">Hora de Sortida</label>
                                <input type="time" class="form-control" id="hora_sortida" name="hora_sortida" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="origen" class="form-label">Origen</label>
                                <input type="text" class="form-control" id="origen" name="origen" 
                                       placeholder="Ciutat d'origen" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="desti" class="form-label">Destí</label>
                                <input type="text" class="form-control" id="desti" name="desti" 
                                       placeholder="Ciutat de destí" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle" class="form-label">Vehicle</label>
                                <select class="form-select" id="vehicle" name="vehicle" required>
                                    <option value="">Selecciona un vehicle</option>
                                    <option value="cotxe">Cotxe</option>
                                    <option value="moto">Moto</option>
                                    <option value="bicicleta">Bicicleta</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="places_totals" class="form-label">Places Totals</label>
                                <input type="number" class="form-control" id="places_totals" name="places_totals" 
                                       min="1" max="8" value="4" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observacions" class="form-label">Observacions</label>
                            <textarea class="form-control" id="observacions" name="observacions" rows="3" 
                                      placeholder="Comentaris adicionals sobre el viatge..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Horari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle Dark Mode">
        <i class="bi bi-moon-stars fs-4" id="darkModeIcon"></i>
    </button>

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

        function joinHorari(id) {
            if (confirm('Vols unir-te a aquest horari?')) {
                // Implementar lógica de unirse al horario
                window.location.href = `?action=join&id=${id}`;
            }
        }

        function editHorari(id) {
            // Implementar lógica de edición
            window.location.href = `?action=edit&id=${id}`;
        }

        function deleteHorari(id) {
            if (confirm('Estàs segur que vols eliminar aquest horari?')) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }

        // Establecer fecha mínima a hoy
        document.getElementById('data_ruta').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>