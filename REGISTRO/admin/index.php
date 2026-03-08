<?php
session_start();

// Simple authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciales inválidas';
    }
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Show login form without displaying credentials
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Administración</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            .login-container {
                max-width: 400px;
                margin: 50px auto;
                padding: 20px;
            }
            .login-form {
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #333;
            }
            .form-control {
                width: 100%;
                padding: 12px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.3s;
            }
            .form-control:focus {
                outline: none;
                border-color: #007bff;
            }
            .btn {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
            .alert {
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .alert-danger {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .security-info {
                background: #d1ecf1;
                color: #0c5460;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <main class="container">
            <div class="login-container">
                <div class="login-form">
                    <h2>🔐 Acceso Administrador</h2>
                    
                    <div class="security-info">
                        <strong>🔒 Zona Segura</strong><br>
                        Esta es una área restringida. Por favor ingresa tus credenciales para continuar.
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="form-group">
                            <label for="username">Usuario:</label>
                            <input type="text" id="username" name="username" class="form-control" required autocomplete="username">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                        </div>
                        
                        <button type="submit" class="btn">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Crear tablas si no existen (solo en producción)
if ($db && getenv('DB_HOST')) {
    $database->createTables();
}

// Verificar si hay conexión a la base de datos
if (!$db) {
    // Si no hay conexión, mostrar valores por defecto
    $total_empleados = 0;
    $registros_hoy = 0;
    $entradas_hoy = 0;
    $salidas_hoy = 0;
    $recent_records = [];
    
    // Mostrar mensaje de error amigable
    $db_error = "No hay conexión a la base de datos. El sistema está funcionando en modo demo.";
} else {
    // Get statistics
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
        $total_empleados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE()");
        $registros_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'entrada'");
        $entradas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo_registro = 'salida'");
        $salidas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $db_error = null;
    } catch(Exception $e) {
        // Si hay error en las consultas, mostrar valores por defecto
        $total_empleados = 0;
        $registros_hoy = 0;
        $entradas_hoy = 0;
        $salidas_hoy = 0;
        $recent_records = [];
        $db_error = "Error en las consultas de base de datos.";
    }
}

// Get recent records
if ($db && !$db_error) {
    try {
        $stmt = $db->query("
            SELECT e.nombre, e.apellido, e.codigo_empleado, a.tipo_registro, a.fecha_hora, a.ip_address 
            FROM asistencia a 
            INNER JOIN empleados e ON a.empleado_id = e.id 
            ORDER BY a.fecha_hora DESC 
            LIMIT 10
        ");
        $ultimos_registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        $ultimos_registros = [];
    }
} else {
    $ultimos_registros = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Asistencia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js para estadísticas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>📊 Panel de Administración</h1>
            <nav>
                <a href="../index.html" class="nav-link">🏠 QR Dinámico</a>
                <a href="../static-qr.html" class="nav-link">🎯 QR Estático</a>
                <a href="../scanner.html" class="nav-link">📷 Escanear QR</a>
                <a href="../reports.html" class="nav-link">📊 Reportes</a>
                <a href="index.php" class="nav-link active">📈 Administración</a>
                <a href="index.php?logout=true" class="nav-link" style="background: linear-gradient(135deg, #dc3545, #e74c3c);">🚪 Salir</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Estadísticas Principales -->
        <div class="card">
            <h2>📈 Estadísticas del Sistema</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">👥</div>
                    <h3><?php echo $total_empleados; ?></h3>
                    <p>Empleados Activos</p>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">📝</div>
                    <h3><?php echo $registros_hoy; ?></h3>
                    <p>Registros Hoy</p>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔼</div>
                    <h3><?php echo $entradas_hoy; ?></h3>
                    <p>Entradas Hoy</p>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔽</div>
                    <h3><?php echo $salidas_hoy; ?></h3>
                    <p>Salidas Hoy</p>
                </div>
            </div>

            <!-- Gráfico de Actividad -->
            <div style="margin-top: 3rem;">
                <h3 style="margin-bottom: 1.5rem; color: #333;">📊 Actividad Reciente</h3>
                
                <?php if ($db_error): ?>
                    <div style="background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                        <strong>⚠️ Aviso:</strong> <?php echo $db_error; ?>
                        <br><br>
                        <small>El sistema está funcionando en modo demo. Para habilitar todas las funcionalidades, configura la base de datos.</small>
                    </div>
                <?php endif; ?>
                
                <canvas id="activityChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Últimos Registros -->
        <div class="card" style="margin-top: 2rem;">
            <h2>🕐 Últimos Registros de Asistencia</h2>
            
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>👤 Empleado</th>
                            <th>🆔 Código</th>
                            <th>📝 Tipo</th>
                            <th>🕐 Fecha y Hora</th>
                            <th>🌐 IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimos_registros)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #666;">
                                    <?php if ($db_error): ?>
                                        📊 No hay registros disponibles. El sistema está funcionando en modo demo.
                                    <?php else: ?>
                                        📊 No hay registros de asistencia aún.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimos_registros as $registro): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 1.2rem;">👤</span>
                                            <span><?php echo htmlspecialchars($registro['nombre'] . ' ' . $registro['apellido']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                                            <?php echo htmlspecialchars($registro['codigo_empleado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $registro['tipo_registro'] === 'entrada' ? 'success' : 'warning'; ?>">
                                            <?php echo $registro['tipo_registro'] === 'entrada' ? '🔼 ENTRADA' : '🔽 SALIDA'; ?>
                                        </span>
                                    </td>
                                    <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>🕐</span>
                                        <span><?php echo date('d/m/Y H:i:s', strtotime($registro['fecha_hora'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span>🌐</span>
                                            <span><?php echo htmlspecialchars($registro['ip_address']); ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gestión de Empleados -->
        <div class="card" style="margin-top: 2rem;">
            <h2>👥 Gestión de Empleados</h2>
            
            <div style="margin-bottom: 2rem;">
                <button onclick="showAddEmployeeForm()" class="btn btn-primary">
                    ➕ Agregar Empleado
                </button>
                <button onclick="refreshData()" class="btn btn-secondary">
                    🔄 Actualizar Datos
                </button>
                <button onclick="exportData()" class="btn btn-success">
                    📊 Exportar Reporte
                </button>
            </div>
            
            <div id="employeeForm" style="display: none; margin-bottom: 2rem;">
                <div class="card" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                    <h3>➕ Nuevo Empleado</h3>
                    <form id="addEmployeeForm">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label>👤 Nombre:</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>👤 Apellido:</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>📧 Email:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>🆔 Código:</label>
                                <input type="text" name="codigo_empleado" class="form-control" pattern="[A-Z]{3}[0-9]{3,}" required>
                            </div>
                            <div class="form-group">
                                <label>🏢 Departamento:</label>
                                <input type="text" name="departamento" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" class="btn btn-success">💾 Guardar</button>
                                    <button type="button" onclick="hideAddEmployeeForm()" class="btn btn-secondary">❌ Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>🆔 ID</th>
                            <th>👤 Nombre</th>
                            <th>📧 Email</th>
                            <th>🆔 Código</th>
                            <th>🏢 Departamento</th>
                            <th>📊 Estado</th>
                            <th>⚙️ Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTable">
                        <?php
                        $stmt = $db->query("SELECT * FROM empleados ORDER BY nombre, apellido");
                        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($empleados as $empleado):
                        ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                                        #<?php echo $empleado['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.2rem;">👤</span>
                                        <span><?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>📧</span>
                                        <span><?php echo htmlspecialchars($empleado['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: white;">
                                        <?php echo htmlspecialchars($empleado['codigo_empleado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>🏢</span>
                                        <span><?php echo htmlspecialchars($empleado['departamento'] ?: 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $empleado['activo'] ? 'success' : 'danger'; ?>">
                                        <?php echo $empleado['activo'] ? '✅ Activo' : '❌ Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="toggleEmployee(<?php echo $empleado['id']; ?>, <?php echo $empleado['activo'] ? 'false' : 'true'; ?>)" 
                                            class="btn btn-sm btn-<?php echo $empleado['activo'] ? 'warning' : 'success'; ?>">
                                        <?php echo $empleado['activo'] ? '⏸️ Desactivar' : '▶️ Activar'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Gráfico de actividad
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['6:00', '9:00', '12:00', '15:00', '18:00', '21:00'],
                datasets: [{
                    label: 'Entradas',
                    data: [2, 8, 15, 12, 18, 5],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Salidas',
                    data: [1, 3, 8, 10, 15, 8],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        function showAddEmployeeForm() {
            document.getElementById('employeeForm').style.display = 'block';
        }
        
        function hideAddEmployeeForm() {
            document.getElementById('employeeForm').style.display = 'none';
            document.getElementById('addEmployeeForm').reset();
        }
        
        function refreshData() {
            location.reload();
        }
        
        function exportData() {
            // Mostrar diálogo de exportación
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            modal.innerHTML = `
                <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
                    <h3 style="margin-top: 0; color: #333;">📊 Exportar Reporte</h3>
                    
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fecha Inicio:</label>
                        <input type="date" id="startDate" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fecha Fin:</label>
                        <input type="date" id="endDate" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Formato:</label>
                        <select id="exportFormat" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="excel">📊 Excel (CSV)</option>
                            <option value="pdf">📄 PDF (HTML)</option>
                        </select>
                    </div>
                    
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Empleado (opcional):</label>
                        <select id="employeeFilter" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Todos los empleados</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button onclick="this.closest('.modal').remove()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
                        <button onclick="generateReport()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Generar Reporte</button>
                    </div>
                </div>
            `;
            
            modal.className = 'modal';
            document.body.appendChild(modal);
            
            // Establecer fechas por defecto (últimos 7 días)
            const today = new Date();
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('startDate').value = weekAgo.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
            
            // Cargar empleados
            loadEmployeesForExport();
        }
        
        function loadEmployeesForExport() {
            fetch('api/employees.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list_all' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('employeeFilter');
                    data.employees.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = `${emp.codigo_empleado} - ${emp.nombre} ${emp.apellido}`;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error cargando empleados:', error));
        }
        
        function generateReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const format = document.getElementById('exportFormat').value;
            const employeeId = document.getElementById('employeeFilter').value;
            
            if (!startDate || !endDate) {
                alert('Por favor selecciona ambas fechas');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('La fecha de inicio no puede ser mayor que la fecha de fin');
                return;
            }
            
            // Mostrar loading
            const button = event.target;
            button.textContent = 'Generando...';
            button.disabled = true;
            
            fetch('api/export_reports.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    format: format,
                    employee_id: employeeId || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Descargar archivo
                    const link = document.createElement('a');
                    link.href = `reports/${data.filename}`;
                    link.download = data.filename;
                    link.click();
                    
                    alert(`✅ Reporte generado exitosamente\n📊 Total de registros: ${data.total_records}\n📁 Archivo: ${data.filename}`);
                    
                    // Cerrar modal
                    document.querySelector('.modal').remove();
                } else {
                    alert('❌ Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error de conexión al generar el reporte');
            })
            .finally(() => {
                button.textContent = 'Generar Reporte';
                button.disabled = false;
            });
        }
        
        function toggleEmployee(id, activo) {
            if (confirm('¿Estás seguro de cambiar el estado de este empleado?')) {
                fetch('api/employees.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'toggle_status',
                        employee_id: id,
                        activo: activo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error de conexión: ' + error.message);
                });
            }
        }
        
        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            data.action = 'add_employee';
            
            fetch('api/employees.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Empleado agregado exitosamente');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error de conexión: ' + error.message);
            });
        });
    </script>
</body>
</html>
