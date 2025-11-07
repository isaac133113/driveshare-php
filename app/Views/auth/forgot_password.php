<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Recuperar Contrasenya</title>
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
                            <i class="bi bi-key text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Recuperar Contrasenya</h2>
                            <p class="text-muted">Introdueix el teu email per rebre un enllaç de recuperació</p>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> rounded-3" role="alert">
                                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correu Electrònic
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3" 
                                       name="email" id="email" required placeholder="exemple@correu.com">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold py-3">
                                    <i class="bi bi-send me-2"></i>Enviar Enllaç de Recuperació
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    Recordes la contrasenya? 
                                    <a href="../horaris/login.php" class="text-primary fw-semibold text-decoration-none">
                                        Inicia sessió aquí
                                    </a>
                                </p>
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