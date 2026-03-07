# Sistema de Control de Asistencia con QR Dinámico

Un sistema completo para el control de asistencia mediante códigos QR dinámicos que generan tokens únicos y seguros para cada registro de entrada y salida.

## 🚀 Características

- **QR Dinámicos**: Códigos QR únicos que expiran en 5 minutos
- **Escaneo en Tiempo Real**: Interfaz de cámara para escanear códigos QR
- **Control de Entrada/Salida**: Registra automáticamente el tipo de asistencia
- **Panel de Administración**: Gestión de empleados y visualización de reportes
- **Seguridad**: Tokens únicos, validación de IP y registro de user agent
- **Responsive Design**: Funciona en dispositivos móviles y desktop
- **Base de Datos MySQL**: Almacenamiento persistente de registros

## 📋 Requisitos

- PHP 7.4+ con PDO
- MySQL 5.7+ o MariaDB 10.2+
- Servidor web (Apache/Nginx)
- Acceso a cámara en dispositivos móviles
- XAMPP (para desarrollo local en Windows)

## 🛠️ Instalación

### 1. Configurar Base de Datos

1. Inicia tu servidor MySQL (generalmente incluido en XAMPP)
2. Importa el archivo `database.sql`:
   ```bash
   mysql -u root -p < database.sql
   ```
   O usa phpMyAdmin:
   - Abre phpMyAdmin (http://localhost/phpmyadmin)
   - Crea una nueva base de datos llamada `registro_asistencia`
   - Importa el archivo `database.sql`

### 2. Configurar Archivos

1. Asegúrate que la carpeta esté en `htdocs/REGISTRO/` de XAMPP
2. Verifica la configuración en `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'registro_asistencia';
   private $username = 'root';
   private $password = ''; // Cambia si tienes contraseña
   ```

### 3. Iniciar Servidor

1. Inicia Apache desde el panel de control XAMPP
2. Accede al sistema: http://localhost/REGISTRO/

## 📱 Uso del Sistema

### Para Empleados

#### Generar Código QR
1. Ingresa tu código de empleado (ej: EMP001)
2. El sistema genera un QR único válido por 5 minutos
3. Muestra el QR al dispositivo de escaneo

#### Escanear Código QR
1. Accede a la sección "Escanear QR"
2. Permite el acceso a la cámara
3. Enfoca el código QR dentro del cuadro verde
4. El sistema registra automáticamente entrada/salida

### Para Administradores

#### Acceso al Panel
- URL: http://localhost/REGISTRO/admin/
- Usuario: `admin`
- Contraseña: `admin123`

#### Funciones Administrativas
- **Estadísticas**: Vista general de registros del día
- **Gestión de Empleados**: Agregar, activar/desactivar empleados
- **Reportes**: Consulta de últimos registros de asistencia
- **Configuración**: Ajustes del sistema (tiempo de expiración, reglas)

## 🏗️ Arquitectura del Sistema

```
REGISTRO/
├── 📁 assets/
│   ├── css/style.css          # Estilos del sistema
│   ├── js/qr-scanner.js       # Lógica de escaneo y generación QR
│   └── images/                # Recursos gráficos
├── 📁 config/
│   └── database.php           # Configuración de base de datos
├── 📁 api/
│   ├── generate_qr.php        # API para generar QR
│   └── register_attendance.php # API para registrar asistencia
├── 📁 admin/
│   ├── index.php              # Panel de administración
│   └── api/
│       └── employees.php      # API gestión empleados
├── 📄 index.html              # Página principal (generar QR)
├── 📄 scanner.html            # Página de escaneo
├── 📄 database.sql            # Estructura de base de datos
└── 📄 README.md               # Documentación
```

## 🗄️ Estructura de Base de Datos

### Tablas Principales

- **empleados**: Información de empleados
- **qr_codes**: Códigos QR generados y su estado
- **asistencia**: Registros de entrada/salida
- **configuracion**: Parámetros del sistema

### Relaciones
- `empleados` → `qr_codes` (1:N)
- `empleados` → `asistencia` (1:N)
- `qr_codes` → `asistencia` (1:N)

## 🔒 Seguridad

- **Tokens Únicos**: Cada QR contiene un token aleatorio de 64 caracteres
- **Tiempo de Expiración**: Los QR expiran automáticamente en 5 minutos
- **Uso Único**: Cada QR solo puede utilizarse una vez
- **Registro de IP**: Se almacena la dirección IP de cada registro
- **Validación**: Verificación de formato y existencia de empleados

## 📊 Reportes y Estadísticas

El panel de administración proporciona:
- Total de empleados activos
- Registros de asistencia del día
- Conteo de entradas y salidas
- Historial detallado con timestamps
- Filtros por empleado y fecha

## 🔄 Flujo de Trabajo

1. **Generación QR**: Empleado solicita QR con su código
2. **Validación**: Sistema verifica empleado activo
3. **Generación**: Crea token único con expiración
4. **Escaneo**: Dispositivo lee el código QR
5. **Procesamiento**: Sistema valida token y no expiración
6. **Registro**: Determina tipo (entrada/salida) y guarda
7. **Confirmación**: Muestra resultado al usuario

## 🛠️ Personalización

### Cambiar Tiempo de Expiración
```sql
UPDATE configuracion SET valor = '10' WHERE parametro = 'qr_expiration_minutes';
```

### Modificar Reglas de Entrada Múltiple
```sql
UPDATE configuracion SET valor = 'true' WHERE parametro = 'allow_multiple_entries';
```

### Agregar Nuevos Campos a Empleados
1. Modifica la tabla `empleados` en MySQL
2. Actualiza los formularios en `admin/index.php`
3. Modifica las APIs correspondientes

## 🐛 Solución de Problemas

### Problemas Comunes

**No se puede acceder a la cámara**
- Verifica permisos del navegador
- Asegúrate de usar HTTPS en producción
- Revisa que no otra app esté usando la cámara

**Error de conexión a base de datos**
- Verifica que MySQL esté corriendo
- Confirma credenciales en `config/database.php`
- Asegúrate que la base de datos exista

**QR no se genera**
- Verifica que el código de empleado exista
- Confirma que el empleado esté activo
- Revisa la conexión a la API

**El sistema no registra asistencia**
- Verifica que el QR no haya expirado
- Confirma que el QR no haya sido usado
- Revisa los logs de error del servidor

## 📱 Compatibilidad

- **Desktop**: Chrome, Firefox, Safari, Edge
- **Móvil**: Chrome Mobile, Safari iOS
- **Tablets**: Navegadores modernos con soporte de cámara
- **Sistemas**: Windows, macOS, Linux, iOS, Android

## 🚀 Mejoras Futuras

- [ ] Autenticación con JWT
- [ ] Reportes PDF exportables
- [ ] Notificaciones por email
- [ ] Integración con APIs externas
- [ ] Modo offline con sincronización
- [ ] Reconocimiento facial alternativo
- [ ] Geolocalización de registros

## 📝 Licencia

Este proyecto es de código abierto y puede ser utilizado y modificado libremente.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork del proyecto
2. Crea una rama feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## 📞 Soporte

Para soporte técnico o preguntas:
- Revisa la sección de solución de problemas
- Verifica los logs del servidor web
- Confirma la configuración de base de datos

---

**Desarrollado con ❤️ para gestión eficiente de asistencia**
