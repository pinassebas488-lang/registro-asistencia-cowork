# 🗄️ Crear Base de Datos PostgreSQL en Render

## 📋 PASOS PARA CREAR LA BD

### 🎯 Paso 1: Ir a Render Dashboard
1. **Ve a:** https://dashboard.render.com
2. **Inicia sesión** con tu cuenta

### 🎯 Paso 2: Crear Nueva Base de Datos
1. **Click en "New"** (botón superior derecho)
2. **Selecciona "PostgreSQL"**
3. **Configura los siguientes datos:**

#### 📝 Configuración:
- **Name:** `registro-asistencia-cowork-db`
- **Database Name:** `registro_asistencia`
- **User:** `postgres`
- **Region:** Oregon (US West) ← ¡MISMA REGIÓN!
- **Instance Type:** Free

### 🎯 Paso 3: Esperar Creación
1. **Espera 2-3 minutos** mientras se crea
2. **Verás las credenciales** cuando esté lista

### 🎯 Paso 4: Obtener Credenciales
1. **Copia estos datos:**
   - **Host:** (ej: dpg-xxxxx.oregon-postgres.render.com)
   - **Database:** `registro_asistencia`
   - **User:** `postgres`
   - **Password:** (contraseña generada)

### 🎯 Paso 5: Configurar Variables de Entorno
1. **Ve a tu Web Service:** `registro-asistencia-cowork`
2. **Click en "Environment"**
3. **Agrega estas variables:**

```
DB_TYPE=postgresql
DB_HOST=dpg-xxxxx.oregon-postgres.render.com
DB_NAME=registro_asistencia
DB_USER=postgres
DB_PASSWORD=tu-contraseña-generada
```

### 🎯 Paso 6: Guardar y Deploy
1. **Click en "Save Changes"**
2. **Click en "Manual Deploy"**
3. **Selecciona "Deploy Latest Commit"**

## 🔍 VERIFICACIÓN

### 📋 Después de configurar:
1. **Ve a:** https://registro-asistencia-cowork.onrender.com/debug_env.php
2. **Deberías ver:**
   - `DB_HOST: dpg-xxxxx.oregon-postgres.render.com`
   - `DB_NAME: registro_asistencia`
   - `DB_USER: postgres`
   - `DB_PASSWORD: ***DEFINIDO***`

## 🆘 TROUBLESHOOTING

### ❌ Si sigue diciendo "NO DEFINIDO":
1. **Verifica que hayas guardado** las variables
2. **Espera 2-3 minutos** después de guardar
3. **Haz deploy nuevo** después de configurar

### ❌ Si la conexión falla:
1. **Verifica el host** esté correcto
2. **Copia la contraseña** exactamente
3. **Espera 1-2 minutos** después de crear la BD

## ✅ CHECKLIST FINAL

- [ ] **Base de datos PostgreSQL creada**
- [ ] **Variables de entorno configuradas**
- [ ] **Misma región que el Web Service**
- [ ] **Deploy nuevo después de configurar**
- [ ] **debug_env.php muestra las variables**
- [ ] **Conexión PostgreSQL exitosa**
