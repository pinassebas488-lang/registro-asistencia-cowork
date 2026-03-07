<?php
/**
 * Configuración de Base de Datos para Nube (Render)
 * Compatible con PostgreSQL y MySQL
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $db_type;

    public function __construct() {
        // Configuración para Render (variables de entorno)
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'asistencia_qr';
        $this->username = getenv('DB_USER') ?: 'postgres';
        $this->password = getenv('DB_PASSWORD') ?: '';
        
        // Determinar tipo de base de datos
        $this->db_type = getenv('DB_TYPE') ?: 'mysql';
        
        // Para Render PostgreSQL
        if (strpos($this->host, '.render.com') !== false) {
            $this->db_type = 'postgresql';
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            if ($this->db_type === 'postgresql') {
                // Conexión PostgreSQL para Render
                $dsn = "pgsql:host={$this->host};port=5432;dbname={$this->db_name};user={$this->username};password={$this->password}";
                $this->conn = new PDO($dsn);
            } else {
                // Conexión MySQL para desarrollo local
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            }
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Crear tablas si no existen (compatible con PostgreSQL)
     */
    public function createTables() {
        $conn = $this->getConnection();
        
        if ($this->db_type === 'postgresql') {
            return $this->createPostgreSQLTables($conn);
        } else {
            return $this->createMySQLTables($conn);
        }
    }

    private function createPostgreSQLTables($conn) {
        $queries = [
            // Tabla empleados
            "CREATE TABLE IF NOT EXISTS empleados (
                id SERIAL PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                codigo_empleado VARCHAR(50) UNIQUE NOT NULL,
                departamento VARCHAR(50),
                activo BOOLEAN DEFAULT TRUE,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Tabla asistencia
            "CREATE TABLE IF NOT EXISTS asistencia (
                id SERIAL PRIMARY KEY,
                empleado_id INTEGER NOT NULL REFERENCES empleados(id),
                qr_token VARCHAR(255) NOT NULL,
                tipo_registro VARCHAR(10) CHECK (tipo_registro IN ('entrada', 'salida')),
                fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT
            )",
            
            // Tabla configuracion
            "CREATE TABLE IF NOT EXISTS configuracion (
                id SERIAL PRIMARY KEY,
                clave VARCHAR(100) UNIQUE NOT NULL,
                valor TEXT,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Tabla qr_codes
            "CREATE TABLE IF NOT EXISTS qr_codes (
                id SERIAL PRIMARY KEY,
                empleado_id INTEGER REFERENCES empleados(id),
                token VARCHAR(255) UNIQUE NOT NULL,
                tipo VARCHAR(20) DEFAULT 'dinamico',
                fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_expiracion TIMESTAMP,
                activo BOOLEAN DEFAULT TRUE,
                usos INTEGER DEFAULT 0,
                max_usos INTEGER DEFAULT 1
            )"
        ];

        foreach ($queries as $query) {
            try {
                $conn->exec($query);
            } catch(PDOException $exception) {
                // La tabla ya existe, continuar
            }
        }
    }

    private function createMySQLTables($conn) {
        // Usar el archivo database.sql existente
        if (file_exists(__DIR__ . '/../database.sql')) {
            $sql = file_get_contents(__DIR__ . '/../database.sql');
            $conn->exec($sql);
        }
    }
}
?>
