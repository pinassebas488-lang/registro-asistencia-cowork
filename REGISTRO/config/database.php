<?php
/**
 * Configuración de la Base de Datos para Producción en Render
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
        $this->db_name = getenv('DB_NAME') ?: 'registro_asistencia';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        
        // Determinar tipo de base de datos
        $this->db_type = getenv('DB_TYPE') ?: 'mysql';
        
        // Para Render PostgreSQL
        if (strpos($this->host, '.render.com') !== false) {
            $this->db_type = 'postgresql';
        }
        
        // Si estamos en Docker local y no hay contraseña, usar la configuración de Docker
        if ($this->host === 'db' && empty($this->password)) {
            $this->password = 'registro_pass';
            $this->db_type = 'mysql';
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Obtener información de la conexión para depuración
     */
    public function getConnectionInfo() {
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'password_set' => !empty($this->password),
            'docker_mode' => $this->host === 'db'
        ];
    }
}
?>
