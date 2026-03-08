# 🚀 Instrucciones de Deploy para Render

## 📁 ESTRUCTURA CORRECTA

### ✅ Debe subir estos archivos y carpetas:

```
REGISTRO/
├── 📄 Dockerfile              ← Configuración Docker
├── 📄 .htaccess             ← Configuración Apache
├── 📄 .gitignore            ← Ignorar archivos innecesarios
├── 📄 index.html            ← Página principal
├── 📄 scanner.html          ← Escáner QR
├── 📄 static-qr.html        ← QR estático
├── 📄 reports.html          ← Reportes
├── 📄 database.sql          ← Estructura BD MySQL
├── 📄 debug_env.php         ← Debug de variables
├── 📁 admin/                ← Panel administración
├── 📁 api/                  ← APIs del sistema
├── 📁 assets/               ← CSS, JS, imágenes
├── 📁 config/               ← Configuración BD
└── 📁 reports/              ← Sistema reportes
```

## 🔧 CONFIGURACIÓN RENDER

### 📋 Web Service Settings:
- **Name:** `registro-asistencia-cowork`
- **Root Directory:** (dejar en blanco)
- **Dockerfile Path:** `./Dockerfile`
- **Instance Type:** Free

### 📋 Environment Variables:
```
DB_TYPE=postgresql
DB_HOST=tu-host-postgresql.render.com
DB_NAME=tu-base-de-datos
DB_USER=postgres
DB_PASSWORD=tu-contraseña-generada
```

## 🚀 PASOS DE DEPLOY

### 📋 1. Subir a GitHub:
1. **Abrir Git Bash** en `c:/xampp/htdocs/REGISTRO/`
2. **Ejecutar:**
   ```bash
   git add .
   git commit -m "Deploy fix - estructura correcta"
   git push origin main
   ```

### 📋 2. Deploy en Render:
1. **Ve a Render dashboard**
2. **Click en tu servicio**
3. **Click "Manual Deploy"**
4. **Selecciona "Deploy latest commit"**

## 🔍 DEBUG POST-DEPLOY

### 📋 Verificar URLs:
```
🔍 Debug:    https://registro-asistencia-cowork.onrender.com/debug_env.php
🏠 Sistema:   https://registro-asistencia-cowork.onrender.com
📈 Admin:     https://registro-asistencia-cowork.onrender.com/admin/
```

## 🆘 SOLUCIÓN DE ERRORES

### ❌ Si dice "/REGISTRO": not found:
- **Estás subiendo desde la carpeta incorrecta**
- **Debes subir desde `c:/xampp/htdocs/REGISTRO/`**
- **NO desde `c:/xampp/htdocs/`**

### ❌ Si falla el build:
- **Verifica Dockerfile** esté en raíz
- **Verifica .htaccess** esté en raíz
- **Revisa que no haya carpetas vacías**

### ❌ Si no hay conexión BD:
- **Ve a debug_env.php**
- **Verifica variables de entorno**
- **Revisa logs en Render**

## ✅ CHECKLIST FINAL

- [ ] Subiendo desde `c:/xampp/htdocs/REGISTRO/`
- [ ] Dockerfile en raíz
- [ ] .htaccess en raíz
- [ ] Todas las carpetas incluidas
- [ ] Variables de entorno configuradas
- [ ] Deploy exitoso
- [ ] URLs funcionando
