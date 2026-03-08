-- Base de datos para Sistema de Control de Asistencia con QR Dinámico
CREATE DATABASE IF NOT EXISTS registro_asistencia;
USE registro_asistencia;

-- Tabla de empleados/usuarios
CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    codigo_empleado VARCHAR(50) UNIQUE NOT NULL,
    departamento VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de códigos QR dinámicos
CREATE TABLE qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id)
);

-- Tabla de registros de asistencia
CREATE TABLE asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    qr_token VARCHAR(255) NOT NULL,
    tipo_registro ENUM('entrada', 'salida') NOT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id),
    INDEX idx_empleado_fecha (empleado_id, fecha_hora),
    INDEX idx_qr_token (qr_token)
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parametro VARCHAR(100) NOT NULL,
    valor TEXT NOT NULL,
    descripcion TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuración inicial
INSERT INTO configuracion (parametro, valor, descripcion) VALUES
('qr_expiration_minutes', '5', 'Tiempo de expiración del QR en minutos'),
('allow_multiple_entries', 'false', 'Permitir múltiples registros de entrada sin salida'),
('work_start_time', '09:00', 'Hora de inicio de jornada laboral'),
('work_end_time', '18:00', 'Hora de fin de jornada laboral');

-- Insertar empleados de ejemplo
INSERT INTO empleados (nombre, apellido, email, codigo_empleado, departamento) VALUES
('Juan', 'Pérez', 'juan.perez@empresa.com', 'EMP001', 'TI'),
('María', 'González', 'maria.gonzalez@empresa.com', 'EMP002', 'RH'),
('Carlos', 'Rodríguez', 'carlos.rodriguez@empresa.com', 'EMP003', 'Ventas');
