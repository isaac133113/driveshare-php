<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Canviar Contrasenya</title>
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
                            <h2 class="fw-bold text-dark mt-3">Canviar Contrasenya</h2>
                            <p class="text-muted">Actualitza la teva contrasenya de forma segura</p>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> rounded-3" role="alert">
                                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Contrasenya Actual
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3" 
                                       name="current_password" id="current_password" required 
                                       placeholder="Introdueix la contrasenya actual">
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold">
                                    <i class="bi bi-key me-2"></i>Nova Contrasenya
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3" 
                                       name="new_password" id="new_password" required 
                                       placeholder="Introdueix la nova contrasenya">
                                <div class="form-text">
                                    <small>Mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial</small>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-2"></i>Confirmar Nova Contrasenya
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3" 
                                       name="confirm_password" id="confirm_password" required 
                                       placeholder="Repeteix la nova contrasenya">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold py-3">
                                    <i class="bi bi-check-circle me-2"></i>Canviar Contrasenya
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-muted mb-0">
                                <a href="../../dashboard.php" class="text-primary fw-semibold text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>Tornar al Dashboard
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validación en tiempo real de la contraseña
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Les contrasenyes no coincideixen');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const confirmPassword = document.getElementById('confirm_password');
            
            // Validar fortaleza de contraseña
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            const hasMinLength = password.length >= 8;
            
            if (!hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar || !hasMinLength) {
                this.setCustomValidity('La contrasenya no compleix els requisits mínims');
            } else {
                this.setCustomValidity('');
            }
            
            // Re-validar confirmación
            if (confirmPassword.value && password !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Les contrasenyes no coincideixen');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>
</html>