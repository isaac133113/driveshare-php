<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Estructura - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Test de Estructura del Proyecto</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<h5>Información del Sistema</h5>";
                        echo "<p><strong>Fecha/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
                        echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
                        echo "<p><strong>Directorio raíz:</strong> " . realpath(__DIR__ . '/..') . "</p>";
                        
                        echo "<h5 class='mt-4'>Verificación de Rutas</h5>";
                        
                        // Verificar archivos clave
                        $archivos_clave = [
                            'app/Config/config.php' => '../app/Config/config.php',
                            'app/Config/Database.php' => '../app/Config/Database.php',
                            'app/Controllers/VehicleController.php' => '../app/Controllers/VehicleController.php',
                            'app/Controllers/MapController.php' => '../app/Controllers/MapController.php',
                            'app/Controllers/DriveCoinController.php' => '../app/Controllers/DriveCoinController.php',
                            'app/Models/DriveCoinModel.php' => '../app/Models/DriveCoinModel.php',
                            'app/Views/horaris/dashboard.php' => '../app/Views/horaris/dashboard.php'
                        ];
                        
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>Archivo</th><th>Estado</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($archivos_clave as $nombre => $ruta) {
                            $existe = file_exists($ruta);
                            $icono = $existe ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>';
                            $estado = $existe ? 'Existe' : 'No encontrado';
                            $clase = $existe ? 'text-success' : 'text-danger';
                            
                            echo "<tr>";
                            echo "<td>{$nombre}</td>";
                            echo "<td class='{$clase}'>{$icono} {$estado}</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody></table>";
                        echo "</div>";
                        
                        echo "<h5 class='mt-4'>Enlaces de Prueba</h5>";
                        echo "<div class='d-grid gap-2'>";
                        echo "<a href='../app/Views/horaris/dashboard.php' class='btn btn-outline-primary'>Dashboard</a>";
                        echo "<a href='ver-coches.php' class='btn btn-outline-info'>Ver Coches</a>";
                        echo "<a href='buscar-coche.php' class='btn btn-outline-success'>Buscar Coche</a>";
                        echo "<a href='comprar-drivecoins.php' class='btn btn-outline-warning'>Comprar DriveCoins</a>";
                        echo "</div>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>