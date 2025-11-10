<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/DriveCoinModel.php';

class DriveCoinController {
    private $driveCoinModel;
    
    public function __construct() {
        $this->driveCoinModel = new DriveCoinModel();
    }
    
    /**
     * Manejar peticiones AJAX para operaciones de DriveCoins
     */
    public function handleRequest() {
        session_start();
        
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado']);
            return;
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_balance':
                $this->getBalance();
                break;
                
            case 'get_packages':
                $this->getPackages();
                break;
                
            case 'purchase_package':
                $this->purchasePackage();
                break;
                
            case 'get_transaction_history':
                $this->getTransactionHistory();
                break;
                
            case 'convert_euros':
                $this->convertEuros();
                break;
                
            case 'check_sufficient_balance':
                $this->checkSufficientBalance();
                break;
                
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    /**
     * Obtener saldo actual de DriveCoins del usuario
     */
    private function getBalance() {
        $balance = $this->driveCoinModel->getBalance($_SESSION['user_id']);
        $this->jsonResponse([
            'success' => true,
            'balance' => $balance,
            'formatted_balance' => number_format($balance, 0, ',', '.') . ' DC'
        ]);
    }
    
    /**
     * Obtener paquetes disponibles de DriveCoins
     */
    private function getPackages() {
        $packages = $this->driveCoinModel->getAvailablePackages();
        $this->jsonResponse([
            'success' => true,
            'packages' => $packages
        ]);
    }
    
    /**
     * Procesar compra de paquete de DriveCoins
     */
    private function purchasePackage() {
        $packageId = intval($_POST['package_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'card';
        
        if ($packageId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de paquete no válido']);
            return;
        }
        
        // Simular procesamiento de pago (en producción aquí iría la integración con Stripe, PayPal, etc.)
        $paymentResult = $this->simulatePayment($paymentMethod);
        
        if (!$paymentResult['success']) {
            $this->jsonResponse($paymentResult);
            return;
        }
        
        // Procesar compra de DriveCoins
        $result = $this->driveCoinModel->purchaseDriveCoins($_SESSION['user_id'], $packageId, $paymentMethod);
        
        if ($result['success']) {
            // Añadir información adicional para la respuesta
            $result['payment_reference'] = $paymentResult['payment_reference'];
        }
        
        $this->jsonResponse($result);
    }
    
    /**
     * Obtener historial de transacciones
     */
    private function getTransactionHistory() {
        $limit = intval($_GET['limit'] ?? 20);
        $transactions = $this->driveCoinModel->getTransactionHistory($_SESSION['user_id'], $limit);
        
        // Formatear transacciones para mostrar
        $formattedTransactions = array_map(function($transaction) {
            return [
                'type' => $transaction['transaction_type'],
                'amount' => $transaction['amount'],
                'description' => $transaction['description'],
                'reference' => $transaction['reference_id'],
                'date' => date('d/m/Y H:i', strtotime($transaction['created_at'])),
                'formatted_amount' => number_format(abs($transaction['amount']), 0, ',', '.') . ' DC',
                'is_positive' => $transaction['amount'] > 0
            ];
        }, $transactions);
        
        $this->jsonResponse([
            'success' => true,
            'transactions' => $formattedTransactions
        ]);
    }
    
    /**
     * Convertir cantidad de euros a DriveCoins
     */
    private function convertEuros() {
        $euros = floatval($_GET['euros'] ?? 0);
        
        if ($euros <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cantidad no válida']);
            return;
        }
        
        $driveCoins = DriveCoinModel::eurosToDriveCoins($euros);
        
        $this->jsonResponse([
            'success' => true,
            'euros' => $euros,
            'drivecoins' => $driveCoins,
            'conversion_rate' => DriveCoinModel::CONVERSION_RATE
        ]);
    }
    
    /**
     * Verificar si el usuario tiene suficiente saldo para una transacción
     */
    private function checkSufficientBalance() {
        $requiredAmount = floatval($_GET['amount'] ?? 0);
        $currentBalance = $this->driveCoinModel->getBalance($_SESSION['user_id']);
        
        $this->jsonResponse([
            'success' => true,
            'sufficient' => $currentBalance >= $requiredAmount,
            'current_balance' => $currentBalance,
            'required_amount' => $requiredAmount,
            'deficit' => max(0, $requiredAmount - $currentBalance)
        ]);
    }
    
    /**
     * Simular procesamiento de pago
     */
    private function simulatePayment($paymentMethod) {
        // Simular tiempo de procesamiento
        usleep(500000); // 0.5 segundos
        
        // Simular éxito del 95% (en producción esto sería real)
        $success = rand(1, 100) <= 95;
        
        if (!$success) {
            return [
                'success' => false,
                'message' => 'Error en el procesamiento del pago. Inténtalo nuevamente.'
            ];
        }
        
        return [
            'success' => true,
            'payment_reference' => 'PAY_' . date('Ymd_His') . '_' . rand(1000, 9999),
            'payment_method' => $paymentMethod
        ];
    }
    
    /**
     * Procesar pago con DriveCoins (usado desde otros controladores)
     */
    public function processPayment($userId, $amount, $description, $referenceId = null) {
        return $this->driveCoinModel->spendDriveCoins($userId, $amount, $description, $referenceId);
    }
    
    /**
     * Obtener estadísticas de DriveCoins del usuario
     */
    public function getUserStats($userId) {
        $balance = $this->driveCoinModel->getBalance($userId);
        $transactions = $this->driveCoinModel->getTransactionHistory($userId, 100);
        
        $stats = [
            'current_balance' => $balance,
            'total_purchased' => 0,
            'total_spent' => 0,
            'total_bonus' => 0,
            'total_transactions' => count($transactions)
        ];
        
        foreach ($transactions as $transaction) {
            switch ($transaction['transaction_type']) {
                case DriveCoinModel::TRANSACTION_PURCHASE:
                    $stats['total_purchased'] += $transaction['amount'];
                    break;
                case DriveCoinModel::TRANSACTION_RESERVATION:
                    $stats['total_spent'] += abs($transaction['amount']);
                    break;
                case DriveCoinModel::TRANSACTION_BONUS:
                    $stats['total_bonus'] += $transaction['amount'];
                    break;
            }
        }
        
        return $stats;
    }

    /**
     * Render page to buy DriveCoins (prepara variables y carga la vista)
     */
    public function renderPage() {
        session_start();

        // Si no hay sesión, redirigir al login de la app
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../app/Views/horaris/login.php');
            exit;
        }

        // Preparar datos para la vista
        try {
            $packages = $this->driveCoinModel->getAvailablePackages();
        } catch (Throwable $e) {
            $packages = [];
            error_log('DriveCoinController::renderPage packages error: ' . $e->getMessage());
        }

        try {
            $currentBalance = $this->driveCoinModel->getBalance($_SESSION['user_id']);
        } catch (Throwable $e) {
            $currentBalance = 0;
            error_log('DriveCoinController::renderPage balance error: ' . $e->getMessage());
        }

        // Incluir la vista (ruta relativa al directorio controllers)
        include __DIR__ . '/../views/drivecoins/comprar-drivecoins.php';
        exit;
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Manejar peticiones directas a este archivo
if (basename($_SERVER['SCRIPT_NAME']) === 'DriveCoinController.php') {
    $controller = new DriveCoinController();
    $controller->handleRequest();
}