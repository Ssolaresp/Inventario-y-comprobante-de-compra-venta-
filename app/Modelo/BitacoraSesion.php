<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class BitacoraSesion {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function registrarIngreso($usuario_id, $ip, $navegador) {
        $sql = "INSERT INTO bitacora_sesiones (usuario_id, ip, navegador) VALUES (?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $resultado = $stmt->execute([$usuario_id, $ip, $navegador]);
        if (!$resultado) {
            $error = $stmt->errorInfo();
            throw new Exception("Error al insertar en bitacora_sesiones: " . $error[2]);
        }
        return true;
    }


    
    public function registrarSalida($usuario_id) {
        $sql = "UPDATE bitacora_sesiones SET fecha_salida = NOW() WHERE usuario_id = ? AND fecha_salida IS NULL ORDER BY fecha_ingreso DESC LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $resultado = $stmt->execute([$usuario_id]);
        if (!$resultado) {
            $error = $stmt->errorInfo();
            throw new Exception("Error al actualizar bitacora_sesiones: " . $error[2]);
        }
        return true;
    }


    
}
