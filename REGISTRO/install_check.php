<?php
// Script para verificar instalación completa
header('Content-Type: text/plain');

echo "=== VERIFICACIÓN DE INSTALACIÓN COMPLETA ===\n\n";

echo "1. Variables de Entorno:\n";
echo "RENDER: " . (getenv('RENDER') ?: 'NO') . "\n";
echo "HOST_DE_BASE_DE_DATOS: " . (getenv('HOST_DE_BASE_DE_DATOS') ?: 'NO DEFINIDO') . "\n";
echo "NOMBRE_BD: " . (getenv('NOMBRE_BD') ?: 'NO DEFINIDO') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NO DEFINIDO') . "\n";
echo "CONTRASEÑA_DE_LA_BASE_DE_DATOS: " . (getenv('CONTRASEÑA_DE_LA_BASE_DE_DATOS') ? '***DEFINIDO***' : 'NO DEFINIDO') . "\n";

echo "\n2. Drivers PDO Disponibles:\n";
$drivers = PDO::getAvailableDrivers();
foreach ($drivers as $driver) {
    echo "- $driver\n";
}

echo "\n3. Extensiones PostgreSQL:\n";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ pdo_pgsql cargado\n";
} else {
    echo "❌ pdo_pgsql NO cargado\n";
}

if (extension_loaded('pgsql')) {
    echo "✅ pgsql cargado\n";
} else {
    echo "❌ pgsql NO cargado\n";
}

echo "\n4. Intento de Conexión:\n";
try {
    $host = getenv('HOST_DE_BASE_DE_DATOS') ?: 'localhost';
    $dbname = getenv('NOMBRE_BD') ?: 'registro_asistencia';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('CONTRASEÑA_DE_LA_BASE_DE_DATOS') ?: '';
    
    echo "Host: $host\n";
    echo "DB: $dbname\n";
    echo "User: $user\n";
    
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password";
    $conn = new PDO($dsn);
    echo "✅ Conexión PostgreSQL exitosa\n";
    
    // Verificar tablas
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Información PHP:\n";
echo "Versión: " . phpversion() . "\n";
echo "Sistema: " . php_uname() . "\n";
?>
