<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veure Rutes - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">

<?php include_once __DIR__ . "/../templates/navbar.php" ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold text-dark mb-0">
                                <i class="bi bi-map text-warning me-2"></i>Veure Rutes
                            </h2>
                            <p class="text-muted mb-0">Explora i reserva rutes d'altres usuaris</p>
                        </div>
                        <a href="../../public/index.php?controller=horaris&action=index" class="btn btn-success rounded-3">
                            <i class="bi bi-calendar-plus me-2"></i>Les Meves Reserves
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulario de filtros -->
            <div class="card border-0 shadow-lg rounded-4 mb-4 p-3">
                <form method="GET" action="index.php" class="mb-4">
                    <input type="hidden" name="controller" value="rutes">
                    <input type="hidden" name="action" value="index">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="origen" class="form-control" placeholder="Origen" value="<?= htmlspecialchars($_GET['origen'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="desti" class="form-control" placeholder="Destino" value="<?= htmlspecialchars($_GET['desti'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input 
                                type="date" 
                                name="data_ruta" 
                                class="form-control" 
                                value="<?= htmlspecialchars($_GET['data_ruta'] ?? '') ?>"
                            >
                        </div>
                        <div class="col-md-2">
                            <select name="tipus" class="form-control">
                                <option value="">Tipo vehículo</option>
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
                                class="form-control" 
                                placeholder="Min €" 
                                min="0" 
                                step="5" 
                                value="<?= htmlspecialchars($_GET['min_precio'] ?? '') ?>">
                        </div>

                        <div class="col-md-1">
                            <input type="number" 
                                name="max_precio" 
                                class="form-control" 
                                placeholder="Max €" 
                                min="0" 
                                step="5" 
                                value="<?= htmlspecialchars($_GET['max_precio'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button type="submit" class="btn btn-warning">Filtrar</button>
                        <a href="" class="btn btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>

            
            <div class="row g-4">
                <?php if (!empty($rutes)): ?>
                    <?php foreach ($rutes as $ruta): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-lg rounded-4 h-100">
                                <!-- Imagen del vehículo -->
                                <?php if (!empty($ruta['vehicle_image'])): ?>
                                    <img src="<?= htmlspecialchars('/' . $ruta['vehicle_image']); ?>" 
                                        class="card-img-top rounded-top-4"
                                        alt="<?= htmlspecialchars($ruta['vehicle']); ?>"
                                        style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-car-front display-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($ruta['origen']); ?> → <?= htmlspecialchars($ruta['desti']); ?></h5>
                                    <span class="badge bg-warning mb-2"><?= htmlspecialchars($ruta['vehicle']); ?></span>
                                    <p class="text-muted small mb-2">
                                        <?= htmlspecialchars($ruta['data_ruta']); ?> | <?= substr($ruta['hora_inici'],0,5); ?> - <?= substr($ruta['hora_fi'],0,5); ?>
                                    </p>
                                    <p class="text-muted small mb-2">
                                        Plazas disponibles: <strong><?= $ruta['plazas_disponibles'] - $this->reservaModel->getReservedSeats($ruta['id']); ?></strong><br>
                                        Preu: <strong><?php echo number_format($ruta['precio_euros'], 2); ?>€</strong>
                                        <br>
                                        O amb DriveCoins: <strong><?php echo $ruta['precio_drivecoins']; ?> DC</strong>
                                    </p>
                                    <?php if (!empty($ruta['comentaris'])): ?>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars($ruta['comentaris']); ?></p>
                                    <?php endif; ?>

                                    <!-- Botón para abrir modal -->
                                    <button type="button" class="btn btn-outline-warning w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reservarModal"
                                            data-ruta-id="<?= $ruta['id']; ?>"
                                            data-ruta-origen="<?= htmlspecialchars($ruta['origen']); ?>"
                                            data-ruta-desti="<?= htmlspecialchars($ruta['desti']); ?>"
                                            data-precio-euros="<?= $ruta['precio_euros']; ?>"
                                            data-precio-dc="<?= $ruta['precio_drivecoins']; ?>"
                                            data-saldo-euros="<?= $user['saldo']; ?>"
                                            data-saldo-dc="<?= $driveCoinModel->getBalance($user['id']); ?>">
                                        Reservar Ruta
                                    </button>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-map text-muted display-1 mb-3"></i>
                                <h4 class="text-muted mb-3">No hi ha rutes disponibles</h4>
                                <p class="text-muted">Torna més tard per veure noves rutes.</p>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../../index.php?controller=rutes&action=reservar">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservarModalLabel">Reservar Ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ruta_id" id="modalRutaId">

                    <p>Estàs reservant la ruta:</p>
                    <p><strong id="modalRutaText"></strong></p>

                    <div class="mb-3">
                        <label for="plazas" class="form-label">Plazas a reservar:</label>
                        <input type="number" class="form-control" name="plazas" id="modalPlazas" min="1" value="1" required>
                    </div>

                    <!-- Mostrar precio -->
                    <p id="modalPrecio">Precio: 0 € / 0 DC</p>

                    <!-- Mostrar saldo actual del usuario -->
                    <p id="modalSaldo">Tu saldo: 0 € | 0 DC</p>

                    <!-- Opciones de pago -->
                    <p>Selecciona el método de pago:</p>
                    <div class="mb-2">
                        <button type="submit" name="pay_method" value="drivecoins" class="btn btn-success w-100 mb-2">Pagar con DriveCoins</button>
                        <button type="submit" name="pay_method" value="money" class="btn btn-primary w-100">Pagar con dinero</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const reservarModal = document.getElementById('reservarModal');

    reservarModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const rutaId = button.getAttribute('data-ruta-id');
        const origen = button.getAttribute('data-ruta-origen');
        const desti = button.getAttribute('data-ruta-desti');
        const precioEuros = parseFloat(button.getAttribute('data-precio-euros'));
        const precioDC = parseFloat(button.getAttribute('data-precio-dc'));
        const saldoEuros = parseFloat(button.getAttribute('data-saldo-euros'));
        const saldoDC = parseFloat(button.getAttribute('data-saldo-dc'));

        document.getElementById('modalRutaId').value = rutaId;
        document.getElementById('modalRutaText').textContent = `${origen} → ${desti}`;
        document.getElementById('modalPrecio').textContent = `Precio: ${precioEuros.toFixed(2)} € / ${precioDC.toFixed(2)} DC`;
        document.getElementById('modalSaldo').textContent = `Tu saldo: ${saldoEuros.toFixed(2)} € | ${saldoDC.toFixed(2)} DC`;
    });

    document.querySelectorAll('input[name="min_precio"], input[name="max_precio"]').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value < 0) input.value = 0;
        });
    });
</script>

</body>
</html>
