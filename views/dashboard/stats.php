<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadístiques - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../templates/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-chart-bar"></i> Les meves estadístiques</h2>
                <p class="text-muted">Estadístiques del <?php echo $month; ?>/<?php echo $year; ?></p>
            </div>
        </div>

        <!-- Estadístiques mensuals -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Horaris aquest mes</h6>
                                <h3><?php echo count($monthlyStats); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Rutes favorites</h6>
                                <h3><?php echo count($popularRoutes); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-route fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Vehicles utilitzats</h6>
                                <h3><?php echo count($vehicleUsage); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-car fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Posició ranking</h6>
                                <h3><?php 
                                $userPos = array_search($_SESSION['user_id'], array_column($userRanking, 'id'));
                                echo $userPos !== false ? '#' . ($userPos + 1) : 'N/A';
                                ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trophy fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gràfic anual -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Activitat anual <?php echo $year; ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="yearlyChart" width="400" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking d'usuaris -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy"></i> Ranking d'usuaris</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($userRanking)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($userRanking as $index => $user): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center <?php echo $user['id'] == $_SESSION['user_id'] ? 'bg-light' : ''; ?>">
                                        <div>
                                            <span class="badge bg-<?php echo $index < 3 ? 'warning' : 'secondary'; ?> me-2">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                            <?php echo htmlspecialchars($user['nom'] . ' ' . $user['cognoms']); ?>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <small class="text-muted">(Tu)</small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $user['total_horaris']; ?> horaris
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hi ha dades de ranking disponibles.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-route"></i> Les meves rutes favorites</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($popularRoutes)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($popularRoutes as $route): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                            <?php echo htmlspecialchars($route['origen'] . ' → ' . $route['desti']); ?>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $route['vegades']; ?> vegades
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Encara no tens rutes favorites.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicles més utilitzats -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-car"></i> Vehicles més utilitzats</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vehicleUsage)): ?>
                            <div class="row">
                                <?php foreach ($vehicleUsage as $vehicle): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-car fa-2x text-primary mb-3"></i>
                                                <h6 class="card-title"><?php echo htmlspecialchars($vehicle['vehicle']); ?></h6>
                                                <p class="card-text">
                                                    <span class="badge bg-primary"><?php echo $vehicle['vegades']; ?> vegades</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Encara no has utilitzat cap vehicle.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gràfic anual
        const ctx = document.getElementById('yearlyChart').getContext('2d');
        const yearlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Des'],
                datasets: [{
                    label: 'Horaris per mes',
                    data: <?php echo json_encode(array_values($yearlyStats)); ?>,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>