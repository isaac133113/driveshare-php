<?php
// Entrada pública mínima: delega en el controlador para preparar datos y cargar la vista
require_once __DIR__ . '/../controllers/DriveCoinController.php';

$controller = new DriveCoinController();
$controller->renderPage();
exit;
