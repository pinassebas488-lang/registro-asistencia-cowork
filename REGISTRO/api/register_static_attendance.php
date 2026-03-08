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
    
    if (!isset($data['qr_data'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos QR requeridos']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Decodificar datos del QR estático
        $qr_data = json_decode($data['qr_data'], true);
        
        // Debug: Log para ver qué se está recibiendo
        error_log("QR Data recibido: " . $data['qr_data']);
        error_log("QR Data parseado: " . print_r($qr_data, true));
        
        if (!$qr_data) {
            http_response_code(400);
            echo json_encode(['error' => 'QR inválido - formato JSON incorrecto']);
            exit;
        }
        
        // Validar QR estático (más flexible)
        if (!isset($qr_data['empleado_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'QR inválido - falta empleado_id']);
            exit;
        }
        
        // Si no tiene tipo, asumir que es estático (compatibilidad)
        if (!isset($qr_data['tipo'])) {
            $qr_data['tipo'] = 'static_qr';
        }
        
        if ($qr_data['tipo'] !== 'static_qr') {
            http_response_code(400);
            echo json_encode(['error' => 'QR inválido - no es un QR estático']);
            exit;
        }
        
        // Verificar que el empleado siga activo
        $stmt = $db->prepare("SELECT id, nombre, apellido, codigo_empleado FROM empleados WHERE id = ? AND activo = 1");
        $stmt->execute([$qr_data['empleado_id']]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$empleado) {
            http_response_code(404);
            echo json_encode(['error' => 'Empleado no encontrado o inactivo']);
            exit;
        }
        
        // Determinar tipo de registro (entrada/salida)
        $stmt = $db->prepare("SELECT tipo_registro FROM asistencia 
                             WHERE empleado_id = ? 
                             ORDER BY fecha_hora DESC 
                             LIMIT 1");
        $stmt->execute([$qr_data['empleado_id']]);
        $ultimo_registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $tipo_registro = ($ultimo_registro && $ultimo_registro['tipo_registro'] === 'entrada') ? 'salida' : 'entrada';
        
        // Obtener configuración
        $stmt = $db->prepare("SELECT valor FROM configuracion WHERE parametro = 'allow_multiple_entries'");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $allow_multiple = $config['valor'] === 'true';
        
        // Validar regla de entrada/salida
        if (!$allow_multiple && $tipo_registro === 'entrada' && $ultimo_registro && $ultimo_registro['tipo_registro'] === 'entrada') {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe un registro de entrada sin salida']);
            exit;
        }
        
        // Registrar asistencia
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO asistencia (empleado_id, qr_token, tipo_registro, ip_address, user_agent) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $qr_data['empleado_id'], 
            'static_qr_' . $qr_data['empleado_id'],
            $tipo_registro, 
            $ip_address, 
            $user_agent
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Registro de $tipo_registro exitoso",
            'empleado' => [
                'nombre' => $empleado['nombre'],
                'apellido' => $empleado['apellido'],
                'codigo_empleado' => $empleado['codigo_empleado']
            ],
            'tipo_registro' => $tipo_registro,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'qr_tipo' => 'estático'
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
