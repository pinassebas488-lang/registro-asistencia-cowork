<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    // Verificar autenticación básica
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no especificada']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        switch ($data['action']) {
            case 'list_all':
                $stmt = $db->query("SELECT id, nombre, apellido, email, codigo_empleado, departamento, activo FROM empleados ORDER BY nombre, apellido");
                $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'empleados' => $empleados
                ]);
                break;
                
            case 'add_employee':
                // Validar datos requeridos
                $required_fields = ['nombre', 'apellido', 'email', 'codigo_empleado'];
                foreach ($required_fields as $field) {
                    if (empty($data[$field])) {
                        http_response_code(400);
                        echo json_encode(['error' => "El campo $field es requerido"]);
                        exit;
                    }
                }
                
                // Verificar si el email ya existe
                $stmt = $db->prepare("SELECT id FROM empleados WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El email ya está registrado']);
                    exit;
                }
                
                // Verificar si el código de empleado ya existe
                $stmt = $db->prepare("SELECT id FROM empleados WHERE codigo_empleado = ?");
                $stmt->execute([$data['codigo_empleado']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El código de empleado ya existe']);
                    exit;
                }
                
                // Insertar nuevo empleado
                $stmt = $db->prepare("INSERT INTO empleados (nombre, apellido, email, codigo_empleado, departamento) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nombre'],
                    $data['apellido'],
                    $data['email'],
                    strtoupper($data['codigo_empleado']),
                    $data['departamento'] ?? null
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Empleado agregado exitosamente',
                    'employee_id' => $db->lastInsertId()
                ]);
                break;
                
            case 'toggle_status':
                if (!isset($data['employee_id']) || !isset($data['activo'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de empleado y estado son requeridos']);
                    exit;
                }
                
                // Actualizar estado
                $stmt = $db->prepare("UPDATE empleados SET activo = ? WHERE id = ?");
                $stmt->execute([$data['activo'], $data['employee_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Estado actualizado exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Empleado no encontrado']);
                }
                break;
                
            case 'update_employee':
                if (!isset($data['employee_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de empleado requerido']);
                    exit;
                }
                
                $employee_id = $data['employee_id'];
                $updates = [];
                $params = [];
                
                // Campos actualizables
                $updatable_fields = ['nombre', 'apellido', 'email', 'codigo_empleado', 'departamento'];
                
                foreach ($updatable_fields as $field) {
                    if (isset($data[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (empty($updates)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No hay campos para actualizar']);
                    exit;
                }
                
                $params[] = $employee_id;
                
                $sql = "UPDATE empleados SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Empleado actualizado exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Empleado no encontrado o sin cambios']);
                }
                break;
                
            case 'delete_employee':
                if (!isset($data['employee_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de empleado requerido']);
                    exit;
                }
                
                // Verificar si tiene registros de asistencia
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM asistencia WHERE empleado_id = ?");
                $stmt->execute([$data['employee_id']]);
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($count > 0) {
                    // En lugar de eliminar, desactivar
                    $stmt = $db->prepare("UPDATE empleados SET activo = 0 WHERE id = ?");
                    $stmt->execute([$data['employee_id']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Empleado desactivado (tiene registros de asistencia)'
                    ]);
                } else {
                    // Eliminar completamente si no tiene registros
                    $stmt = $db->prepare("DELETE FROM empleados WHERE id = ?");
                    $stmt->execute([$data['employee_id']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Empleado eliminado exitosamente'
                    ]);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                break;
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
