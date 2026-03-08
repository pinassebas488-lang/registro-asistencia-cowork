<?php
class Database {
    private $host = "dpg-d6lrnikr85hc73ad849g-a.oregon-postgres.render.com";
    private $db_name = "bd_asistencia";
    private $username = "bd_asistencia_user";
    private $password = "NMASb682cmpbKhjfwe5F707Xbyx9hvbO";
    private $port = "5432";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Conexión específica para PostgreSQL en Render
            $this->conn = new PDO("pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
