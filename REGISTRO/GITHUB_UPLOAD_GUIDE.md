# 📋 Guía Rápida - Subir a GitHub

## 🎯 **ARCHIVOS QUE DEBES SUBIR**

### **✅ Archivos Principales (Raíz):**
```
✅ Dockerfile                 ← ¡IMPORTANTE!
✅ render.yaml              ← ¡IMPORTANTE!
✅ .htaccess                 ← ¡IMPORTANTE!
✅ .gitignore               ← ¡IMPORTANTE!
✅ index.html
✅ scanner.html
✅ static-qr.html
✅ reports.html
✅ README_CLOUD.md
```

### **✅ Carpetas Completas:**
```
✅ admin/     (Panel de administración)
✅ api/       (APIs del sistema)
✅ assets/    (CSS, JS, imágenes)
✅ config/    (Configuración BD)
✅ reports/   (Sistema de reportes)
```

---

## 🚀 **PASOS PARA SUBIR**

### **📋 Paso 1: Crear Repositorio**
1. **Ve a:** https://github.com
2. **Click en:** "New repository"
3. **Nombre:** `asistencia-qr`
4. **Descripción:** `Sistema de Asistencia QR - PHP + PostgreSQL`
5. **Visibilidad:** Public o Private
6. **NO marcar:** "Add README file"
7. **Click en:** "Create repository"

### **📋 Paso 2: Subir Archivos**
1. **En tu repositorio**, click "Add file" → "Upload files"
2. **Arrastra o selecciona** todos los archivos listados arriba
3. **Sube las carpetas completas** (admin/, api/, assets/, config/, reports/)
4. **Commit message:** `🚀 Initial commit - Sistema de Asistencia QR`
5. **Click en:** "Commit changes"

---

## 🔍 **VERIFICACIÓN**

### **📋 En GitHub deberías ver:**
```
📁 asistencia-qr/
├── 📄 Dockerfile
├── 📄 render.yaml
├── 📄 .htaccess
├── 📄 .gitignore
├── 📄 index.html
├── 📄 scanner.html
├── 📄 static-qr.html
├── 📄 reports.html
├── 📄 README_CLOUD.md
├── 📁 admin/
├── 📁 api/
├── 📁 assets/
├── 📁 config/
└── 📁 reports/
```

---

## 🚀 **DESPUÉS DE SUBIR A GITHUB**

### **📋 Paso 3: Configurar Render**
1. **Ve a:** https://render.com
2. **Regístrate** con GitHub
3. **Click en:** "New" → "Web Service"
4. **Conecta tu repositorio:** `asistencia-qr`
5. **Configura:**
   - **Name:** `asistencia-qr`
   - **Runtime:** Docker
   - **Dockerfile path:** `./Dockerfile`
   - **Instance Type:** Free
6. **Click en:** "Create Web Service"

### **📋 Paso 4: Crear Base de Datos**
1. **Cuando Render pregunte:** Click "Yes, create PostgreSQL database"
2. **Configura BD:**
   - **Name:** `asistencia-qr-db`
   - **Plan:** Free
3. **Click en:** "Create Database"

---

## ⏱️ **RESULTADO FINAL**

### **🌐 URLs Públicas (después de 5-8 minutos):**
```
🏠 Sistema: https://asistencia-qr.onrender.com
📈 Admin:   https://asistencia-qr.onrender.com/admin/
📷 Scanner: https://asistencia-qr.onrender.com/scanner.html
🎯 QR Estático: https://asistencia-qr.onrender.com/static-qr.html
📊 Reportes: https://asistencia-qr.onrender.com/reports.html
```

### **🔑 Credenciales:**
```
Panel Admin:
Usuario: admin
Contraseña: admin123
```

---

## 🎯 **CHECKLIST FINAL**

### **✅ Antes de subir:**
- [ ] **Tener cuenta GitHub**
- [ ] **Tener todos los archivos listados**
- [ ] **Tener carpetas completas**

### **✅ Después de subir:**
- [ ] **Repositorio creado** en GitHub
- [ ] **Archivos subidos** correctamente
- [ ] **Render conectado** a GitHub
- [ ] **Base de datos creada**
- [ ] **Deploy completado**

---

## 🆘 **PROBLEMAS COMUNES**

### **❌ Si el build falla:**
1. **Verifica que Dockerfile esté subido**
2. **Verifica que render.yaml esté subido**
3. **Revisa los logs en Render**

### **❌ Si la BD no conecta:**
1. **Espera 2-3 minutos** después de crearla
2. **Verifica que el servicio esté "Live"**

### **❌ Si las URLs no funcionan:**
1. **Espera a que esté "Live"** en Render
2. **Refresca la página** después de 1 minuto

---

## 🎉 **¡LISTO PARA COMPARTIR!**

### **✅ En menos de 30 minutos tendrás:**
- **Sistema web funcional** en internet
- **URL permanente** para compartir
- **Base de datos robusta** gratuita
- **Panel de administración** completo
- **Deploy automático** futuro

---

**🚀 ¡Tu Sistema de Asistencia QR estará público y funcional en minutos!**

**📋 Sigue esta guía y tendrás tu aplicación web funcionando profesionalmente en internet.**
