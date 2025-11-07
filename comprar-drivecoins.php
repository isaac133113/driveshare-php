<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar DriveCoins - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .drivecoin-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .drivecoin-card:hover {
            transform: translateY(-5px);
            border-color: #0d6efd;
        }
        .popular-badge {
            position: absolute;
            top: -10px;
            right: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            border: none;
        }
        .drivecoin-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 15px;
        }
        .bonus-highlight {
            background: linear-gradient(45deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: bold;
        }
        .payment-method-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-method-card:hover {
            transform: scale(1.02);
        }
        .payment-method-card.selected {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.1);
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <?php
    session_start();
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        header('Location: views/horaris/login.php');
        exit;
    }
    
    require_once 'config/Database.php';
    require_once 'models/DriveCoinModel.php';
    require_once 'controllers/DriveCoinController.php';
    
    $driveCoinModel = new DriveCoinModel();
    $currentBalance = $driveCoinModel->getBalance($_SESSION['user_id']);
    $packages = $driveCoinModel->getAvailablePackages();
    ?>

    <div class="container py-5">
        <!-- Header -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="drivecoin-icon mx-auto">
                            <i class="bi bi-coin"></i>
                        </div>
                        <h1 class="fw-bold text-dark mb-2">Comprar DriveCoins</h1>
                        <p class="text-muted mb-3">La moneda virtual de DriveShare para todas tus reservas</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                    <small class="text-muted">Tu saldo actual</small>
                                    <div class="h4 fw-bold text-primary mb-0" id="currentBalance">
                                        <i class="bi bi-coin me-1"></i><?php echo number_format($currentBalance, 0, ',', '.'); ?> DC
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                    <small class="text-muted">Tasa de conversión</small>
                                    <div class="h5 fw-bold text-info mb-0">
                                        <i class="bi bi-arrow-left-right me-1"></i>1€ = 10 DC
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paquetes de DriveCoins -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h3 class="text-white text-center mb-4">
                    <i class="bi bi-gift me-2"></i>Elige tu Paquete
                </h3>
                <div class="row g-4" id="packagesContainer">
                    <?php foreach ($packages as $index => $package): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-lg rounded-4 drivecoin-card h-100 position-relative" 
                                 data-package-id="<?php echo $package['id']; ?>"
                                 data-price="<?php echo $package['euro_price']; ?>"
                                 data-coins="<?php echo $package['total_coins']; ?>">
                                
                                <?php if ($index == 1): // Popular pack ?>
                                    <span class="badge popular-badge">⭐ Popular</span>
                                <?php elseif ($index == 3): // Best value ?>
                                    <span class="badge popular-badge">🔥 Mejor Valor</span>
                                <?php endif; ?>
                                
                                <div class="card-body p-4 text-center">
                                    <div class="drivecoin-icon">
                                        <i class="bi bi-coin"></i>
                                    </div>
                                    
                                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    
                                    <div class="display-6 fw-bold text-primary mb-2">
                                        €<?php echo number_format($package['euro_price'], 0, ',', '.'); ?>
                                    </div>
                                    
                                    <div class="h5 text-success mb-3">
                                        <i class="bi bi-coin me-1"></i>
                                        <?php echo number_format($package['drivecoins_amount'], 0, ',', '.'); ?> DC
                                        <?php if ($package['bonus_percentage'] > 0): ?>
                                            <small class="bonus-highlight d-block">
                                                + <?php echo number_format(($package['drivecoins_amount'] * $package['bonus_percentage']) / 100, 0, ',', '.'); ?> DC bonus
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($package['bonus_percentage'] > 0): ?>
                                        <div class="alert alert-success py-2 mb-3">
                                            <i class="bi bi-gift me-1"></i>
                                            <small><strong><?php echo $package['bonus_percentage']; ?>% BONUS</strong></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-muted small mb-3">
                                        Total: <strong><?php echo number_format($package['total_coins'], 0, ',', '.'); ?> DriveCoins</strong>
                                    </div>
                                    
                                    <button class="btn btn-primary w-100 rounded-3 buy-package-btn" 
                                            data-package-id="<?php echo $package['id']; ?>">
                                        <i class="bi bi-cart-plus me-2"></i>Comprar Ahora
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="bi bi-info-circle me-2"></i>¿Por qué DriveCoins?
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <i class="bi bi-shield-check text-success display-6 mb-2"></i>
                                <h6 class="fw-bold">Seguro</h6>
                                <small class="text-muted">Tus DriveCoins están protegidos</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-lightning text-warning display-6 mb-2"></i>
                                <h6 class="fw-bold">Instantáneo</h6>
                                <small class="text-muted">Reservas inmediatas sin esperas</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-gift text-primary display-6 mb-2"></i>
                                <h6 class="fw-bold">Bonificaciones</h6>
                                <small class="text-muted">Obtén extras con paquetes grandes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="text-center mt-4">
            <a href="views/horaris/dashboard.php" class="btn btn-outline-light rounded-3">
                <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Modal de Compra -->
    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card me-2"></i>Finalizar Compra
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="purchaseDetails" class="mb-4">
                        <!-- Detalles de la compra se llenarán por JavaScript -->
                    </div>
                    
                    <h6 class="fw-bold mb-3">Método de Pago</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card payment-method-card border-2" data-payment="card">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-credit-card text-primary display-6 mb-2"></i>
                                    <h6 class="mb-1">Tarjeta de Crédito</h6>
                                    <small class="text-muted">Visa, Mastercard, etc.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card payment-method-card border-2" data-payment="paypal">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-paypal text-primary display-6 mb-2"></i>
                                    <h6 class="mb-1">PayPal</h6>
                                    <small class="text-muted">Pago seguro y rápido</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmPurchaseBtn" disabled>
                        <i class="bi bi-lock me-2"></i>Procesar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Resultado -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-4" id="resultModalBody">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedPackage = null;
        let selectedPaymentMethod = null;

        // Event listeners para compra de paquetes
        document.querySelectorAll('.buy-package-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const packageId = this.dataset.packageId;
                const card = this.closest('.drivecoin-card');
                
                selectedPackage = {
                    id: packageId,
                    price: card.dataset.price,
                    coins: card.dataset.coins,
                    name: card.querySelector('h5').textContent
                };
                
                showPurchaseModal();
            });
        });

        // Event listeners para métodos de pago
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección anterior
                document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
                
                // Seleccionar actual
                this.classList.add('selected');
                selectedPaymentMethod = this.dataset.payment;
                
                // Habilitar botón de compra
                document.getElementById('confirmPurchaseBtn').disabled = false;
            });
        });

        // Confirmar compra
        document.getElementById('confirmPurchaseBtn').addEventListener('click', processPurchase);

        function showPurchaseModal() {
            const detailsHTML = `
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold">${selectedPackage.name}</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Precio:</small>
                                <div class="fw-bold text-primary">€${selectedPackage.price}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Recibirás:</small>
                                <div class="fw-bold text-success">
                                    <i class="bi bi-coin"></i> ${parseInt(selectedPackage.coins).toLocaleString()} DC
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('purchaseDetails').innerHTML = detailsHTML;
            
            // Reset payment selection
            document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
            selectedPaymentMethod = null;
            document.getElementById('confirmPurchaseBtn').disabled = true;
            
            new bootstrap.Modal(document.getElementById('purchaseModal')).show();
        }

        function processPurchase() {
            if (!selectedPackage || !selectedPaymentMethod) return;
            
            const btn = document.getElementById('confirmPurchaseBtn');
            const originalText = btn.innerHTML;
            
            // Mostrar loading
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
            btn.disabled = true;
            
            // Enviar petición
            const formData = new FormData();
            formData.append('action', 'purchase_package');
            formData.append('package_id', selectedPackage.id);
            formData.append('payment_method', selectedPaymentMethod);
            
            fetch('controllers/DriveCoinController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('purchaseModal')).hide();
                
                setTimeout(() => {
                    if (data.success) {
                        showSuccessResult(data);
                        updateBalance(data.data.new_balance);
                    } else {
                        showErrorResult(data.message);
                    }
                }, 300);
            })
            .catch(error => {
                console.error('Error:', error);
                bootstrap.Modal.getInstance(document.getElementById('purchaseModal')).hide();
                setTimeout(() => showErrorResult('Error de conexión'), 300);
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function showSuccessResult(data) {
            const resultHTML = `
                <div class="text-center">
                    <i class="bi bi-check-circle text-success display-3 mb-3"></i>
                    <h4 class="fw-bold text-success mb-3">¡Compra Exitosa!</h4>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">DriveCoins Recibidos</small>
                                    <div class="h5 fw-bold text-primary">
                                        <i class="bi bi-coin"></i> ${parseInt(data.data.total_coins).toLocaleString()} DC
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Nuevo Saldo</small>
                                    <div class="h5 fw-bold text-success">
                                        <i class="bi bi-wallet2"></i> ${parseInt(data.data.new_balance).toLocaleString()} DC
                                    </div>
                                </div>
                            </div>
                            ${data.data.bonus_coins > 0 ? `
                                <div class="alert alert-success py-2 mt-2 mb-0">
                                    <small><i class="bi bi-gift me-1"></i>
                                    <strong>Bonus: +${parseInt(data.data.bonus_coins).toLocaleString()} DC</strong></small>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <small class="text-muted">
                        <i class="bi bi-file-text me-1"></i>
                        Referencia: ${data.data.reference_id}
                    </small>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary" data-bs-dismiss="modal">¡Perfecto!</button>
                    </div>
                </div>
            `;
            
            document.getElementById('resultModalBody').innerHTML = resultHTML;
            new bootstrap.Modal(document.getElementById('resultModal')).show();
        }

        function showErrorResult(message) {
            const resultHTML = `
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle text-warning display-3 mb-3"></i>
                    <h4 class="fw-bold text-warning mb-3">Error en la Compra</h4>
                    <p class="text-muted mb-3">${message}</p>
                    <button class="btn btn-warning" data-bs-dismiss="modal">Entendido</button>
                </div>
            `;
            
            document.getElementById('resultModalBody').innerHTML = resultHTML;
            new bootstrap.Modal(document.getElementById('resultModal')).show();
        }

        function updateBalance(newBalance) {
            document.getElementById('currentBalance').innerHTML = 
                `<i class="bi bi-coin me-1"></i>${parseInt(newBalance).toLocaleString()} DC`;
        }

        // Efectos visuales
        document.querySelectorAll('.drivecoin-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>