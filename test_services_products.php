<?php
require_once 'config/connection.php';

try {
    // Check current services table structure
    $sql = "DESCRIBE services";
    $result = $php_fetch_direct($sql);
    
    echo "<h3>Current Services Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check current services data
    $services = $php_fetch('services', '*');
    
    echo "<h3>Current Services/Products Data:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Duration</th><th>Type</th><th>Stock</th></tr>";
    
    if ($services && count($services) > 0) {
        foreach (array_slice($services, 0, 10) as $service) {
            echo "<tr>";
            echo "<td>{$service['id']}</td>";
            echo "<td>{$service['service_name']}</td>";
            echo "<td>₱{$service['price']}</td>";
            echo "<td>{$service['per_minute']} min</td>";
            echo "<td>" . (isset($service['type']) ? $service['type'] : 'N/A') . "</td>";
            echo "<td>" . (isset($service['stock']) ? $service['stock'] : 'N/A') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No services found</td></tr>";
    }
    echo "</table>";
    
    // Check column status
    $hasType = false;
    $hasStock = false;
    foreach ($result as $row) {
        if ($row['Field'] === 'type') $hasType = true;
        if ($row['Field'] === 'stock') $hasStock = true;
    }
    
    echo "<h3>Feature Status:</h3>";
    echo "<p><strong>Has type column:</strong> " . ($hasType ? '✅ YES' : '❌ NO - Run migration') . "</p>";
    echo "<p><strong>Has stock column:</strong> " . ($hasStock ? '✅ YES' : '❌ NO - Run migration') . "</p>";
    
    if (!$hasType || !$hasStock) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h4>⚠️ Missing Columns Detected</h4>";
        echo "<p>To enable full services & products functionality, you need to run the database migration:</p>";
        echo "<p><strong>File:</strong> <code>database_migration_services_products.sql</code></p>";
        echo "<p><strong>Action:</strong> Run this SQL in your database console (Supabase SQL Editor)</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d1edff; border: 1px solid #74b9ff; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h4>✅ Ready to Use!</h4>";
        echo "<p>Your database supports both services and products. You can now:</p>";
        echo "<ul>";
        echo "<li>Add services with duration</li>";
        echo "<li>Add products with stock quantity</li>";
        echo "<li>Filter by type in the admin panel</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>