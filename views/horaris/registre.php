<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre d'usuari</title>
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
                            <i class="bi bi-person-plus-fill text-primary display-4"></i>
                            <h2 class="fw-bold text-dark mt-3">Registre d'usuari</h2>
                            <p class="text-muted">Crea el teu compte</p>
                        </div>
                        
                        <form action="registre.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nom" class="form-label fw-semibold">
                                    <i class="bi bi-person me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control form-control-lg rounded-3" 
                                       name="nom" id="nom" required placeholder="Introdueix el teu nom">
                            </div>
                            
                            <div class="mb-3">
                                <label for="cognom" class="form-label fw-semibold">
                                    <i class="bi bi-people me-2"></i>Cognoms
                                </label>
                                <input type="text" class="form-control form-control-lg rounded-3" 
                                       name="cognom" id="cognom" required placeholder="Introdueix els teus cognoms">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3" 
                                       name="email" id="email" required placeholder="exemple@correu.com">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Contrasenya
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3" 
                                       name="password" id="password" required placeholder="Crea una contrasenya segura">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold py-3">
                                    <i class="bi bi-check-circle me-2"></i>Registrar-se
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="text-muted mb-0">
                                    Ja tens compte? 
                                    <a href="login.php" class="text-primary fw-semibold text-decoration-none">
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
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo '<div class="container mt-4">';
        echo '<div class="row justify-content-center">';
        echo '<div class="col-lg-5 col-md-7">';
        
        $nom = htmlspecialchars($_POST['nom']);
        $cognom = htmlspecialchars($_POST['cognom']);
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];
        
        // Validar contraseña
        $password_errors = [];
        if (strlen($password) < 8) {
            $password_errors[] = "Ha de tenir almenys 8 caràcters";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $password_errors[] = "Ha de contenir almenys una lletra majúscula";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $password_errors[] = "Ha de contenir almenys una lletra minúscula";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $password_errors[] = "Ha de contenir almenys un número";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $password_errors[] = "Ha de contenir almenys un caràcter especial";
        }
        
        if (!empty($password_errors)) {
            echo '<div class="alert alert-danger rounded-3" role="alert">';
            echo '<i class="bi bi-exclamation-triangle me-2"></i>';
            echo '<strong>La contrasenya no és vàlida:</strong><ul class="mb-0 mt-2">';
            foreach ($password_errors as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul></div>';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
            $mysql = new mysqli('localhost','root','','aplicaciocompra');
            if($mysql->connect_error){
                echo '<div class="alert alert-danger rounded-3" role="alert">';
                echo '<i class="bi bi-exclamation-triangle me-2"></i>';
                echo 'Error de connexió: '.$mysql->connect_error;
                echo '</div>';
            } else {
                $sql="INSERT INTO usuaris (nom, cognoms, correu, contrasenya) VALUES (?, ?, ?, ?)";
                $stmt=$mysql->prepare($sql);
                $stmt->bind_param('ssss',$nom,$cognom,$email,$password_hash);
                if($stmt->execute()){
                    echo '<div class="alert alert-success rounded-3" role="alert">';
                    echo '<i class="bi bi-check-circle me-2"></i>';
                    echo '<strong>Usuari registrat correctament!</strong>';
                    echo '<div class="mt-2">';
                    echo '<small>Serà redirigit al login en <span id="countdown">3</span> segons...</small><br>';
                    echo '<a href="login.php" class="btn btn-sm btn-outline-success mt-2">';
                    echo '<i class="bi bi-box-arrow-in-right me-1"></i>Anar al Login ara';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '<script>';
                    echo 'let seconds = 3;';
                    echo 'const countdownElement = document.getElementById("countdown");';
                    echo 'const interval = setInterval(function() {';
                    echo '  seconds--;';
                    echo '  countdownElement.textContent = seconds;';
                    echo '  if (seconds <= 0) {';
                    echo '    clearInterval(interval);';
                    echo '    window.location.href = "login.php";';
                    echo '  }';
                    echo '}, 1000);';
                    echo '</script>';
                } else {
                    echo '<div class="alert alert-danger rounded-3" role="alert">';
                    echo '<i class="bi bi-exclamation-triangle me-2"></i>';
                    echo 'Error al registrar l\'usuari: '.$stmt->error;
                    echo '</div>';
                }
                $stmt->close();
                $mysql->close();
            }
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validación de contraseña en tiempo real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const form = document.querySelector('form');
            
            // Crear contenedor para mostrar requisitos
            const passwordHelp = document.createElement('div');
            passwordHelp.className = 'form-text mt-2';
            passwordHelp.innerHTML = `
                <small class="text-muted">
                    La contrasenya ha de tenir:
                    <ul class="mb-0 mt-1" style="font-size: 0.8em;">
                        <li id="length-check">Almenys 8 caràcters</li>
                        <li id="uppercase-check">Una lletra majúscula</li>
                        <li id="lowercase-check">Una lletra minúscula</li>
                        <li id="number-check">Un número</li>
                        <li id="special-check">Un caràcter especial</li>
                    </ul>
                </small>
            `;
            passwordInput.parentNode.appendChild(passwordHelp);
            
            // Validar en tiempo real
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Validaciones
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[^A-Za-z0-9]/.test(password);
                
                // Actualizar indicadores visuales
                updateCheck('length-check', hasLength);
                updateCheck('uppercase-check', hasUppercase);
                updateCheck('lowercase-check', hasLowercase);
                updateCheck('number-check', hasNumber);
                updateCheck('special-check', hasSpecial);
                
                // Cambiar borde del input
                const isValid = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
                if (password.length > 0) {
                    this.className = isValid ? 
                        'form-control form-control-lg rounded-3 is-valid' : 
                        'form-control form-control-lg rounded-3 is-invalid';
                }
            });
            
            function updateCheck(elementId, isValid) {
                const element = document.getElementById(elementId);
                if (isValid) {
                    element.style.color = '#198754';
                    element.innerHTML = '✓ ' + element.innerHTML.replace('✓ ', '').replace('✗ ', '');
                } else {
                    element.style.color = '#dc3545';
                    element.innerHTML = '✗ ' + element.innerHTML.replace('✓ ', '').replace('✗ ', '');
                }
            }
            
            // Validar antes de enviar
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[^A-Za-z0-9]/.test(password);
                
                if (!(hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial)) {
                    e.preventDefault();
                    alert('La contrasenya no compleix tots els requisits.');
                    passwordInput.focus();
                }
            });
        });
    </script>
</body>
</html>