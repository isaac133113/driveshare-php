
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sessió</title>
    <!-- Bootstrap CSS CDN -->
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
                            <i class="bi bi-person-circle text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Iniciar Sessió</h2>
                            <p class="text-muted">Accedeix al teu compte</p>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="../../public/index.php?controller=auth&action=login" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correu Electrònic
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3" 
                                       name="email" id="email" required placeholder="exemple@correu.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Contrasenya
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3" 
                                       name="password" id="password" required placeholder="Introdueix la teva contrasenya">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sessió
                                </button>
                            </div>
                            
                            <div class="text-center mb-3">
                                <a href="../../controllers/AuthController.php?action=forgot-password" class="text-muted text-decoration-none">
                                    <i class="bi bi-key me-1"></i>Has oblidat la contrasenya?
                                </a>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    No tens compte? 
                                    <a href="registre.php" class="text-primary fw-semibold text-decoration-none">
                                        Registra't aquí
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>