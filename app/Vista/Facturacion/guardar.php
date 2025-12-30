<?php
require_once '../../Controlador/FacturasControlador.php';

$controlador = new FacturasControlador();

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("Método no permitido");
    }

    // Capturar datos principales
    $data = [
        'tipo_factura_id' => $_POST['tipo_factura_id'] ?? null,
        'serie_id' => $_POST['serie_id'] ?? null,
        'cliente_id' => $_POST['cliente_id'] ?? null,
        'fecha_emision' => $_POST['fecha_emision'] ?? date('Y-m-d'),
        'forma_pago_id' => $_POST['forma_pago_id'] ?? null,
        'dias_credito' => $_POST['dias_credito'] ?? 0,
        'almacen_id' => $_POST['almacen_id'] ?? null,
        'vendedor_usuario_id' => $_POST['vendedor_usuario_id'] ?? null,
        'orden_compra' => $_POST['orden_compra'] ?? null,
        'referencia_interna' => $_POST['referencia_interna'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null,
        'subtotal' => $_POST['subtotal'] ?? 0,
        'total_descuento' => $_POST['total_descuento'] ?? 0,
        'total_impuestos' => $_POST['total_impuestos'] ?? 0,
        'total' => $_POST['total'] ?? 0
    ];

    // Validar datos requeridos
    if (empty($data['tipo_factura_id']) || empty($data['serie_id']) || empty($data['cliente_id']) || 
        empty($data['forma_pago_id']) || empty($data['almacen_id'])) {
        throw new Exception("Faltan datos requeridos");
    }

    // Capturar detalle de items
    $detalle = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['item_id'])) {
                $detalle[] = [
                    'tipo_item' => $item['tipo_item'] ?? 'producto',
                    'item_id' => $item['item_id'],
                    'codigo_item' => $item['codigo_item'] ?? '',
                    'descripcion' => $item['descripcion'] ?? '',
                    'cantidad' => $item['cantidad'] ?? 0,
                    'unidad_medida' => $item['unidad_medida'] ?? 'UND',
                    'precio_unitario' => $item['precio_unitario'] ?? 0,
                    'descuento_porcentaje' => $item['descuento_porcentaje'] ?? 0,
                    'descuento_monto' => $item['descuento_monto'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'impuesto_id' => !empty($item['impuesto_id']) ? $item['impuesto_id'] : null,
                    'impuesto_porcentaje' => $item['impuesto_porcentaje'] ?? 0,
                    'impuesto_monto' => $item['impuesto_monto'] ?? 0,
                    'total' => $item['total'] ?? 0,
                    'inventario_almacen_id' => null,
                    'lote' => null,
                    'fecha_vencimiento' => null
                ];
            }
        }
    }

    if (empty($detalle)) {
        throw new Exception("Debe agregar al menos un item a la factura");
    }

    // Crear la factura
    $resultado = $controlador->crear($data, $detalle);

    if ($resultado['success']) {
        header('Location: listar.php?mensaje=Factura creada exitosamente: ' . $resultado['numero_factura']);
    } else {
        throw new Exception($resultado['message']);
    }
    exit;

} catch (Exception $e) {
    header('Location: nuevo.php?error=' . urlencode($e->getMessage()));
    exit;
}