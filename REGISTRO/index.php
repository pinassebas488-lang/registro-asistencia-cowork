<?php
// Fallback para asegurar que el sistema funcione
// Redirige a index.html si existe, sino muestra error

if (file_exists(__DIR__ . '/index.html')) {
    // Redirigir a index.html
    header('Location: index.html');
    exit;
} else {
    // Mostrar página de error si no existe index.html
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Asistencia QR</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>❌ Error Crítico</h1>
        <div class="error">
            <strong>No se encuentra el archivo index.html</strong><br>
            Por favor, verifica que el archivo index.html esté presente en el servidor.
        </div>
    </div>
</body>
</html>';
}
?>
