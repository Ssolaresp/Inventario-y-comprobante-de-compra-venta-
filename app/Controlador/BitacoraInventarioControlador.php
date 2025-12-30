<?php
require_once __DIR__ . '/../Modelo/BitacoraInventario.php';

class BitacoraInventarioControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new BitacoraInventario();
    }

    /**
     * Obtiene el kardex con filtros
     */
    public function obtenerKardex($filtros = []) {
        try {
            // Validar fechas si existen
            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $this->validarFechas($filtros['fecha_desde'], $filtros['fecha_hasta']);
            }
            
            return $this->modelo->obtenerKardex($filtros);
        } catch (Exception $e) {
            throw new Exception("Error al obtener kardex: " . $e->getMessage());
        }
    }

    /**
     * Obtiene kardex por producto
     */
    public function obtenerKardexPorProducto($producto_id, $almacen_id = null) {
        try {
            if (empty($producto_id)) {
                throw new Exception("Debe especificar un producto");
            }
            return $this->modelo->obtenerKardexPorProducto($producto_id, $almacen_id);
        } catch (Exception $e) {
            throw new Exception("Error al obtener kardex del producto: " . $e->getMessage());
        }
    }

    /**
     * Obtiene información resumida del producto para el kardex individual
     */
    public function obtenerInfoProducto($producto_id, $almacen_id = null) {
        try {
            if (empty($producto_id)) {
                throw new Exception("Debe especificar un producto");
            }
            return $this->modelo->obtenerInfoProducto($producto_id, $almacen_id);
        } catch (Exception $e) {
            throw new Exception("Error al obtener información del producto: " . $e->getMessage());
        }
    }

    /**
     * Obtiene resumen de stock
     */
    public function obtenerResumenStock() {
        try {
            return $this->modelo->obtenerResumenStock();
        } catch (Exception $e) {
            throw new Exception("Error al obtener resumen de stock: " . $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas de movimientos
     */
    public function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        try {
            if ($fecha_desde && $fecha_hasta) {
                $this->validarFechas($fecha_desde, $fecha_hasta);
            }
            return $this->modelo->obtenerEstadisticas($fecha_desde, $fecha_hasta);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas: " . $e->getMessage());
        }
    }

    /**
     * Obtiene detalle de un movimiento
     */
    public function obtenerMovimiento($id) {
        try {
            if (empty($id)) {
                throw new Exception("ID de movimiento no especificado");
            }
            $movimiento = $this->modelo->obtenerMovimiento($id);
            if (!$movimiento) {
                throw new Exception("Movimiento no encontrado");
            }
            return $movimiento;
        } catch (Exception $e) {
            throw new Exception("Error al obtener movimiento: " . $e->getMessage());
        }
    }

    /**
     * Obtiene productos para filtros
     */
    public function obtenerProductos() {
        return $this->modelo->obtenerProductos();
    }

    /**
     * Obtiene almacenes para filtros
     */
    public function obtenerAlmacenes() {
        return $this->modelo->obtenerAlmacenes();
    }

    /**
     * Obtiene tipos de referencia
     */
    public function obtenerTiposReferencia() {
        return $this->modelo->obtenerTiposReferencia();
    }

    /**
     * Exporta kardex a CSV
     */
    public function exportarKardex($filtros = []) {
        try {
            $csv = $this->modelo->exportarKardex($filtros);
            
            // Establecer headers para descarga
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="kardex_' . date('Y-m-d_His') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo "\xEF\xBB\xBF"; // BOM para UTF-8
            echo $csv;
            exit;
        } catch (Exception $e) {
            throw new Exception("Error al exportar kardex: " . $e->getMessage());
        }
    }

    /**
     * Valida que las fechas sean correctas
     */
    private function validarFechas($fecha_desde, $fecha_hasta) {
        if (strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            throw new Exception("La fecha desde no puede ser mayor a la fecha hasta");
        }
    }
}
?>