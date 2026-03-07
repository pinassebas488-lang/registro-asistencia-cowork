<?php
// API corregida para estadísticas del sistema
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $action = $_GET['action'] ?? 'get_stats';
    
    switch ($action) {
        case 'get_stats':
            // Estadísticas principales - CORREGIDAS
            $stats = [];
            
            // 1. Total empleados activos
            $stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
            $stats['total_empleados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 2. Total registros hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE()");
            $stats['registros_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 3. Entradas hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'entrada'");
            $stats['entradas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 4. Salidas hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'salida'");
            $stats['salidas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 5. Últimos 10 registros
            $stmt = $db->query("
                SELECT e.nombre, e.apellido, e.codigo_empleado, a.tipo_registro, a.fecha_hora, a.ip_address 
                FROM asistencia a 
                INNER JOIN empleados e ON a.empleado_id = e.id 
                ORDER BY a.fecha_hora DESC 
                LIMIT 10
            ");
            $stats['ultimos_registros'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 6. Estadísticas por día (últimos 7 días)
            $stmt = $db->query("
                SELECT 
                    DATE(fecha_hora) as fecha,
                    COUNT(*) as total,
                    COUNT(CASE WHEN tipo_registro = 'entrada' THEN 1 END) as entradas,
                    COUNT(CASE WHEN tipo_registro = 'salida' THEN 1 END) as salidas
                FROM asistencia 
                WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha_hora)
                ORDER BY fecha DESC
            ");
            $stats['estadisticas_semana'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 7. Empleados con actividad hoy
            $stmt = $db->query("
                SELECT DISTINCT e.id, e.nombre, e.apellido, e.codigo_empleado
                FROM empleados e 
                INNER JOIN asistencia a ON e.id = a.empleado_id 
                WHERE DATE(a.fecha_hora) = CURDATE() AND e.activo = 1
                ORDER BY e.nombre, e.apellido
            ");
            $stats['empleados_activos_hoy'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 8. Resumen por tipo
            $stmt = $db->query("
                SELECT 
                    tipo_registro,
                    COUNT(*) as total,
                    COUNT(DISTINCT empleado_id) as empleados_unicos
                FROM asistencia 
                WHERE DATE(fecha_hora) = CURDATE()
                GROUP BY tipo_registro
            ");
            $stats['resumen_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'debug_stats':
            // Información completa para depuración
            $debug = [];
            
            // Todos los empleados
            $stmt = $db->query("SELECT id, nombre, apellido, codigo_empleado, activo FROM empleados ORDER BY nombre");
            $debug['todos_empleados'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Todos los registros de hoy
            $stmt = $db->query("
                SELECT a.*, e.nombre, e.apellido, e.codigo_empleado 
                FROM asistencia a 
                LEFT JOIN empleados e ON a.empleado_id = e.id 
                WHERE DATE(a.fecha_hora) = CURDATE()
                ORDER BY a.fecha_hora DESC
            ");
            $debug['registros_hoy_completos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Relación completa
            $stmt = $db->query("
                SELECT 
                    e.id as emp_id,
                    e.nombre,
                    e.apellido,
                    e.codigo_empleado,
                    e.activo,
                    COUNT(a.id) as total_registros,
                    COUNT(CASE WHEN DATE(a.fecha_hora) = CURDATE() THEN 1 END) as registros_hoy,
                    COUNT(CASE WHEN a.tipo_registro = 'entrada' AND DATE(a.fecha_hora) = CURDATE() THEN 1 END) as entradas_hoy,
                    COUNT(CASE WHEN a.tipo_registro = 'salida' AND DATE(a.fecha_hora) = CURDATE() THEN 1 END) as salidas_hoy
                FROM empleados e 
                LEFT JOIN asistencia a ON e.id = a.empleado_id
                GROUP BY e.id, e.nombre, e.apellido, e.codigo_empleado, e.activo
                ORDER BY e.nombre
            ");
            $debug['relacion_completa'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'debug' => $debug,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'test_queries':
            // Prueba individual de cada consulta
            $tests = [];
            
            // Test 1: Empleados activos
            $stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
            $tests['empleados_activos'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Test 2: Registros hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE()");
            $tests['registros_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Test 3: Entradas hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'entrada'");
            $tests['entradas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Test 4: Salidas hoy
            $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'salida'");
            $tests['salidas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'tests' => $tests,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
