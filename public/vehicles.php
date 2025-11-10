<?php
require_once __DIR__ . '/../controllers/VehicleController.php';

$ctrl = new VehicleController();
$ctrl->index();
exit;