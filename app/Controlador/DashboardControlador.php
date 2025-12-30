<?php
require_once __DIR__ . '/../Modelo/Dashboard.php';

class DashboardControlador
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Dashboard();
    }

    /* -----  Métodos que ya existían  ----- */
    public function totalUsuariosActivos()
    {
        return $this->modelo->contarUsuariosActivos();
    }

    /* -----  Nuevos contadores  ----- */
    public function totalAlmacenesActivos()
    {
        return $this->modelo->contarAlmacenesActivos();
    }

    public function totalEntradasHoy()
    {
        return $this->modelo->contarEntradasHoy();
    }

    public function totalSalidasHoy()
    {
        return $this->modelo->contarSalidasHoy();
    }

    public function totalTransferenciasHoy()
    {
        return $this->modelo->contarTransferenciasHoy();
    }

    /* Datos para gráfico de barras */
    public function datosAlmacenesProductos()
    {
        return $this->modelo->almacenesConCantidadProductos();
    }

    /* Datos para alerta de vencimientos */
    public function alertasVencimientos()
    {
        return $this->modelo->productosProximosAVencer();
    }
}