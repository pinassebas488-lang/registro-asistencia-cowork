# 🚀 Sistema de Asistencia QR - Producción

## 📋 Descripción
Sistema completo de control de asistencia mediante códigos QR dinámicos, optimizado para despliegue en Render con base de datos PostgreSQL.

## 🌐 URLs del Sistema
- **Principal:** `https://registro-asistencia-cowork.onrender.com`
- **Panel Admin:** `https://registro-asistencia-cowork.onrender.com/admin/`
- **Escáner QR:** `https://registro-asistencia-cowork.onrender.com/scanner.html`
- **QR Estático:** `https://registro-asistencia-cowork.onrender.com/static-qr.html`
- **Reportes:** `https://registro-asistencia-cowork.onrender.com/reports.html`

## 🔑 Credenciales de Acceso
- **Panel Administrador:**
  - Usuario: `admin`
  - Contraseña: `admin123`

## 🏗️ Arquitectura del Sistema

### 📁 Estructura de Archivos
```
REGISTRO/
├── 📄 Dockerfile              ← Configuración Docker optimizada
├── 📄 .htaccess             ← Configuración Apache para producción
├── 📄 .gitignore            ← Archivos ignorados en Git
├── 📄 index.html            ← Página principal
├── 📄 scanner.html          ← Escáner de códigos QR
├── 📄 static-qr.html        ← Generador QR estático
├── 📄 reports.html          ← Sistema de reportes
├── 📄 database.sql          ← Estructura BD (MySQL local)
├── 📁 admin/                ← Panel de administración
│   ├── index.php            ← Dashboard principal
│   └── .htaccess           ← Seguridad del admin
├── 📁 api/                  ← APIs del sistema
│   ├── generate_qr.php     ← Generación QR dinámico
│   ├── scan_qr.php         ← Procesamiento de escaneo
│   └── attendance.php      ← Registro de asistencia
├── 📁 assets/               ← Recursos estáticos
│   ├── css/                ← Estilos
│   ├── js/                 ← JavaScript
│   └── images/             ← Imágenes
├── 📁 config/               ← Configuración
│   └── database.php         ← Conexión BD universal
└── 📁 reports/              ← Sistema de reportes
    └── generate.php         ← Generación de reportes
```

## 🔧 Configuración Técnica

### 🐳 Dockerfile Optimizado
- **Base:** PHP 8.2 con Apache
- **Extensiones:** MySQL, PostgreSQL, ZIP
- **Seguridad:** Permisos configurados
- **Rendimiento:** Optimizado para producción

### 🗄️ Base de Datos
- **Producción:** PostgreSQL en Render
- **Desarrollo:** MySQL local (XAMPP)
- **Detección automática** del entorno

### 🌐 Configuración Apache
- **Reescritura URL:** Activada
- **Compresión Gzip:** Habilitada
- **Caché estática:** Configurada
- **Headers de seguridad:** Implementados

## 🚀 Despliegue en Render

### 📋 Requisitos Previos
1. **Cuenta en Render** con plan gratuito
2. **Repositorio GitHub** con el código
3. **Base de datos PostgreSQL** configurada

### 🎯 Pasos de Despliegue

#### 1. Crear Base de Datos
1. **Dashboard Render** → "New" → "PostgreSQL"
2. **Configurar:**
   - Name: `registro-asistencia-cowork-db`
   - Database: `registro_asistencia`
   - Region: Oregon (US West)
   - Plan: Free

#### 2. Configurar Variables de Entorno
En tu Web Service, agregar:
```
DB_TYPE=postgresql
DB_HOST=tu-host-postgresql.render.com
DB_NAME=registro_asistencia
DB_USER=postgres
DB_PASSWORD=tu-contraseña-generada
```

#### 3. Desplegar Aplicación
1. **"New Web Service"** → Conectar GitHub
2. **Configurar:**
   - Name: `registro-asistencia-cowork`
   - Root Directory: (vacío)
   - Dockerfile Path: `./Dockerfile`
   - Instance Type: Free
   - Region: Oregon (US West)

## 📊 Funcionalidades del Sistema

### ✅ Características Principales
- **Generación QR dinámico** con expiración
- **Escaneo QR** con cámara o archivo
- **Registro de asistencia** automático
- **Panel administrador** completo
- **Reportes detallados** exportables
- **Seguridad** y control de acceso

### 🔐 Seguridad Implementada
- **Autenticación** de administrador
- **Protección** archivos sensibles
- **Headers de seguridad** HTTP
- **Control de acceso** por IP
- **Validación** de tokens QR

### 📈 Métricas y Reportes
- **Asistencia diaria** por empleado
- **Estadísticas** de entrada/salida
- **Reportes** exportables (PDF, Excel)
- **Gráficos** interactivos
- **Filtros** por fecha y empleado

## 🛠️ Mantenimiento

### 📋 Tareas Regulares
- **Monitorear logs** de errores
- **Actualizar dependencias** de seguridad
- **Respaldar base** de datos
- **Verificar rendimiento** del sistema
- **Limpiar caché** periódicamente

### 🔍 Monitoreo
- **Logs de Apache:** `/var/log/apache2/`
- **Logs de PHP:** Configurados en error_log
- **Logs de aplicación:** En base de datos
- **Métricas:** Disponibles en panel admin

## 🆘 Soporte y Troubleshooting

### ❌ Problemas Comunes
1. **Error de conexión BD:** Verificar variables de entorno
2. **Driver no encontrado:** Revisar Dockerfile
3. **Permisos denegados:** Verificar .htaccess
4. **Build fallido:** Revisar sintaxis Dockerfile

### 🔧 Soluciones Rápidas
- **Reiniciar servicio:** En dashboard de Render
- **Verificar logs:** En sección "Logs" de Render
- **Debug local:** Usar archivos de desarrollo
- **Actualizar código:** Push a GitHub con auto-deploy

## 📞 Contacto y Soporte
- **Documentación:** README_PRODUCTION.md
- **Issues:** GitHub repository
- **Logs:** Render dashboard
- **Monitoreo:** Panel administrador

---

## 🎯 Checklist de Producción

### ✅ Antes del Despliegue
- [ ] Base de datos PostgreSQL creada
- [ ] Variables de entorno configuradas
- [ ] Dockerfile optimizado
- [ ] .htaccess configurado
- [ ] Archivos de desarrollo eliminados

### ✅ Después del Despliegue
- [ ] URLs funcionando correctamente
- [ ] Panel admin accesible
- [ ] Conexión BD estable
- [ ] Logs sin errores críticos
- [ ] Funcionalidades probadas

---

**🚀 Sistema listo para producción en Render con PostgreSQL y todas las funcionalidades operativas.**
