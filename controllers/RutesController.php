<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/HorariRutaModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/DriveCoinModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/VehicleModel.php';
define('DRIVECOIN_CONVERSION_RATE', 1); // 1€ = 1 DC

class RutesController extends BaseController {
    private $rutaModel;
    private $reservaModel;
    private $driveCoinModel;

    public function __construct() {
        parent::__construct();
        $this->rutaModel = new HorariRutaModel();
        $this->reservaModel = new ReservaModel();
        $this->driveCoinModel = new DriveCoinModel();
    }

    public function index() {
        $rutes = $this->rutaModel->getAllRutes();
        $user = $this->userModel->getById($_SESSION['user_id']);
        $driveCoinModel = new DriveCoinModel();

        // Filtros del GET
        $origen = strtolower($_GET['origen'] ?? '');
        $desti = strtolower($_GET['desti'] ?? '');
        $tipus = strtolower($_GET['tipus'] ?? '');
        $marca_model = strtolower($_GET['marca_model'] ?? '');
        $min_precio = $_GET['min_precio'] ?? '';
        $max_precio = $_GET['max_precio'] ?? '';
        $data_ruta = $_GET['data_ruta'] ?? ''; // <-- nuevo

        // Filtrar rutas
        $rutes = array_filter($rutes, function($ruta) use ($origen, $desti, $tipus, $marca_model, $min_precio, $max_precio, $data_ruta) {
            if ($origen && stripos($ruta['origen'], $origen) === false) return false;
            if ($desti && stripos($ruta['desti'], $desti) === false) return false;
            if ($tipus && stripos(strtolower($ruta['tipus'] ?? ''), $tipus) === false) return false;
            if ($marca_model && stripos(strtolower($ruta['marca_model'] ?? ''), $marca_model) === false) return false;
            if ($min_precio !== '' && $ruta['precio_euros'] < floatval($min_precio)) return false;
            if ($max_precio !== '' && $ruta['precio_euros'] > floatval($max_precio)) return false;
            if ($data_ruta && $ruta['data_ruta'] !== $data_ruta) return false; // <-- nuevo filtro exacto por fecha
            return true;
        });

        // Calcular DriveCoins y plazas restantes
        foreach ($rutes as &$ruta) {
            $ruta['precio_drivecoins'] = $ruta['precio_euros'] * DRIVECOIN_CONVERSION_RATE;
            $reservas = $this->reservaModel->getByRuta($ruta['id']);
            $ocupadas = 0;
            foreach ($reservas as $res) {
                $ocupadas += $res['plazas'];
            }
            $ruta['plazas_restantes'] = $ruta['plazas_disponibles'] - $ocupadas;
        }

        // Tipos de vehículo para el select
        $vehicleModel = new VehicleModel();
        $tipos = $vehicleModel->getTiposVehicles();
        $selectedTipo = $tipus;

        include __DIR__ . '/../views/rutes/index.php';
    }

    public function reservar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $rutaId = intval($_POST['ruta_id']);
            $plazas = intval($_POST['plazas']);
            $paymentMethod = $_POST['pay_method'] ?? 'money';

            // Obtener la ruta por ID
            $ruta = $this->rutaModel->getById($rutaId);
            if (!$ruta) {
                $_SESSION['message'] = "Ruta no encontrada.";
                $_SESSION['messageType'] = "danger";
                header("Location: " . BASE_URL . "/public/index.php?controller=rutes&action=index");
                exit;
            }

            // Calcular plazas restantes
            $reservas = $this->reservaModel->getByRuta($rutaId);
            $ocupadas = 0;
            foreach ($reservas as $res) {
                $ocupadas += $res['plazas'];
            }
            $plazasRestantes = $ruta['plazas_disponibles'] - $ocupadas;

            if ($plazas > $plazasRestantes) {
                $_SESSION['message'] = "No hay suficientes plazas disponibles.";
                $_SESSION['messageType'] = "danger";
                header("Location: " . BASE_URL . "/public/index.php?controller=rutes&action=index");
                exit;
            }

            if ($paymentMethod === 'drivecoins') {
                // Calcular precio en DriveCoins
                $precioDriveCoins = $ruta['precio_euros'] * DRIVECOIN_CONVERSION_RATE;

                // Intentar gastar DriveCoins
                $success = $this->driveCoinModel->spendCoins($userId, $precioDriveCoins, "Reserva ruta ID $rutaId");

                if (!$success) {
                    $_SESSION['message'] = "Saldo insuficiente de DriveCoins.";
                    $_SESSION['messageType'] = "danger";
                    header("Location: " . BASE_URL . "/public/index.php?controller=rutes&action=index");
                    exit;
                }

                // Crear reserva
                $this->reservaModel->create($userId, $rutaId, $plazas);

                $_SESSION['message'] = "Reserva realizada con DriveCoins!";
                $_SESSION['messageType'] = "success";

            } else {
                // Pago con dinero
                $totalEurosGastados = $ruta['precio_euros'] * $plazas;

                // Obtener datos de usuario y propietario de la ruta
                $usuario = $this->userModel->getById($userId);
                $propietario = $this->userModel->getById($ruta['user_id']);

                if ($usuario['saldo'] < $totalEurosGastados) {
                    $_SESSION['message'] = "Saldo insuficiente.";
                    $_SESSION['messageType'] = "danger";
                    header("Location: " . BASE_URL . "/public/index.php?controller=rutes&action=index");
                    exit;
                }

                // Descontar del usuario
                $this->userModel->updateSaldo($userId, $usuario['saldo'] - $totalEurosGastados);

                // Sumar al propietario
                $this->userModel->updateSaldo($ruta['user_id'], $propietario['saldo'] + $totalEurosGastados);

                // Crear reserva
                $this->reservaModel->create($userId, $rutaId, $plazas);

                // Bonus DriveCoins
                $bonusDC = $totalEurosGastados * 0.2; // 20% de los euros gastados
                if ($bonusDC > 0) {
                    $this->driveCoinModel->addCoins($userId, $bonusDC, "Bonus por gastar $totalEurosGastados €");
                    $_SESSION['message'] = "Reserva realizada! ¡Ganaste $bonusDC DriveCoins!";
                } else {
                    $_SESSION['message'] = "Reserva realizada!";
                }

                $_SESSION['messageType'] = "success";
            }

            header("Location: " . BASE_URL . "/public/index.php?controller=rutes&action=index");
            exit;
        }
    }
}
