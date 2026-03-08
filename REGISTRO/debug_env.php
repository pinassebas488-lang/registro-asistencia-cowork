<?php
// Archivo para debug de variables de entorno
header('Content-Type: text/plain');

echo "=== DEBUG DE VARIABLES DE ENTORNO ===\n\n";

echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NO DEFINIDO') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NO DEFINIDO') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NO DEFINIDO') . "\n";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? '***DEFINIDO***' : 'NO DEFINIDO') . "\n";
echo "DB_TYPE: " . (getenv('DB_TYPE') ?: 'NO DEFINIDO') . "\n";

echo "\n=== TODAS LAS VARIABLES DE ENTORNO ===\n\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DB') === 0 || strpos($key, 'RENDER') === 0) {
        echo "$key: " . ($key === 'DB_PASSWORD' ? '***DEFINIDO***' : $value) . "\n";
    }
}

echo "\n=== INFORMACIÓN DEL SERVIDOR ===\n\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'NO DEFINIDO') . "\n";
echo "Server Addr: " . ($_SERVER['SERVER_ADDR'] ?? 'NO DEFINIDO') . "\n";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'NO DEFINIDO') . "\n";

// Intentar conexión a PostgreSQL
echo "\n=== INTENTO DE CONEXIÓN POSTGRESQL ===\n\n";

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'registro_asistencia';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password";
    echo "Intentando conectar con: $dsn\n";
    
    $conn = new PDO($dsn);
    echo "✅ Conexión PostgreSQL exitosa\n";
    
    // Verificar tablas
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
?>
