<?php
// Configuración de la base de datos para producción
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $db_type;
    private $conn;

    public function __construct() {
        // Detectar si estamos en producción (Render) o desarrollo local
        if (getenv('DB_HOST') || getenv('DB_TYPE') === 'postgresql') {
            // Configuración para Render (producción) - Forzar PostgreSQL
            $this->host = getenv('DB_HOST') ?: 'localhost';
            $this->db_name = getenv('DB_NAME') ?: 'registro_asistencia';
            $this->username = getenv('DB_USER') ?: 'postgres';
            $this->password = getenv('DB_PASSWORD') ?: '';
            $this->db_type = 'postgresql'; // Forzar PostgreSQL en producción
        } else {
            // Configuración para desarrollo local
            $this->host = 'localhost';
            $this->db_name = 'registro_asistencia';
            $this->username = 'root';
            $this->password = '';
            $this->db_type = 'mysql';
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            if ($this->db_type === 'postgresql') {
                // Verificar que el driver de PostgreSQL esté disponible
                if (!in_array('pgsql', PDO::getAvailableDrivers())) {
                    throw new PDOException("Driver PostgreSQL no disponible");
                }
                
                // Conexión PostgreSQL
                $dsn = "pgsql:host={$this->host};port=5432;dbname={$this->db_name};user={$this->username};password={$this->password}";
                error_log("Intentando conectar PostgreSQL: " . str_replace($this->password, "***", $dsn));
                $this->conn = new PDO($dsn);
                error_log("Conexión PostgreSQL exitosa");
            } else {
                // Verificar que el driver de MySQL esté disponible
                if (!in_array('mysql', PDO::getAvailableDrivers())) {
                    throw new PDOException("Driver MySQL no disponible");
                }
                
                // Conexión MySQL
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                error_log("Intentando conectar MySQL: " . $dsn);
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("set names utf8");
                error_log("Conexión MySQL exitosa");
            }
        } catch(PDOException $exception) {
            // Si falla la conexión, mostrar error amigable
            error_log("Error de conexión: " . $exception->getMessage());
            error_log("Drivers disponibles: " . implode(', ', PDO::getAvailableDrivers()));
            error_log("Variables de entorno - DB_HOST: " . (getenv('DB_HOST') ?: 'NO DEFINIDO'));
            error_log("Variables de entorno - DB_TYPE: " . (getenv('DB_TYPE') ?: 'NO DEFINIDO'));
            error_log("Variables de entorno - DB_NAME: " . (getenv('DB_NAME') ?: 'NO DEFINIDO'));
            
            // En producción, no mostrar el error al usuario
            if (getenv('DB_HOST')) {
                $this->conn = null;
            } else {
                echo "Error de conexión: " . $exception->getMessage();
            }
        }
        
        return $this->conn;
    }
    
    // Método para verificar si hay conexión
    public function isConnected() {
        return $this->conn !== null;
    }
    
    // Método para crear tablas si no existen
    public function createTables() {
        if (!$this->conn) return false;
        
        try {
            if ($this->db_type === 'postgresql') {
                // Crear tablas para PostgreSQL
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
            } else {
                // Usar el archivo SQL para MySQL
                if (file_exists(__DIR__ . '/../database.sql')) {
                    $sql = file_get_contents(__DIR__ . '/../database.sql');
                    $this->conn->exec($sql);
                }
            }
            return true;
        } catch(Exception $e) {
            error_log("Error creando tablas: " . $e->getMessage());
            return false;
        }
    }
}
?>
