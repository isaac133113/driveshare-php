<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - El meu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../horaris/dashboard.php">
                <i class="bi bi-car-front me-2"></i>DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../horaris/dashboard.php">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="#">
                    <i class="bi bi-person me-1"></i>Perfil
                </a>
                <a class="nav-link" href="?logout=1">
                    <i class="bi bi-box-arrow-right me-1"></i>Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold text-dark mb-1">
                            <i class="bi bi-person-circle text-primary me-2"></i>
                            El meu Perfil
                        </h2>
                        <p class="text-muted mb-0">
                            Gestiona la informació del teu compte
                        </p>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Informació Personal
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label fw-semibold">
                                        <i class="bi bi-person me-2"></i>Nom
                                    </label>
                                    <input type="text" class="form-control" name="nom" id="nom" 
                                           value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cognoms" class="form-label fw-semibold">
                                        <i class="bi bi-people me-2"></i>Cognoms
                                    </label>
                                    <input type="text" class="form-control" name="cognoms" id="cognoms" 
                                           value="<?php echo htmlspecialchars($user['cognoms'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correu Electrònic
                                </label>
                                <input type="email" class="form-control" name="email" id="email" 
                                       value="<?php echo htmlspecialchars($user['correu'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Actualitzar Perfil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card border-0 shadow rounded-4 mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>Canviar Contrasenya
                        </h5>
                    </div>
                    <div class="card-body p-4">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>