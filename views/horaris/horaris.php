<?php

// Nota: toda la lógica (conexión a BD, CRUD) fue movida al controlador `controllers/HorariController.php`.
// Esta vista espera que el controlador suministre las variables:
// - $message (string), $messageType (string), $editingHorari (array|null),
// - $myHoraris (array), $allHoraris (mysqli_result|array)
// Para permitir acceso directo (por compatibilidad), establecemos valores por defecto seguros.

$message = $message ?? '';
$messageType = $messageType ?? '';
$editingHorari = $editingHorari ?? null;
$myHoraris = $myHoraris ?? [];
$allHoraris = $allHoraris ?? [];
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
    <style>
        .nav-tabs .nav-link {
            color: #000 !important;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd !important;
        }
        .map-container {
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .map-input-group {
            position: relative;
        }
        .location-display {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .filter-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-toggle:hover {
            background-color: rgba(0,0,0,0.05);
        }
        .no-results {
            text-align: center;
            padding: 3rem;
            opacity: 0.7;
        }
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            .filter-col {
                margin-bottom: 1rem;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
            .btn-group {
                flex-direction: column;
            }
        }
        
        /* Estilos para panel de filtros */
        .filter-panel {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-panel .form-control, 
        .filter-panel .form-select {
            background-color: white;
        }
        
        .results-info {
            background-color: #e9ecef;
            border-radius: 6px;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        /* ESTILOS PERSONALIZADOS PARA DATATABLES */
        .dataTables_wrapper {
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dataTables_length label,
        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .dataTables_length select {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            min-width: 80px;
        }
        
        .dataTables_length select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        
        .dataTables_filter input {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            min-width: 250px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .dataTables_filter input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25), inset 0 2px 4px rgba(0,0,0,0.05);
            outline: none;
            transform: translateY(-1px);
        }
        
        .dataTables_filter input::placeholder {
            color: #adb5bd;
            font-style: italic;
        }
        
        /* Mejorar la tabla */
        .dataTable {
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .dataTable thead th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.8rem;
            border: none;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .dataTable thead th:first-child {
            border-top-left-radius: 12px;
        }
        
        .dataTable thead th:last-child {
            border-top-right-radius: 12px;
        }
        
        .dataTable thead th.sorting,
        .dataTable thead th.sorting_asc,
        .dataTable thead th.sorting_desc {
            cursor: pointer;
        }
        
        .dataTable thead th.sorting:hover,
        .dataTable thead th.sorting_asc:hover,
        .dataTable thead th.sorting_desc:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a0);
            transform: translateY(-1px);
        }
        
        .dataTable tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .dataTable tbody tr:hover {
            background: linear-gradient(135deg, #667eea10, #764ba210);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .dataTable tbody td {
            padding: 1rem 0.8rem;
            vertical-align: middle;
            border: none;
        }
        
        /* Info y paginación */
        .dataTables_info {
            background: rgba(102, 126, 234, 0.1);
            padding: 0.8rem 1.2rem;
            border-radius: 20px;
            font-weight: 500;
            color: #495057;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .dataTables_paginate {
            margin-top: 1rem;
        }
        
        .dataTables_paginate .paginate_button {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            color: #495057;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .dataTables_paginate .paginate_button.disabled:hover {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            color: #495057;
            border-color: #e9ecef;
            transform: none;
            box-shadow: none;
        }
        
        /* Responsive en móviles */
        @media (max-width: 768px) {
            .dataTables_wrapper {
                padding: 1rem;
            }
            
            .dataTables_filter input {
                min-width: 200px;
            }
            
            .dataTables_length,
            .dataTables_filter {
                text-align: center;
                margin-bottom: 1rem;
            }
            
            .dataTables_info,
            .dataTables_paginate {
                text-align: center;
                margin-top: 1rem;
            }
        }
        
        /* Animaciones para las filas */
        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mejoras para badges y botones */
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.8rem;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .btn-group .btn {
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.3s ease;
        }
        
        .btn-group .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-car-front me-2"></i>DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="horaris.php">
                    <i class="bi bi-calendar-week me-1"></i>Horari
                </a>
                <a class="nav-link" href="controllers/AuthController.php?action=logout">
                    <i class="bi bi-box-arrow-right me-1"></i>Sortir
                </a>
            </div>
        </div>
    </nav>

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
                                    Gestió d'Horaris i Rutes
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
                    <i class="bi bi-person-circle me-2"></i>Els meus Horaris
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-horaris-tab" data-bs-toggle="tab" data-bs-target="#all-horaris" type="button">
                    <i class="bi bi-people me-2"></i>Tots els Horaris
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
                        <div class="col-md-3 filter-col">
                            <label class="form-label small fw-semibold text-muted">📅 Data</label>
                            <input type="date" class="form-control form-control-sm" id="filterDate" placeholder="Selecciona data">
                        </div>
                        <div class="col-md-3 filter-col">
                            <label class="form-label small fw-semibold text-muted">🚗 Vehicle</label>
                            <select class="form-select form-select-sm" id="filterVehicle">
                                <option value="">Tots els vehicles</option>
                                <option value="Seat Ibiza">Seat Ibiza</option>
                                <option value="Ford Focus">Ford Focus</option>
                                <option value="Tesla Model 3">Tesla Model 3</option>
                                <option value="BMW X5">BMW X5</option>
                            </select>
                        </div>
                        <div class="col-md-3 filter-col">
                            <label class="form-label small fw-semibold text-muted">📍 Ubicació</label>
                            <input type="text" class="form-control form-control-sm" id="filterLocation" placeholder="Origen o destí">
                        </div>
                        <div class="col-md-3 filter-col">
                            <label class="form-label small fw-semibold text-muted">👤 Usuari</label>
                            <input type="text" class="form-control form-control-sm" id="filterUser" placeholder="Nom usuari">
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
            <!-- Els meus Horaris -->
            <div class="tab-pane fade show active" id="my-horaris" role="tabpanel">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check me-2"></i>Els teus Horaris
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
                                                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm"
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

            <!-- Tots els Horaris -->
            <div class="tab-pane fade" id="all-horaris" role="tabpanel">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-globe me-2"></i>Tots els Horaris de la Comunitat
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="allHorarisTable" class="table table-hover mb-0 datatable-elegant">
                                <thead class="table-light">
                                    <tr>
                                        <th>Usuari</th>
                                        <th>Data</th>
                                        <th>Horari</th>
                                        <th>Ruta</th>
                                        <th>Vehicle</th>
                                        <th>Comentaris</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Soportar resultados como mysqli_result o como array proporcionado por el controlador
                                    if ($allHoraris instanceof mysqli_result) {
                                        while ($row = $allHoraris->fetch_assoc()): ?>
                                            <tr class="horari-row <?php echo ($row['user_id'] == $_SESSION['user_id']) ? 'table-primary' : ''; ?>"
                                                data-date="<?php echo $row['data_ruta']; ?>"
                                                data-vehicle="<?php echo htmlspecialchars($row['vehicle']); ?>"
                                                data-origen="<?php echo htmlspecialchars($row['origen']); ?>"
                                                data-desti="<?php echo htmlspecialchars($row['desti']); ?>"
                                                data-user="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['cognoms']); ?>"
                                                data-tab="all">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle text-primary me-2"></i>
                                                        <span><?php echo htmlspecialchars($row['nom'] . ' ' . $row['cognoms']); ?></span>
                                                        <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-primary ms-2">Tu</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
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
                                            </tr>
                                        <?php endwhile;
                                    } elseif (is_array($allHoraris)) {
                                        foreach ($allHoraris as $row): ?>
                                            <tr class="horari-row <?php echo ($row['user_id'] == $_SESSION['user_id']) ? 'table-primary' : ''; ?>"
                                                data-date="<?php echo $row['data_ruta']; ?>"
                                                data-vehicle="<?php echo htmlspecialchars($row['vehicle']); ?>"
                                                data-origen="<?php echo htmlspecialchars($row['origen']); ?>"
                                                data-desti="<?php echo htmlspecialchars($row['desti']); ?>"
                                                data-user="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['cognoms']); ?>"
                                                data-tab="all">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle text-primary me-2"></i>
                                                        <span><?php echo htmlspecialchars($row['nom'] . ' ' . $row['cognoms']); ?></span>
                                                        <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-primary ms-2">Tu</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
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
                                            </tr>
                                        <?php endforeach;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
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
                <form method="post">
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
                                <option value="Seat Ibiza" <?php echo ($editingHorari && $editingHorari['vehicle'] == 'Seat Ibiza') ? 'selected' : ''; ?>>Seat Ibiza</option>
                                <option value="Ford Focus" <?php echo ($editingHorari && $editingHorari['vehicle'] == 'Ford Focus') ? 'selected' : ''; ?>>Ford Focus</option>
                                <option value="Tesla Model 3" <?php echo ($editingHorari && $editingHorari['vehicle'] == 'Tesla Model 3') ? 'selected' : ''; ?>>Tesla Model 3</option>
                                <option value="BMW X5" <?php echo ($editingHorari && $editingHorari['vehicle'] == 'BMW X5') ? 'selected' : ''; ?>>BMW X5</option>
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
            
            // Inicializar DataTable para Tots els Horaris
            initAllHorarisTable();
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
                    search: "🔍 Buscar en els teus horaris:",
                    lengthMenu: "📄 Mostrar _MENU_ horaris per pàgina",
                    info: "📊 Mostrant _START_ a _END_ de _TOTAL_ horaris",
                    infoEmpty: "🚧 No hi ha horaris per mostrar",
                    infoFiltered: "(filtrat de _MAX_ horaris totals)",
                    zeroRecords: "🔍 No s'han trobat horaris que coincideixin",
                    emptyTable: "📅 No tens cap horari creat encara",
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
                    console.log('✅ DataTable inicializada correctamente para Els meus Horaris');
                    
                    // Personalizar controles
                    $('#myHorarisTable_wrapper .dataTables_filter input').attr('placeholder', '🔍 Buscar els meus horaris...');
                    $('#myHorarisTable_wrapper .dataTables_length label').prepend('<i class="bi bi-list-ul me-2 text-primary"></i>');
                    $('#myHorarisTable_wrapper .dataTables_filter label').prepend('<i class="bi bi-search me-2 text-primary"></i>');
                }
            });
            
            return dataTable;
        }
        
        function initAllHorarisTable() {
            const table = $('#allHorarisTable');
            if (table.length === 0) {
                console.log('❌ Tabla allHorarisTable no encontrada');
                return;
            }
            
            // Configuración de DataTables para Tots els Horaris
            const dataTable = table.DataTable({
                // Idioma en catalán/español
                language: {
                    search: "🌍 Buscar en tots els horaris:",
                    lengthMenu: "📄 Mostrar _MENU_ horaris per pàgina",
                    info: "📊 Mostrant _START_ a _END_ de _TOTAL_ horaris de la comunitat",
                    infoEmpty: "🚧 No hi ha horaris per mostrar",
                    infoFiltered: "(filtrat de _MAX_ horaris totals)",
                    zeroRecords: "🔍 No s'han trobat horaris que coincideixin",
                    emptyTable: "🌍 No hi ha horaris de la comunitat",
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
                lengthMenu: [[5, 10, 15, 25, 50, -1], [5, 10, 15, 25, 50, "🌍 Tots"]],
                
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
                        targets: [0], // Usuari
                        orderable: true,
                        className: 'text-center'
                    },
                    {
                        targets: [1], // Data
                        orderable: true,
                        type: 'date'
                    },
                    {
                        targets: [2, 3, 4, 5], // Horari, Ruta, Vehicle, Comentaris
                        orderable: true
                    }
                ],
                
                // Ordenación inicial por fecha descendente
                order: [[1, 'desc']],
                
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
                    console.log('✅ DataTable inicializada correctamente para Tots els Horaris');
                    
                    // Personalizar controles
                    $('#allHorarisTable_wrapper .dataTables_filter input').attr('placeholder', '🌍 Buscar tots els horaris...');
                    $('#allHorarisTable_wrapper .dataTables_length label').prepend('<i class="bi bi-people me-2 text-success"></i>');
                    $('#allHorarisTable_wrapper .dataTables_filter label').prepend('<i class="bi bi-globe me-2 text-success"></i>');
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
            const filterUser = document.getElementById('filterUser');
            const clearFiltersBtn = document.getElementById('clearFilters');
            const autoFilterCheck = document.getElementById('autoFilter');
            const resultsCount = document.getElementById('resultsCount');

            // Event listeners para filtros automáticos
            [filterDate, filterVehicle, filterLocation, filterUser].forEach(filter => {
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
                filterUser.value = '';
                applyDataTableFilters();
            });

            // Aplicar filtros iniciales
            setTimeout(() => applyDataTableFilters(), 500);
        }

        function applyDataTableFilters() {
            const filterDate = document.getElementById('filterDate').value;
            const filterVehicle = document.getElementById('filterVehicle').value;
            const filterLocation = document.getElementById('filterLocation').value;
            const filterUser = document.getElementById('filterUser').value;
            
            // Obtener tab activo
            const activeTab = document.querySelector('.nav-link.active').id;
            const isMyHoraris = activeTab === 'my-horaris-tab';
            
            // Obtener la tabla DataTable correspondiente
            let table;
            if (isMyHoraris) {
                table = $('#myHorarisTable').DataTable();
            } else {
                table = $('#allHorarisTable').DataTable();
            }
            
            if (!table) return;
            
            // Limpiar filtros previos
            table.columns().search('').draw();
            
            // Aplicar filtros por columna
            if (filterDate) {
                table.column(isMyHoraris ? 0 : 1).search(filterDate);
            }
            
            if (filterVehicle) {
                table.column(isMyHoraris ? 3 : 4).search(filterVehicle);
            }
            
            if (filterLocation) {
                table.column(isMyHoraris ? 2 : 3).search(filterLocation);
            }
            
            if (filterUser && !isMyHoraris) {
                table.column(0).search(filterUser);
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
