<?php
// Valoraciones - Vista principal
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Valoracions</title>
    <link rel="stylesheet" href="../../public/css/modern-styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4"><i class="bi bi-star me-2"></i>Valoracions</h2>
        <div class="card glass-card mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" id="valoracionesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">Pendents</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="recibidas-tab" data-bs-toggle="tab" data-bs-target="#recibidas" type="button" role="tab">Rebudes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dadas-tab" data-bs-toggle="tab" data-bs-target="#dadas" type="button" role="tab">Enviades</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="top-tab" data-bs-toggle="tab" data-bs-target="#top" type="button" role="tab">Top Conductors</button>
                    </li>
                </ul>
                <div class="tab-content" id="valoracionesTabsContent">
                    <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                        <!-- Valoraciones pendientes -->
                        <?php if (!empty($valoracionesPendientes)): ?>
                            <ul class="list-group">
                                <?php foreach ($valoracionesPendientes as $pendiente): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <strong><?= htmlspecialchars($pendiente['origen']) ?> → <?= htmlspecialchars($pendiente['desti']) ?></strong>
                                            <br>
                                            <small><?= htmlspecialchars($pendiente['data_ruta']) ?> <?= htmlspecialchars($pendiente['hora_inici']) ?> - <?= htmlspecialchars($pendiente['hora_fi']) ?></small>
                                        </span>
                                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#valorarModal" data-ruta-id="<?= $pendiente['id'] ?>" data-conductor-id="<?= $pendiente['conductor_id'] ?>">
                                            Valorar
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-star display-4"></i>
                                <h5 class="mt-3">No tens valoracions pendents</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="recibidas" role="tabpanel">
                        <!-- Valoraciones recibidas -->
                        <?php if (!empty($misValoracionesRecibidas)): ?>
                            <ul class="list-group">
                                <?php foreach ($misValoracionesRecibidas as $recibida): ?>
                                    <li class="list-group-item">
                                        <strong><?= htmlspecialchars($recibida['origen']) ?> → <?= htmlspecialchars($recibida['desti']) ?></strong>
                                        <br>
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $recibida['puntuacion'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <small class="text-muted ms-2"><?= htmlspecialchars($recibida['fecha_valoracion']) ?></small>
                                        <?php if (!empty($recibida['comentario'])): ?>
                                            <p class="mb-0 text-muted"><em>"<?= htmlspecialchars($recibida['comentario']) ?>"</em></p>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-star display-4"></i>
                                <h5 class="mt-3">No has rebut valoracions</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="dadas" role="tabpanel">
                        <!-- Valoraciones dadas -->
                        <?php if (!empty($misValoracionesDadas)): ?>
                            <ul class="list-group">
                                <?php foreach ($misValoracionesDadas as $dada): ?>
                                    <li class="list-group-item">
                                        <strong><?= htmlspecialchars($dada['origen']) ?> → <?= htmlspecialchars($dada['desti']) ?></strong>
                                        <br>
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $dada['puntuacion'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <small class="text-muted ms-2"><?= htmlspecialchars($dada['fecha_valoracion']) ?></small>
                                        <?php if (!empty($dada['comentario'])): ?>
                                            <p class="mb-0 text-muted"><em>"<?= htmlspecialchars($dada['comentario']) ?>"</em></p>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-star display-4"></i>
                                <h5 class="mt-3">No has enviat valoracions</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="top" role="tabpanel">
                        <!-- Top conductores -->
                        <?php if (!empty($topConductores)): ?>
                            <ul class="list-group">
                                <?php foreach ($topConductores as $conductor): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <strong><?= htmlspecialchars($conductor['nom']) ?> <?= htmlspecialchars($conductor['cognoms']) ?></strong>
                                            <br>
                                            <span class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= round($conductor['promedio']) ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <small class="text-muted ms-2">Mitjana: <?= number_format($conductor['promedio'], 2) ?> (<?= $conductor['total_valoraciones'] ?> valoracions)</small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-star display-4"></i>
                                <h5 class="mt-3">Encara no hi ha ranking</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para valorar -->
    <div class="modal fade" id="valorarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="../../public/index.php?controller=valoracion&action=create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-star me-2"></i>Valorar Ruta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ruta_id" id="modalRutaId">
                        <input type="hidden" name="conductor_id" id="modalConductorId">
                        <div class="mb-3">
                            <label for="puntuacion" class="form-label">Puntuació</label>
                            <div id="starRating" class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="puntuacion" id="modalPuntuacion" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentari (opcional)</label>
                            <textarea class="form-control" name="comentario" id="modalComentario" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tancar</button>
                        <button type="submit" class="btn btn-primary">Enviar Valoració</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal: pasar datos de la ruta y conductor
        var valorarModal = document.getElementById('valorarModal');
        valorarModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var rutaId = button.getAttribute('data-ruta-id');
            var conductorId = button.getAttribute('data-conductor-id');
            document.getElementById('modalRutaId').value = rutaId;
            document.getElementById('modalConductorId').value = conductorId;
        });
        // Star rating
        var stars = document.querySelectorAll('#starRating .bi-star');
        stars.forEach(function(star) {
            star.addEventListener('click', function() {
                var value = this.getAttribute('data-value');
                document.getElementById('modalPuntuacion').value = value;
                stars.forEach(function(s, i) {
                    if (i < value) {
                        s.classList.add('bi-star-fill');
                        s.classList.remove('bi-star');
                    } else {
                        s.classList.add('bi-star');
                        s.classList.remove('bi-star-fill');
                    }
                });
            });
        });
    </script>
</body>
</html>
