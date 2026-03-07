<?php
// Configuración de la base de datos para Render
class BaseDeDatos {
    private $host = 'dpg-d61rnikr85hc73ad849g-a';
    private $db_name = 'bd_asistencia';
    private $usuario = 'bd_asistencia_user';
    private $password = 'NMASb682cmpbKhjfwe5F7O7Xbyx9hvbO'; // Pégala aquí
    private $puerto = '5432';
    public $conexion;

    public function obtenerConexión() {
        $this->conexion = null;
        try {
            // Conexión específica para PostgreSQL en Render
            $this->conexion = new PDO("pgsql:host=" . $this->host . ";port=" . $this->puerto . ";dbname=" . $this->db_name, $this->usuario, $this->password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conexion;
    }
}
?>
