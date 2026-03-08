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
    
    // Verificar autenticación básica
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['format']) || !isset($data['start_date']) || !isset($data['end_date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan parámetros requeridos']);
        exit;
    }
    
    $format = $data['format']; // 'excel' o 'pdf'
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];
    $employee_id = $data['employee_id'] ?? null;
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Construir consulta SQL
        $sql = "
            SELECT 
                e.codigo_empleado,
                e.nombre,
                e.apellido,
                e.departamento,
                a.tipo_registro,
                a.fecha_hora,
                a.ip_address,
                DATE(a.fecha_hora) as fecha,
                TIME(a.fecha_hora) as hora
            FROM asistencia a
            INNER JOIN empleados e ON a.empleado_id = e.id
            WHERE DATE(a.fecha_hora) BETWEEN :start_date AND :end_date
        ";
        
        $params = [
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ];
        
        if ($employee_id) {
            $sql .= " AND a.empleado_id = :employee_id";
            $params[':employee_id'] = $employee_id;
        }
        
        $sql .= " ORDER BY a.fecha_hora DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($records)) {
            echo json_encode(['error' => 'No hay registros en el rango de fechas seleccionado']);
            exit;
        }
        
        // Generar reporte según formato
        if ($format === 'excel') {
            $filename = generateExcelReport($records, $start_date, $end_date);
        } elseif ($format === 'pdf') {
            $filename = generatePDFReport($records, $start_date, $end_date);
        } else {
            echo json_encode(['error' => 'Formato no válido']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'filename' => $filename,
            'download_url' => "reports/$filename",
            'total_records' => count($records)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al generar reporte: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}

function generateExcelReport($records, $start_date, $end_date) {
    // Crear contenido CSV (Excel compatible)
    $filename = "reporte_asistencia_" . date('Y-m-d') . ".csv";
    $filepath = __DIR__ . "/../reports/" . $filename;
    
    // Asegurar que el directorio reports exista
    if (!is_dir(__DIR__ . "/../reports")) {
        mkdir(__DIR__ . "/../reports", 0777, true);
    }
    
    $file = fopen($filepath, 'w');
    
    // Encabezados CSV
    fputcsv($file, [
        'Código Empleado',
        'Nombre',
        'Apellido',
        'Departamento',
        'Tipo Registro',
        'Fecha',
        'Hora',
        'Dirección IP'
    ]);
    
    // Datos
    foreach ($records as $record) {
        fputcsv($file, [
            $record['codigo_empleado'],
            $record['nombre'],
            $record['apellido'],
            $record['departamento'],
            $record['tipo_registro'],
            $record['fecha'],
            $record['hora'],
            $record['ip_address']
        ]);
    }
    
    fclose($file);
    
    return $filename;
}

function generatePDFReport($records, $start_date, $end_date) {
    $filename = "reporte_asistencia_" . date('Y-m-d') . ".html";
    $filepath = __DIR__ . "/../reports/" . $filename;
    
    // Asegurar que el directorio reports exista
    if (!is_dir(__DIR__ . "/../reports")) {
        mkdir(__DIR__ . "/../reports", 0777, true);
    }
    
    // Generar HTML para PDF
    $html = generatePDFHTML($records, $start_date, $end_date);
    
    file_put_contents($filepath, $html);
    
    return $filename;
}

function generatePDFHTML($records, $start_date, $end_date) {
    $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; }
        .header p { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        .summary { background-color: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .summary h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reporte de Asistencia</h1>
        <p><strong>Periodo:</strong> ' . $start_date . ' al ' . $end_date . '</p>
        <p><strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '</p>
    </div>
    
    <div class="summary">
        <h3>📈 Resumen</h3>
        <p><strong>Total de registros:</strong> ' . count($records) . '</p>';
    
    // Contar entradas y salidas
    $entradas = 0;
    $salidas = 0;
    $empleados_unicos = [];
    
    foreach ($records as $record) {
        if ($record['tipo_registro'] === 'entrada') {
            $entradas++;
        } else {
            $salidas++;
        }
        $empleados_unicos[] = $record['codigo_empleado'];
    }
    
    $html .= '<p><strong>Total de entradas:</strong> ' . $entradas . '</p>';
    $html .= '<p><strong>Total de salidas:</strong> ' . $salidas . '</p>';
    $html .= '<p><strong>Empleados únicos:</strong> ' . count(array_unique($empleados_unicos)) . '</p>';
    
    $html .= '</div>
    
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Departamento</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($records as $record) {
        $html .= '<tr>
            <td>' . htmlspecialchars($record['codigo_empleado']) . '</td>
            <td>' . htmlspecialchars($record['nombre']) . '</td>
            <td>' . htmlspecialchars($record['apellido']) . '</td>
            <td>' . htmlspecialchars($record['departamento']) . '</td>
            <td>' . htmlspecialchars($record['tipo_registro']) . '</td>
            <td>' . htmlspecialchars($record['fecha']) . '</td>
            <td>' . htmlspecialchars($record['hora']) . '</td>
            <td>' . htmlspecialchars($record['ip_address']) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Reporte generado por Sistema de Asistencia QR</p>
        <p>Página ' . date('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';
    
    return $html;
}
?>
