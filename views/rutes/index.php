<!DOCTYPE html>
<html lang="ca" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veure Rutes - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/modern-styles.css" rel="stylesheet">
</head>
<body class="gradient-bg" style="min-height: 100vh;">

<?php include_once __DIR__ . "/../templates/navbar.php" ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Mensaje de sesión -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['messageType'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $_SESSION['messageType'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['messageType']); ?>
            <?php endif; ?>

            <div class="glass-card shadow-lg rounded-4 mb-4 fade-in">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="fw-bold mb-0 text-gradient" style="font-size: 2rem;">
                                <i class="bi bi-map text-warning me-2 pulse-icon"></i>Veure Rutes
                            </h2>
                            <p class="text-muted mb-0 mt-2" style="font-size: 1.1rem;">Explora i reserva rutes d'altres usuaris</p>
                        </div>
                        <a href="../../public/index.php?controller=horaris&action=index" class="btn btn-success btn-modern shadow">
                            <i class="bi bi-calendar-plus me-2"></i>Les Meves Reserves
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulario de filtros -->
            <div class="glass-card shadow-lg rounded-4 mb-4 p-4 fade-in" style="animation-delay: 0.1s;">
                <form method="GET" action="index.php">
                    <input type="hidden" name="controller" value="rutes">
                    <input type="hidden" name="action" value="index">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="origen" class="form-control modern-input" placeholder="🚩 Origen" value="<?= htmlspecialchars($_GET['origen'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="desti" class="form-control modern-input" placeholder="📍 Destí" value="<?= htmlspecialchars($_GET['desti'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input 
                                type="date" 
                                name="data_ruta" 
                                class="form-control modern-input" 
                                value="<?= htmlspecialchars($_GET['data_ruta'] ?? '') ?>"
                            >
                        </div>
                        <div class="col-md-2">
                            <select name="tipus" class="form-select modern-input">
                                <option value="">🚗 Tipus vehicle</option>
                                <?php foreach ($tipos as $key => $label): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" <?= ($_GET['tipus'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="number" 
                                name="min_precio" 
                                class="form-control modern-input" 
                                placeholder="Min €" 
                                min="0" 
                                step="5" 
                                value="<?= htmlspecialchars($_GET['min_precio'] ?? '') ?>">
                        </div>

                        <div class="col-md-1">
                            <input type="number" 
                                name="max_precio" 
                                class="form-control modern-input" 
                                placeholder="Max €" 
                                min="0" 
                                step="5" 
                                value="<?= htmlspecialchars($_GET['max_precio'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-warning btn-modern shadow">
                            <i class="bi bi-funnel me-2"></i>Filtrar
                        </button>
                        <a href="../../public/index.php?controller=rutes&action=index" class="btn btn-secondary btn-modern shadow ms-2">
                            <i class="bi bi-x-circle me-2"></i>Netejar
                        </a>
                    </div>
                </form>
            </div>

            <!-- DEBUG INFO -->
            <?php 
            error_log("DEBUG Vista Rutes: Nombre de rutes: " . count($rutes));
            if (empty($rutes)) {
                error_log("DEBUG Vista Rutes: Array de rutes està buit!");
            } else {
                error_log("DEBUG Vista Rutes: Primera ruta: " . print_r($rutes[0], true));
            }
            ?>
            
            <div class="row g-4">
                <?php if (!empty($rutes)): ?>
                    <?php foreach ($rutes as $index => $ruta): ?>
                        <div class="col-md-6 col-lg-4 stagger-item">
                            <div class="modern-card route-card h-100">
                                <!-- Imagen del vehículo -->
                                <div class="image-overlay">
                                    <?php if (!empty($ruta['vehicle_image'])): ?>
                                        <img src="<?= htmlspecialchars('/' . $ruta['vehicle_image']); ?>" 
                                            class="card-img-top rounded-top-4"
                                            alt="<?= htmlspecialchars($ruta['vehicle']); ?>"
                                            style="height: 220px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-gradient rounded-top-4 d-flex align-items-center justify-content-center" style="height: 220px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="bi bi-car-front display-1 text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3" style="font-size: 1.25rem;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= htmlspecialchars($ruta['origen']); ?> 
                                        <i class="bi bi-arrow-right text-primary mx-1"></i> 
                                        <?= htmlspecialchars($ruta['desti']); ?>
                                    </h5>
                                    <span class="modern-badge bg-warning text-dark mb-3">
                                        <i class="bi bi-car-front me-1"></i>
                                        <?= htmlspecialchars($ruta['vehicle']); ?>
                                    </span>
                                    <div class="d-flex align-items-center gap-2 mb-3 text-muted">
                                        <i class="bi bi-calendar3"></i>
                                        <span><?= htmlspecialchars($ruta['data_ruta']); ?></span>
                                        <i class="bi bi-clock ms-2"></i>
                                        <span><?= substr($ruta['hora_inici'],0,5); ?> - <?= substr($ruta['hora_fi'],0,5); ?></span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="status-indicator text-success mb-2" style="font-size: 0.95rem;">
                                            <strong><?= $ruta['plazas_restantes']; ?></strong> places disponibles
                                        </div>
                                        
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="price-tag" style="font-size: 1.1rem;">
                                                <?php echo number_format($ruta['precio_euros'], 2); ?>€
                                            </span>
                                            <span class="badge bg-warning text-dark" style="padding: 0.5rem 0.75rem;">
                                                <i class="bi bi-coin me-1"></i><?php echo $ruta['precio_drivecoins']; ?> DC
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (!empty($ruta['comentaris'])): ?>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars($ruta['comentaris']); ?></p>
                                    <?php endif; ?>

                                    <!-- Botón para abrir modal -->
                                    <button type="button" class="btn btn-warning btn-modern w-100 shadow"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reservarModal"
                                            data-ruta-id="<?= $ruta['id']; ?>"
                                            data-ruta-origen="<?= htmlspecialchars($ruta['origen']); ?>"
                                            data-ruta-desti="<?= htmlspecialchars($ruta['desti']); ?>"
                                            data-ruta-data="<?= htmlspecialchars($ruta['data_ruta']); ?>"
                                            data-ruta-hora-inici="<?= substr($ruta['hora_inici'],0,5); ?>"
                                            data-ruta-hora-fi="<?= substr($ruta['hora_fi'],0,5); ?>"
                                            data-ruta-vehicle="<?= htmlspecialchars($ruta['vehicle']); ?>"
                                            data-plazas-restantes="<?= $ruta['plazas_restantes']; ?>"
                                            data-precio-euros="<?= $ruta['precio_euros']; ?>"
                                            data-precio-dc="<?= $ruta['precio_drivecoins']; ?>"
                                            data-saldo-euros="<?= $user['saldo']; ?>"
                                            data-saldo-dc="<?= $driveCoinModel->getBalance($user['id']); ?>">
                                        <i class="bi bi-calendar-check me-2"></i>Reservar Ruta
                                    </button>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 fade-in">
                        <div class="glass-card shadow-lg rounded-4">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <i class="bi bi-map text-muted" style="font-size: 5rem; opacity: 0.5;"></i>
                                </div>
                                <h4 class="fw-bold mb-3">No hi ha rutes disponibles</h4>
                                <p class="text-muted mb-4">Sembla que no hi ha cap ruta que coincideixi amb els teus filtres.</p>
                                <a href="../../public/index.php?controller=rutes&action=index" class="btn btn-primary btn-modern">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Actualitzar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reserva -->
<div class="modal fade" id="reservarModal" tabindex="-1" aria-labelledby="reservarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="../../public/index.php?controller=rutes&action=reservar">
                <div class="modal-header bg-gradient text-white border-0 rounded-top-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title fw-bold" id="reservarModalLabel">
                        <i class="bi bi-calendar-check-fill me-2"></i>Reservar Ruta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="ruta_id" id="modalRutaId">

                    <!-- Detalles de la ruta con icono y estilo mejorado -->
                    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>Detalls de la ruta
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-signpost-2-fill text-primary me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Ruta</small>
                                            <strong id="modalRutaText" class="text-dark"></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar3 text-success me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Data</small>
                                            <strong id="modalRutaData" class="text-dark"></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock-fill text-info me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Horari</small>
                                            <strong id="modalRutaHorari" class="text-dark"></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-car-front-fill text-warning me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Vehicle</small>
                                            <strong id="modalRutaVehicle" class="text-dark"></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de plazas con diseño mejorado -->
                    <div class="mb-4">
                        <label for="plazas" class="form-label fw-bold text-dark">
                            <i class="bi bi-people-fill text-primary me-2"></i>Places a reservar
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-0">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="number" class="form-control border-0 shadow-sm" name="plazas" id="modalPlazas" min="1" value="1" required>
                        </div>
                        <div class="mt-2 p-2 bg-light rounded">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>Places disponibles: 
                                <span id="modalPlazasDisponibles" class="fw-bold text-success"></span>
                            </small>
                        </div>
                    </div>

                    <!-- Precio con diseño mejorado -->
                    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-tag-fill text-info me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Preu per plaça</small>
                                            <strong id="modalPrecioUnitario" class="fs-5 text-dark"></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin text-success me-2 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Preu total</small>
                                            <strong id="modalPrecioTotal" class="fs-4 text-success"></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo del usuario -->
                    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%);">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-wallet2 text-warning me-2 fs-3"></i>
                                <div>
                                    <small class="text-muted d-block">El teu saldo actual</small>
                                    <strong id="modalSaldo" class="fs-5 text-dark"></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opciones de pago mejoradas -->
                    <h6 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-credit-card-fill text-primary me-2"></i>Mètode de pagament
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" id="btnPayDriveCoins" class="btn btn-success btn-lg w-100 shadow-sm rounded-3">
                                <i class="bi bi-coin me-2"></i>
                                <div class="d-block">
                                    <strong>DriveCoins</strong>
                                    <small class="d-block">Moneda virtual</small>
                                </div>
                            </button>
                            <small class="text-muted mt-1 d-block text-center" id="statusDC"></small>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnPayMoney" class="btn btn-primary btn-lg w-100 shadow-sm rounded-3">
                                <i class="bi bi-currency-euro me-2"></i>
                                <div class="d-block">
                                    <strong>Diners</strong>
                                    <small class="d-block">+20% bonus DC</small>
                                </div>
                            </button>
                            <small class="text-muted mt-1 d-block text-center" id="statusMoney"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel·lar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Pago -->
<div class="modal fade" id="confirmPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Pagament
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <i class="bi bi-cash-stack text-warning" style="font-size: 4rem;"></i>
                </div>
                
                <h6 class="fw-bold text-center mb-3" id="confirmTitle"></h6>
                
                <div class="card border-0 shadow-sm mb-3" style="background: #f8f9fa;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ruta:</span>
                            <strong id="confirmRuta"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Places:</span>
                            <strong id="confirmPlazas"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Import total:</span>
                            <strong class="text-danger" id="confirmTotal"></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Saldo actual:</span>
                            <strong id="confirmSaldoActual"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Saldo després:</span>
                            <strong class="text-success" id="confirmSaldoDespues"></strong>
                        </div>
                    </div>
                </div>

                <div id="bonusInfo" class="alert alert-success d-none">
                    <i class="bi bi-gift-fill me-2"></i>
                    <strong>Bonus:</strong> Guanyaràs <span id="bonusDC"></span> DriveCoins!
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Aquesta acció no es pot desfer. Confirma que vols continuar.
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancel·lar
                </button>
                <button type="button" id="btnConfirmPayment" class="btn btn-success rounded-3 px-4">
                    <i class="bi bi-check-circle-fill me-2"></i>Confirmar Pagament
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dark Mode Toggle -->
<button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle Dark Mode">
    <i class="bi bi-moon-stars fs-4" id="darkModeIcon"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const html = document.documentElement;
    
    // Check saved theme
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
    const reservarModal = document.getElementById('reservarModal');
    let precioUnitarioEuros = 0;
    let precioUnitarioDC = 0;
    let saldoEuros = 0;
    let saldoDC = 0;

    reservarModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const rutaId = button.getAttribute('data-ruta-id');
        const origen = button.getAttribute('data-ruta-origen');
        const desti = button.getAttribute('data-ruta-desti');
        const dataRuta = button.getAttribute('data-ruta-data');
        const horaInici = button.getAttribute('data-ruta-hora-inici');
        const horaFi = button.getAttribute('data-ruta-hora-fi');
        const vehicle = button.getAttribute('data-ruta-vehicle');
        const plazasRestantes = parseInt(button.getAttribute('data-plazas-restantes'));
        precioUnitarioEuros = parseFloat(button.getAttribute('data-precio-euros'));
        precioUnitarioDC = parseFloat(button.getAttribute('data-precio-dc'));
        saldoEuros = parseFloat(button.getAttribute('data-saldo-euros'));
        saldoDC = parseFloat(button.getAttribute('data-saldo-dc'));

        document.getElementById('modalRutaId').value = rutaId;
        document.getElementById('modalRutaText').textContent = `${origen} → ${desti}`;
        document.getElementById('modalRutaData').textContent = dataRuta;
        document.getElementById('modalRutaHorari').textContent = `${horaInici} - ${horaFi}`;
        document.getElementById('modalRutaVehicle').textContent = vehicle;
        document.getElementById('modalPlazasDisponibles').textContent = plazasRestantes;
        document.getElementById('modalPrecioUnitario').textContent = `${precioUnitarioEuros.toFixed(2)} € / ${precioUnitarioDC.toFixed(2)} DC`;
        document.getElementById('modalSaldo').textContent = `${saldoEuros.toFixed(2)} € | ${saldoDC.toFixed(2)} DC`;
        
        // Set max plazas to available seats
        document.getElementById('modalPlazas').setAttribute('max', plazasRestantes);
        document.getElementById('modalPlazas').value = 1;
        
        // Calculate initial total
        updatePrecioTotal();
    });

    // Update total price when plazas change
    document.getElementById('modalPlazas').addEventListener('input', updatePrecioTotal);

    function updatePrecioTotal() {
        const plazas = parseInt(document.getElementById('modalPlazas').value) || 1;
        const totalEuros = precioUnitarioEuros * plazas;
        const totalDC = precioUnitarioDC * plazas;
        document.getElementById('modalPrecioTotal').textContent = `${totalEuros.toFixed(2)} € / ${totalDC.toFixed(2)} DC`;
        
        // Validar saldo y actualizar botones
        validatePaymentButtons(totalEuros, totalDC);
    }

    function validatePaymentButtons(totalEuros, totalDC) {
        const btnDC = document.getElementById('btnPayDriveCoins');
        const btnMoney = document.getElementById('btnPayMoney');
        const statusDC = document.getElementById('statusDC');
        const statusMoney = document.getElementById('statusMoney');

        // Verificar DriveCoins
        if (saldoDC >= totalDC) {
            btnDC.disabled = false;
            btnDC.classList.remove('opacity-50');
            statusDC.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Saldo suficient';
            statusDC.classList.remove('text-danger');
            statusDC.classList.add('text-success');
        } else {
            btnDC.disabled = true;
            btnDC.classList.add('opacity-50');
            statusDC.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Saldo insuficient';
            statusDC.classList.remove('text-success');
            statusDC.classList.add('text-danger');
        }

        // Verificar Dinero
        if (saldoEuros >= totalEuros) {
            btnMoney.disabled = false;
            btnMoney.classList.remove('opacity-50');
            statusMoney.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Saldo suficient';
            statusMoney.classList.remove('text-danger');
            statusMoney.classList.add('text-success');
        } else {
            btnMoney.disabled = true;
            btnMoney.classList.add('opacity-50');
            statusMoney.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Saldo insuficient';
            statusMoney.classList.remove('text-success');
            statusMoney.classList.add('text-danger');
        }
    }

    // Botones de pago
    let selectedPaymentMethod = '';
    
    document.getElementById('btnPayDriveCoins').addEventListener('click', function() {
        if (!this.disabled) {
            selectedPaymentMethod = 'drivecoins';
            showConfirmationModal();
        }
    });

    document.getElementById('btnPayMoney').addEventListener('click', function() {
        if (!this.disabled) {
            selectedPaymentMethod = 'money';
            showConfirmationModal();
        }
    });

    function showConfirmationModal() {
        const plazas = parseInt(document.getElementById('modalPlazas').value) || 1;
        const totalEuros = precioUnitarioEuros * plazas;
        const totalDC = precioUnitarioDC * plazas;
        const rutaText = document.getElementById('modalRutaText').textContent;

        // Llenar modal de confirmación
        if (selectedPaymentMethod === 'drivecoins') {
            document.getElementById('confirmTitle').textContent = 'Pagar amb DriveCoins';
            document.getElementById('confirmTotal').textContent = `${totalDC.toFixed(2)} DC`;
            document.getElementById('confirmSaldoActual').textContent = `${saldoDC.toFixed(2)} DC`;
            document.getElementById('confirmSaldoDespues').textContent = `${(saldoDC - totalDC).toFixed(2)} DC`;
            document.getElementById('bonusInfo').classList.add('d-none');
        } else {
            document.getElementById('confirmTitle').textContent = 'Pagar amb Diners';
            document.getElementById('confirmTotal').textContent = `${totalEuros.toFixed(2)} €`;
            document.getElementById('confirmSaldoActual').textContent = `${saldoEuros.toFixed(2)} €`;
            document.getElementById('confirmSaldoDespues').textContent = `${(saldoEuros - totalEuros).toFixed(2)} €`;
            
            // Mostrar bonus
            const bonus = (totalEuros * 0.2).toFixed(2);
            document.getElementById('bonusDC').textContent = `${bonus} DC`;
            document.getElementById('bonusInfo').classList.remove('d-none');
        }

        document.getElementById('confirmRuta').textContent = rutaText;
        document.getElementById('confirmPlazas').textContent = plazas;

        // Cerrar modal de reserva y abrir confirmación
        const reservarModal = bootstrap.Modal.getInstance(document.getElementById('reservarModal'));
        reservarModal.hide();
        
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmPaymentModal'));
        confirmModal.show();
    }

    // Confirmar pago
    document.getElementById('btnConfirmPayment').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processant...';

        // Crear formulario y enviarlo
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../../public/index.php?controller=rutes&action=reservar';

        const rutaIdInput = document.createElement('input');
        rutaIdInput.type = 'hidden';
        rutaIdInput.name = 'ruta_id';
        rutaIdInput.value = document.getElementById('modalRutaId').value;

        const plazasInput = document.createElement('input');
        plazasInput.type = 'hidden';
        plazasInput.name = 'plazas';
        plazasInput.value = document.getElementById('modalPlazas').value;

        const payMethodInput = document.createElement('input');
        payMethodInput.type = 'hidden';
        payMethodInput.name = 'pay_method';
        payMethodInput.value = selectedPaymentMethod;

        form.appendChild(rutaIdInput);
        form.appendChild(plazasInput);
        form.appendChild(payMethodInput);

        document.body.appendChild(form);
        form.submit();
    });

    document.querySelectorAll('input[name="min_precio"], input[name="max_precio"]').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value < 0) input.value = 0;
        });
    });
</script>

</body>
</html>
