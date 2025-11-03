<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Restablir Contrasenya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Restablir Contrasenya</h2>
                            <p class="text-muted">Introdueix la teva nova contrasenya</p>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> rounded-3" role="alert">
                                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($validToken): ?>
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="bi bi-lock me-2"></i>Nova Contrasenya
                                    </label>
                                    <input type="password" class="form-control form-control-lg rounded-3" 
                                           name="password" id="password" required placeholder="Introdueix la nova contrasenya">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="bi bi-lock-fill me-2"></i>Confirmar Contrasenya
                                    </label>
                                    <input type="password" class="form-control form-control-lg rounded-3" 
                                           name="confirm_password" id="confirm_password" required placeholder="Repeteix la nova contrasenya">
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold py-3">
                                        <i class="bi bi-check-circle me-2"></i>Actualitzar Contrasenya
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <p class="text-muted mb-0">
                                <a href="../horaris/login.php" class="text-primary fw-semibold text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>Tornar al Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>