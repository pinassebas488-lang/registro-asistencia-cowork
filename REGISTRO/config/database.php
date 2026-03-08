<?php
/**
 * Configuración de Base de Datos para Sistema de Asistencia QR
 * Compatible con Render PostgreSQL y desarrollo local MySQL
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $db_type;
    private $conn;

    public function __construct() {
        // Detectar automáticamente el entorno
        if (getenv('RENDER') === 'verdadero' || getenv('RENDER') === 'true') {
            // Configuración para Render (producción)
            $this->host = getenv('HOST_DE_BASE_DE_DATOS') ?: getenv('DB_HOST') ?: 'localhost';
            $this->db_name = getenv('NOMBRE_BD') ?: getenv('DB_NAME') ?: 'registro_asistencia';
            $this->username = getenv('DB_USER') ?: 'postgres';
            $this->password = getenv('CONTRASEÑA_DE_LA_BASE_DE_DATOS') ?: getenv('DB_PASSWORD') ?: '';
            $this->db_type = 'postgresql';
            
            error_log("Database: Configuración Render - Host: " . $this->host . ", DB: " . $this->db_name);
        } else {
            // Configuración para desarrollo local
            $this->host = 'localhost';
            $this->db_name = 'registro_asistencia';
            $this->username = 'root';
            $this->password = '';
            $this->db_type = 'mysql';
            
            error_log("Database: Configuración Local - Host: " . $this->host . ", DB: " . $this->db_name);
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            if ($this->db_type === 'postgresql') {
                // Verificar disponibilidad del driver PostgreSQL
                if (!in_array('pgsql', PDO::getAvailableDrivers())) {
                    throw new PDOException("Driver PostgreSQL no disponible. Drivers: " . implode(', ', PDO::getAvailableDrivers()));
                }
                
                // Conexión PostgreSQL
                $dsn = "pgsql:host={$this->host};port=5432;dbname={$this->db_name};user={$this->username};password={$this->password}";
                $this->conn = new PDO($dsn);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                error_log("Database: Conexión PostgreSQL exitosa");
                
            } else {
                // Verificar disponibilidad del driver MySQL
                if (!in_array('mysql', PDO::getAvailableDrivers())) {
                    throw new PDOException("Driver MySQL no disponible. Drivers: " . implode(', ', PDO::getAvailableDrivers()));
                }
                
                // Conexión MySQL
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("set names utf8");
                error_log("Database: Conexión MySQL exitosa");
            }
            
        } catch(PDOException $exception) {
            error_log("Database Error: " . $exception->getMessage());
            error_log("Database Drivers: " . implode(', ', PDO::getAvailableDrivers()));
            
            // En producción, devolver null en lugar de mostrar error
            if (getenv('RENDER')) {
                $this->conn = null;
            } else {
                echo "Error de conexión: " . $exception->getMessage();
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Verificar si hay conexión activa
     */
    public function isConnected() {
        return $this->conn !== null;
    }
    
    /**
     * Crear tablas automáticamente según el tipo de base de datos
     */
    public function createTables() {
        if (!$this->conn) return false;
        
        try {
            if ($this->db_type === 'postgresql') {
                return $this->createPostgreSQLTables();
            } else {
                return $this->createMySQLTables();
            }
        } catch(Exception $e) {
            error_log("Database: Error creando tablas: " . $e->getMessage());
            return false;
        }
    }
    
    private function createPostgreSQLTables() {
        // Tabla empleados
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS empleados (
                id SERIAL PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                codigo_empleado VARCHAR(50) UNIQUE NOT NULL,
                departamento VARCHAR(50),
                activo BOOLEAN DEFAULT TRUE,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
        
        // Tabla asistencia
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS asistencia (
                id SERIAL PRIMARY KEY,
                empleado_id INTEGER NOT NULL REFERENCES empleados(id),
                qr_token VARCHAR(255) NOT NULL,
                tipo_registro VARCHAR(10) CHECK (tipo_registro IN ('entrada', 'salida')),
                fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT
            );
        ");
        
        // Tabla configuración
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS configuracion (
                id SERIAL PRIMARY KEY,
                clave VARCHAR(100) UNIQUE NOT NULL,
                valor TEXT,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
        
        // Insertar configuración inicial
        $this->conn->exec("
            INSERT INTO configuracion (clave, valor) VALUES
            ('qr_expiration_minutes', '5'),
            ('allow_multiple_entries', 'false'),
            ('work_start_time', '09:00'),
            ('work_end_time', '18:00')
            ON CONFLICT (clave) DO NOTHING;
        ");
        
        return true;
    }
    
    private function createMySQLTables() {
        // Usar el archivo SQL si existe
        if (file_exists(__DIR__ . '/../database.sql')) {
            $sql = file_get_contents(__DIR__ . '/../database.sql');
            $this->conn->exec($sql);
            return true;
        }
        return false;
    }
}
?>
