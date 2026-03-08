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
        
        // Decodificar datos del QR
        $qr_data = json_decode($data['qr_data'], true);
        
        if (!$qr_data || !isset($qr_data['token'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato QR inválido']);
            exit;
        }
        
        // Verificar si el QR es válido y no ha expirado
        $stmt = $db->prepare("SELECT qc.*, e.nombre, e.apellido, e.codigo_empleado 
                             FROM qr_codes qc 
                             JOIN empleados e ON qc.empleado_id = e.id 
                             WHERE qc.token = ? AND qc.usado = 0 AND qc.fecha_expiracion > NOW()");
        $stmt->execute([$qr_data['token']]);
        $qr_valido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$qr_valido) {
            http_response_code(400);
            echo json_encode(['error' => 'QR inválido, expirado o ya utilizado']);
            exit;
        }
        
        // Determinar tipo de registro (entrada/salida)
        $stmt = $db->prepare("SELECT tipo_registro FROM asistencia 
                             WHERE empleado_id = ? 
                             ORDER BY fecha_hora DESC 
                             LIMIT 1");
        $stmt->execute([$qr_valido['empleado_id']]);
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
            $qr_valido['empleado_id'], 
            $qr_data['token'], 
            $tipo_registro, 
            $ip_address, 
            $user_agent
        ]);
        
        // Marcar QR como usado
        $stmt = $db->prepare("UPDATE qr_codes SET usado = 1 WHERE id = ?");
        $stmt->execute([$qr_valido['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => "Registro de $tipo_registro exitoso",
            'empleado' => [
                'nombre' => $qr_valido['nombre'],
                'apellido' => $qr_valido['apellido'],
                'codigo_empleado' => $qr_valido['codigo_empleado']
            ],
            'tipo_registro' => $tipo_registro,
            'fecha_hora' => date('Y-m-d H:i:s')
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
