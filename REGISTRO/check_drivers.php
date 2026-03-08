<?php
// Script para verificar drivers de PDO
header('Content-Type: text/plain');

echo "=== VERIFICACIÓN DE DRIVERS PDO ===\n\n";

echo "Drivers PDO disponibles:\n";
$drivers = PDO::getAvailableDrivers();
foreach ($drivers as $driver) {
    echo "- $driver\n";
}

echo "\n=== VERIFICACIÓN DE EXTENSIONES ===\n\n";

echo "Extensiones cargadas:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $extension) {
    if (strpos($extension, 'pdo') !== false || strpos($extension, 'pgsql') !== false || strpos($extension, 'mysql') !== false) {
        echo "- $extension\n";
    }
}

echo "\n=== INFORMACIÓN DE PHP ===\n\n";
echo "Versión PHP: " . phpversion() . "\n";
echo "Versión PDO: " . phpversion('pdo') . "\n";

echo "\n=== INTENTO DE CONEXIÓN POSTGRESQL ===\n\n";

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'registro_asistencia';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '';
    
    echo "Intentando conectar a PostgreSQL...\n";
    echo "Host: $host\n";
    echo "DB: $dbname\n";
    echo "User: $user\n";
    
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password";
    $conn = new PDO($dsn);
    echo "✅ Conexión PostgreSQL exitosa\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== INTENTO DE CONEXIÓN MYSQL ===\n\n";

try {
    $host = 'localhost';
    $dbname = 'registro_asistencia';
    $user = 'root';
    $password = '';
    
    echo "Intentando conectar a MySQL...\n";
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $password);
    echo "✅ Conexión MySQL exitosa\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
