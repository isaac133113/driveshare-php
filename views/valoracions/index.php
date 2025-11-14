<?php
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);

$valoracionesPendientes = $valoracionesPendientes ?? [];
$topConductores = $topConductores ?? [];
$misValoracionesRecibidas = $misValoracionesRecibidas ?? [];
$misValoracionesDadas = $misValoracionesDadas ?? [];
?>

<!DOCTYPE html>
<html lang="ca" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valoracions - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/modern-styles.css" rel="stylesheet">
    <style>
        .rating-display {
            color: #ffc107;
            font-size: 1.2em;
        }
        .rating-input {
            color: #ddd;
            font-size: 1.5em;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-input:hover,
        .rating-input.active {
            color: #ffc107;
        }
        .valoracion-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .valoracion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="gradient-bg" style="min-height: 100vh;">

<?php include __DIR__ . '/../templates/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <i class="bi bi-star-fill text-warning display-3"></i>
                <h1 class="fw-bold mt-3">Sistema de Valoracions</h1>
                <p class="text-muted">Valora les teves experiències i ajuda a millorar la comunitat DriveShare</p>
            </div>

            <!-- Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tabs Navigation -->
            <ul class="nav nav-pills nav-fill mb-4" id="valoracionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="pendientes-tab" data-bs-toggle="pill" data-bs-target="#pendientes" type="button" role="tab">
                        <i class="bi bi-clock me-2"></i>Pendents de Valorar
                        <?php if (count($valoracionesPendientes) > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo count($valoracionesPendientes); ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="recibidas-tab" data-bs-toggle="pill" data-bs-target="#recibidas" type="button" role="tab">
                        <i class="bi bi-inbox me-2"></i>Rebudes
                        <?php if (count($misValoracionesRecibidas) > 0): ?>
                            <span class="badge bg-primary ms-1"><?php echo count($misValoracionesRecibidas); ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="dadas-tab" data-bs-toggle="pill" data-bs-target="#dadas" type="button" role="tab">
                        <i class="bi bi-send me-2"></i>Enviades
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="top-tab" data-bs-toggle="pill" data-bs-target="#top" type="button" role="tab">
                        <i class="bi bi-trophy me-2"></i>Top Conductors
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="valoracionTabContent">
                
                <!-- Valoraciones Pendientes -->
                <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                    <div class="glass-card border-0 shadow-lg rounded-4">
                        <div class="card-header border-0 rounded-top-4 p-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);">
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bi bi-clock me-2"></i>Rutes Pendents de Valorar
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (count($valoracionesPendientes) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($valoracionesPendientes as $ruta): ?>
                                        <div class="col-md-6">
                                            <div class="valoracion-card rounded-4 p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-bold text-primary mb-0">
                                                        <?php echo htmlspecialchars($ruta['origen']); ?> 
                                                        <i class="bi bi-arrow-right mx-1"></i> 
                                                        <?php echo htmlspecialchars($ruta['desti']); ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($ruta['data_ruta'])); ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo date('H:i', strtotime($ruta['hora_inici'])); ?> - 
                                                        <?php echo date('H:i', strtotime($ruta['hora_fi'])); ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="fw-semibold">Conductor:</small>
                                                    <div class="text-primary">
                                                        <i class="bi bi-person-circle me-1"></i>
                                                        <?php echo htmlspecialchars($ruta['conductor_nom'] . ' ' . $ruta['conductor_cognoms']); ?>
                                                    </div>
                                                </div>
                                                
                                                <button type="button" class="btn btn-warning btn-sm w-100" 
                                                        onclick="openRatingModal(<?php echo $ruta['id']; ?>, <?php echo $ruta['conductor_id']; ?>, '<?php echo htmlspecialchars($ruta['conductor_nom'] . ' ' . $ruta['conductor_cognoms']); ?>', '<?php echo htmlspecialchars($ruta['origen'] . ' → ' . $ruta['desti']); ?>')">
                                                    <i class="bi bi-star me-1"></i>Valorar Conductor
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success display-4"></i>
                                    <h5 class="text-muted mt-3">No tens valoracions pendents</h5>
                                    <p class="text-muted">Totes les teves rutes han estat valorades!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Valoraciones Recibidas -->
                <div class="tab-pane fade" id="recibidas" role="tabpanel">
                    <div class="glass-card border-0 shadow-lg rounded-4">
                        <div class="card-header border-0 rounded-top-4 p-3" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bi bi-inbox me-2"></i>Valoracions Rebudes
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (count($misValoracionesRecibidas) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($misValoracionesRecibidas as $valoracion): ?>
                                        <div class="col-12">
                                            <div class="valoracion-card rounded-4 p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold mb-1">
                                                            <?php echo htmlspecialchars($valoracion['ruta_info']['origen']); ?> 
                                                            <i class="bi bi-arrow-right mx-1"></i> 
                                                            <?php echo htmlspecialchars($valoracion['ruta_info']['desti']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y H:i', strtotime($valoracion['ruta_info']['data_ruta'] . ' ' . $valoracion['ruta_info']['hora_inici'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="rating-display">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="bi bi-star<?php echo $i <= $valoracion['puntuacion'] ? '-fill' : ''; ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <small class="text-muted"><?php echo $valoracion['puntuacion']; ?>/5</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Per:</small>
                                                        <div class="fw-semibold">
                                                            <?php echo htmlspecialchars($valoracion['nom'] . ' ' . $valoracion['cognoms']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if (!empty($valoracion['comentario'])): ?>
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <i class="bi bi-chat-quote text-muted me-1"></i>
                                                        <em><?php echo htmlspecialchars($valoracion['comentario']); ?></em>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted display-4"></i>
                                    <h5 class="text-muted mt-3">No has rebut valoracions encara</h5>
                                    <p class="text-muted">Crea més rutes per rebre valoracions dels passatgers!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Valoraciones Dadas -->
                <div class="tab-pane fade" id="dadas" role="tabpanel">
                    <div class="glass-card border-0 shadow-lg rounded-4">
                        <div class="card-header border-0 rounded-top-4 p-3" style="background: linear-gradient(135deg, #2196f3 0%, #1565c0 100%);">
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bi bi-send me-2"></i>Les Teves Valoracions
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (count($misValoracionesDadas) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($misValoracionesDadas as $valoracion): ?>
                                        <div class="col-12">
                                            <div class="valoracion-card rounded-4 p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold mb-1">
                                                            <?php echo htmlspecialchars($valoracion['origen']); ?> 
                                                            <i class="bi bi-arrow-right mx-1"></i> 
                                                            <?php echo htmlspecialchars($valoracion['desti']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y H:i', strtotime($valoracion['data_ruta'] . ' ' . $valoracion['hora_inici'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="rating-display">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="bi bi-star<?php echo $i <= $valoracion['puntuacion'] ? '-fill' : ''; ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <small class="text-muted"><?php echo $valoracion['puntuacion']; ?>/5</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Conductor:</small>
                                                        <div class="fw-semibold">
                                                            <?php echo htmlspecialchars($valoracion['conductor_nom'] . ' ' . $valoracion['conductor_cognoms']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if (!empty($valoracion['comentario'])): ?>
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <i class="bi bi-chat-quote text-muted me-1"></i>
                                                        <em><?php echo htmlspecialchars($valoracion['comentario']); ?></em>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-send text-muted display-4"></i>
                                    <h5 class="text-muted mt-3">No has enviat cap valoració encara</h5>
                                    <p class="text-muted">Reserva rutes i valora les teves experiències!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Conductores -->
                <div class="tab-pane fade" id="top" role="tabpanel">
                    <div class="glass-card border-0 shadow-lg rounded-4">
                        <div class="card-header border-0 rounded-top-4 p-3" style="background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);">
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bi bi-trophy me-2"></i>Top Conductors de DriveShare
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (count($topConductores) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($topConductores as $index => $conductor): ?>
                                        <div class="col-md-6">
                                            <div class="valoracion-card rounded-4 p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <?php if ($index === 0): ?>
                                                                <i class="bi bi-trophy-fill fs-4"></i>
                                                            <?php elseif ($index === 1): ?>
                                                                <i class="bi bi-award-fill fs-4"></i>
                                                            <?php elseif ($index === 2): ?>
                                                                <i class="bi bi-medal-fill fs-4"></i>
                                                            <?php else: ?>
                                                                <span class="fw-bold"><?php echo $index + 1; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1">
                                                            <?php echo htmlspecialchars($conductor['nom'] . ' ' . $conductor['cognoms']); ?>
                                                        </h6>
                                                        <div class="rating-display mb-1">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="bi bi-star<?php echo $i <= round($conductor['promedio']) ? '-fill' : ''; ?>"></i>
                                                            <?php endfor; ?>
                                                            <span class="text-muted ms-1"><?php echo $conductor['promedio']; ?>/5</span>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo $conductor['total_valoraciones']; ?> valoracions
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-trophy text-muted display-4"></i>
                                    <h5 class="text-muted mt-3">Encara no hi ha conductors valorats</h5>
                                    <p class="text-muted">Sigues el primer a valorar i crear el ranking!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Valoración -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-star me-2"></i>Valorar Conductor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../public/index.php?controller=valoracion&action=create" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="ruta_id" name="ruta_id">
                    <input type="hidden" id="conductor_id" name="conductor_id">
                    <input type="hidden" id="puntuacion" name="puntuacion" value="5">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ruta:</label>
                        <div id="ruta_info" class="text-primary fw-semibold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Conductor:</label>
                        <div id="conductor_info" class="text-muted"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Puntuació:</label>
                        <div class="rating-container text-center py-2">
                            <span class="rating-input" data-rating="1">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="rating-input" data-rating="2">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="rating-input" data-rating="3">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="rating-input" data-rating="4">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="rating-input" data-rating="5">
                                <i class="bi bi-star-fill"></i>
                            </span>
                        </div>
                        <div class="text-center">
                            <small id="rating-text" class="text-muted">Excel·lent (5/5)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentari (opcional):</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3" 
                                  placeholder="Comparteix la teva experiència amb aquest conductor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-star me-1"></i>Enviar Valoració
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Rating system
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('.rating-input');
    const ratingText = document.getElementById('rating-text');
    const puntuacionInput = document.getElementById('puntuacion');
    
    const ratingTexts = {
        1: 'Molt dolent (1/5)',
        2: 'Dolent (2/5)', 
        3: 'Regular (3/5)',
        4: 'Bo (4/5)',
        5: 'Excel·lent (5/5)'
    };
    
    ratingInputs.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            puntuacionInput.value = rating;
            
            // Update visual stars
            ratingInputs.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
            
            // Update text
            ratingText.textContent = ratingTexts[rating];
        });
        
        star.addEventListener('mouseover', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            ratingInputs.forEach((s, i) => {
                if (i < rating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    // Reset on mouse leave
    document.querySelector('.rating-container').addEventListener('mouseleave', () => {
        const currentRating = parseInt(puntuacionInput.value);
        ratingInputs.forEach((s, i) => {
            if (i < currentRating) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
});

function openRatingModal(rutaId, conductorId, conductorNombre, rutaInfo) {
    document.getElementById('ruta_id').value = rutaId;
    document.getElementById('conductor_id').value = conductorId;
    document.getElementById('conductor_info').textContent = conductorNombre;
    document.getElementById('ruta_info').textContent = rutaInfo;
    
    // Reset form
    document.getElementById('comentario').value = '';
    document.getElementById('puntuacion').value = 5;
    
    // Reset rating display
    const ratingInputs = document.querySelectorAll('.rating-input');
    ratingInputs.forEach((star, index) => {
        if (index < 5) {
            star.classList.add('active');
            star.style.color = '#ffc107';
        } else {
            star.classList.remove('active');
            star.style.color = '#ddd';
        }
    });
    document.getElementById('rating-text').textContent = 'Excel·lent (5/5)';
    
    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}
</script>

</body>
</html>