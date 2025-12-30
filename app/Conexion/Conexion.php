<?php
class Conexion {
    private $conexion;
    private $host = 'localhost';
    private $dbName = 'aegis';  // Nombre de la base de datos
    private $username = 'root';  // Usuario de la base de datos
    private $password = '';  // Contraseña de la base de datos

    public function __construct() {
        try {
            // Establecer la conexión con PDO
            $this->conexion = new PDO(
                "mysql:host=$this->host;dbname=$this->dbName",
                $this->username,
                $this->password
            );
            // Configurar PDO para lanzar excepciones en caso de error
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Manejo de errores en la conexión
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>
