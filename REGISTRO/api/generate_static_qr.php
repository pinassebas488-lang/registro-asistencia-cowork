<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['codigo_empleado'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Código de empleado requerido']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Verificar si el empleado existe y está activo
        $stmt = $db->prepare("SELECT id, nombre, apellido, codigo_empleado FROM empleados WHERE codigo_empleado = ? AND activo = 1");
        $stmt->execute([$data['codigo_empleado']]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$empleado) {
            http_response_code(404);
            echo json_encode(['error' => 'Empleado no encontrado o inactivo']);
            exit;
        }
        
        // Generar QR estático basado en información permanente del empleado
        $qr_data = [
            'empleado_id' => $empleado['id'],
            'codigo_empleado' => $empleado['codigo_empleado'],
            'nombre' => $empleado['nombre'],
            'apellido' => $empleado['apellido'],
            'tipo' => 'static_qr',
            'version' => '1.0',
            'generado' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            'success' => true,
            'qr_data' => json_encode($qr_data),
            'empleado' => $empleado,
            'tipo' => 'static',
            'mensaje' => 'QR estático generado. Este código es permanente para este empleado.'
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
