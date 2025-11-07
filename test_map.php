<?php
// Simple test to check if map variables are working
session_start();

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

// Include the MapController and test
require_once 'controllers/MapController.php';

echo "Testing MapController...\n";

try {
    $controller = new MapController();
    echo "MapController instantiated successfully.\n";
    
    // Capture output
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "Map page generated successfully!\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
    // Check if the output contains our variables
    if (strpos($output, '$vehicles') !== false) {
        echo "✓ Found vehicle references in output\n";
    } else {
        echo "✗ No vehicle references found\n";
    }
    
    if (strpos($output, 'nearbyVehicles') !== false) {
        echo "✗ WARNING: Still found nearbyVehicles references!\n";
    } else {
        echo "✓ No old nearbyVehicles references found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>