<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactar amb el propietari - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vehicle-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .chat-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .message-templates {
            margin-bottom: 20px;
        }
        .template-btn {
            margin-bottom: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-car-front-fill"></i> DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">
                    <i class="bi bi-house"></i> Inici
                </a>
                <a class="nav-link" href="../ver-coches.php">
                    <i class="bi bi-car-front"></i> Vehicles
                </a>
                <a class="nav-link" href="ChatController.php">
                    <i class="bi bi-chat-dots"></i> Xat
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Información del vehículo -->
                <div class="vehicle-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">
                                <i class="bi bi-car-front"></i> 
                                <?php echo htmlspecialchars($vehicle['name']); ?>
                            </h3>
                            <p class="mb-2 fs-5">
                                <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-geo-alt"></i> 
                                <?php echo htmlspecialchars($vehicle['address']); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Propietari:</strong> 
                                <?php echo htmlspecialchars($vehicle['owner_name']); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-end">
                                <div class="fs-3 fw-bold">
                                    <?php echo number_format($vehicle['price_hour'], 0); ?>€/h
                                </div>
                                <div class="fs-5">
                                    <?php echo number_format($vehicle['price_day'], 0); ?>€/dia
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de chat -->
                <div class="chat-form">
                    <div class="d-flex align-items-center mb-4">
                        <a href="../ver-coches.php" class="btn btn-outline-secondary me-3">
                            <i class="bi bi-arrow-left"></i> Tornar
                        </a>
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-chat-text text-primary"></i> 
                                Contactar amb el propietari
                            </h4>
                            <p class="text-muted mb-0">
                                Envia un missatge a <?php echo htmlspecialchars($vehicle['owner_name']); ?> 
                                sobre el vehicle <?php echo htmlspecialchars($vehicle['name']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Plantillas de mensaje -->
                    <div class="message-templates">
                        <h6 class="text-muted mb-3">Plantilles de missatge:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-primary template-btn w-100" 
                                        onclick="setTemplate('Hola! M\'interessa el teu vehicle. Està disponible per al període que necessito?')">
                                    <i class="bi bi-clock"></i> Consultar disponibilitat
                                </button>
                                <button type="button" class="btn btn-outline-primary template-btn w-100" 
                                        onclick="setTemplate('Bon dia! Podries donar-me més informació sobre l\'estat del vehicle i les condicions de lloguer?')">
                                    <i class="bi bi-info-circle"></i> Demanar informació
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-primary template-btn w-100" 
                                        onclick="setTemplate('Hola! M\'agradaria veure el vehicle abans de fer la reserva. Quan seria possible?')">
                                    <i class="bi bi-eye"></i> Sol·licitar visita
                                </button>
                                <button type="button" class="btn btn-outline-primary template-btn w-100" 
                                        onclick="setTemplate('Bon dia! Tinc algunes preguntes sobre les condicions d\'ús del vehicle.')">
                                    <i class="bi bi-question-circle"></i> Preguntes generals
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <form id="chatForm">
                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">
                                <i class="bi bi-chat-text"></i> El teu missatge:
                            </label>
                            <textarea class="form-control" 
                                      id="message" 
                                      name="message" 
                                      rows="6" 
                                      placeholder="Escriu el teu missatge aquí..."
                                      maxlength="1000"
                                      required></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/1000 caràcters
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Consells per a un bon missatge:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sigues clar sobre les teves necessitats</li>
                                <li>Indica les dates aproximades que necessites el vehicle</li>
                                <li>Pregunta sobre les condicions específiques</li>
                                <li>Mantingues un to respectuós i professional</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="../ver-coches.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel·lar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send"></i> Enviar missatge
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Contador de caracteres
        const messageTextarea = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Función para usar plantillas
        function setTemplate(text) {
            messageTextarea.value = text;
            charCount.textContent = text.length;
            messageTextarea.focus();
        }
        
        // Envío del formulario
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviant...';
            
            const formData = new FormData(this);
            
            fetch('ChatController.php?action=start', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirigir a la conversación
                    window.location.href = 'ChatController.php?action=conversation&id=' + data.conversation_id;
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de connexió. Torna-ho a provar.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
</body>
</html>