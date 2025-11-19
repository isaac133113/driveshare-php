
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - El meu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/modern-styles.css">
</head>
<body class="gradient-bg" style="min-height: 100vh;">
    <?php include_once __DIR__ . "/../templates/navbar.php" ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?php echo urlencode($user['nom'] ?? 'U'); ?>" alt="Avatar" class="rounded-circle border border-3" style="width: 120px; height: 120px; background: #f8f9fa;">
                        </div>
                        <h2 class="fw-bold text-dark mb-1">
                            <i class="bi bi-person-circle text-primary me-2"></i>
                            <?php echo htmlspecialchars($user['nom'] ?? ''); ?> <?php echo htmlspecialchars($user['cognoms'] ?? ''); ?>
                        </h2>
                        <p class="text-muted mb-2">
                            <i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($user['correu'] ?? ''); ?>
                        </p>
                        <span class="badge bg-primary rounded-pill px-3 py-2 mb-2">
                            <i class="bi bi-person-badge me-1"></i> <?php echo htmlspecialchars($user['rol'] ?? 'Usuari'); ?>
                        </span>
                        <p class="text-muted small mb-0">Última actualització: <?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card border-0 shadow rounded-4 mb-4">
                    <div class="card-header bg-light border-bottom-0">
                        <ul class="nav nav-tabs card-header-tabs justify-content-center" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                                    <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                    <i class="bi bi-shield-lock me-2"></i>Canviar Contrasenya
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4 tab-content" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="edit" role="tabpanel">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nom" class="form-label fw-semibold">
                                            <i class="bi bi-person me-2"></i>Nom
                                        </label>
                                        <input type="text" class="form-control" name="nom" id="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cognoms" class="form-label fw-semibold">
                                            <i class="bi bi-people me-2"></i>Cognoms
                                        </label>
                                        <input type="text" class="form-control" name="cognoms" id="cognoms" value="<?php echo htmlspecialchars($user['cognoms'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="bi bi-envelope me-2"></i>Correu Electrònic
                                    </label>
                                    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['correu'] ?? ''); ?>" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>Actualitzar Perfil
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">
                                        <i class="bi bi-lock me-2"></i>Contrasenya Actual
                                    </label>
                                    <input type="password" class="form-control" name="current_password" id="current_password" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label fw-semibold">
                                            <i class="bi bi-key me-2"></i>Nova Contrasenya
                                        </label>
                                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label fw-semibold">
                                            <i class="bi bi-key-fill me-2"></i>Confirmar Contrasenya
                                        </label>
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="bi bi-shield-check me-2"></i>Canviar Contrasenya
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>