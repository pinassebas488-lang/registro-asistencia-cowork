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
    session_start();
    
    // Para el frontend de reportes, no requerimos autenticación
    // Solo para acciones administrativas requerimos login
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no especificada']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        switch ($data['action']) {
            case 'list_all':
                // Listar todos los empleados activos
                $stmt = $db->prepare("
                    SELECT id, codigo_empleado, nombre, apellido, email, departamento, activo 
                    FROM empleados 
                    WHERE activo = 1 
                    ORDER BY apellido, nombre
                ");
                $stmt->execute();
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'employees' => $employees
                ]);
                break;
                
            case 'get_stats':
                // Obtener estadísticas generales
                $stats = [];
                
                // Total empleados activos
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
                $stmt->execute();
                $stats['total_employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Entradas hoy
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total 
                    FROM asistencia 
                    WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'entrada'
                ");
                $stmt->execute();
                $stats['entries_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Salidas hoy
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total 
                    FROM asistencia 
                    WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'salida'
                ");
                $stmt->execute();
                $stats['exits_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total registros hoy
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total 
                    FROM asistencia 
                    WHERE DATE(fecha_hora) = CURDATE()
                ");
                $stmt->execute();
                $stats['total_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
                break;
                
            case 'create':
                // Crear nuevo empleado (requiere autenticación admin)
                if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                    http_response_code(401);
                    echo json_encode(['error' => 'No autorizado']);
                    exit;
                }
                
                if (!isset($data['codigo_empleado']) || !isset($data['nombre']) || !isset($data['apellido']) || !isset($data['email'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan datos requeridos']);
                    exit;
                }
                
                // Verificar si el código ya existe
                $stmt = $db->prepare("SELECT id FROM empleados WHERE codigo_empleado = ?");
                $stmt->execute([$data['codigo_empleado']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El código de empleado ya existe']);
                    exit;
                }
                
                // Verificar si el email ya existe
                $stmt = $db->prepare("SELECT id FROM empleados WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El email ya existe']);
                    exit;
                }
                
                // Insertar nuevo empleado
                $stmt = $db->prepare("
                    INSERT INTO empleados (codigo_empleado, nombre, apellido, email, departamento, activo) 
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $data['codigo_empleado'],
                    $data['nombre'],
                    $data['apellido'],
                    $data['email'],
                    $data['departamento'] ?? 'General'
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Empleado creado exitosamente',
                    'id' => $db->lastInsertId()
                ]);
                break;
                
            case 'update':
                // Actualizar empleado (requiere autenticación admin)
                if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                    http_response_code(401);
                    echo json_encode(['error' => 'No autorizado']);
                    exit;
                }
                
                if (!isset($data['id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de empleado requerido']);
                    exit;
                }
                
                // Verificar si el empleado existe
                $stmt = $db->prepare("SELECT id FROM empleados WHERE id = ?");
                $stmt->execute([$data['id']]);
                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Empleado no encontrado']);
                    exit;
                }
                
                // Actualizar empleado
                $stmt = $db->prepare("
                    UPDATE empleados 
                    SET codigo_empleado = ?, nombre = ?, apellido = ?, email = ?, departamento = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['codigo_empleado'],
                    $data['nombre'],
                    $data['apellido'],
                    $data['email'],
                    $data['departamento'] ?? 'General',
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Empleado actualizado exitosamente'
                ]);
                break;
                
            case 'toggle':
                // Activar/desactivar empleado (requiere autenticación admin)
                if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                    http_response_code(401);
                    echo json_encode(['error' => 'No autorizado']);
                    exit;
                }
                
                if (!isset($data['id']) || !isset($data['activo'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID y estado requeridos']);
                    exit;
                }
                
                $stmt = $db->prepare("UPDATE empleados SET activo = ? WHERE id = ?");
                $stmt->execute([$data['activo'], $data['id']]);
                
                $status = $data['activo'] ? 'activado' : 'desactivado';
                
                echo json_encode([
                    'success' => true,
                    'message' => "Empleado $status exitosamente"
                ]);
                break;
                
            case 'delete':
                // Eliminar empleado (requiere autenticación admin)
                if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                    http_response_code(401);
                    echo json_encode(['error' => 'No autorizado']);
                    exit;
                }
                
                if (!isset($data['id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de empleado requerido']);
                    exit;
                }
                
                // Primero eliminar registros de asistencia
                $stmt = $db->prepare("DELETE FROM asistencia WHERE empleado_id = ?");
                $stmt->execute([$data['id']]);
                
                // Luego eliminar empleado
                $stmt = $db->prepare("DELETE FROM empleados WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Empleado eliminado exitosamente'
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                break;
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
