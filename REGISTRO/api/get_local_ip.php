<?php
// API para detectar IP local
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener IP local del servidor
$localIP = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';

// Si es localhost, intentar obtener IP real de la red
if ($localIP === '127.0.0.1' || $localIP === '::1') {
    // Intentar obtener IP de la interfaz de red
    $output = [];
    exec('ipconfig', $output);
    
    foreach ($output as $line) {
        if (preg_match('/IPv4.*?(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            $ip = $matches[1];
            // Excluir IPs de loopback y APIs privadas comunes
            if ($ip !== '127.0.0.1' && !preg_match('/^169\.254\./', $ip)) {
                $localIP = $ip;
                break;
            }
        }
    }
}

echo json_encode([
    'ip' => $localIP,
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
    'port' => $_SERVER['SERVER_PORT'] ?? '80'
]);
?>
