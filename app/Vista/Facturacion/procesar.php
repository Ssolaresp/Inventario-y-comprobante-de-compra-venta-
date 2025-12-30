<?php
// ============================================================
// IMPORTANTE: Iniciar sesión PRIMERO antes de cualquier cosa
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: ../../login.php?error=sesion_expirada');
    exit;
}

require_once '../../Controlador/FacturasControlador.php';

$controlador = new FacturasControlador();

try {
    $accion = $_GET['accion'] ?? '';
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception("ID no especificado");
    }

    switch ($accion) {
        case 'anular':
            // Si se accede por GET, mostrar formulario de confirmación
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                require_once '../../../includes/sidebar.php';
                $factura = $controlador->obtener($id);
                
                if (!$factura) {
                    throw new Exception("Factura no encontrada");
                }
                
                // Verificar que la factura no esté ya anulada
                if ($factura['estado_codigo'] === 'ANU') {
                    throw new Exception("La factura ya está anulada");
                }
                ?>
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Anular Factura</title>
                    <style>
                        .container-anular {
                            max-width: 800px;
                            margin: 20px auto;
                            padding: 20px;
                        }
                        .alerta-advertencia {
                            background-color: #fff3cd;
                            border: 2px solid #ffc107;
                            border-left: 5px solid #ff9800;
                            padding: 15px;
                            margin: 20px 0;
                            border-radius: 5px;
                        }
                        .info-factura {
                            background-color: #f8f9fa;
                            border: 1px solid #dee2e6;
                            padding: 20px;
                            margin: 20px 0;
                            border-radius: 5px;
                        }
                        .info-factura div {
                            margin-bottom: 10px;
                            font-size: 14px;
                        }
                        .form-group {
                            margin-bottom: 20px;
                        }
                        .form-group label {
                            display: block;
                            font-weight: bold;
                            margin-bottom: 8px;
                            font-size: 14px;
                        }
                        .form-group textarea {
                            width: 100%;
                            max-width: 600px;
                            padding: 10px;
                            border: 1px solid #ced4da;
                            border-radius: 4px;
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            resize: vertical;
                        }
                        .btn-anular {
                            background-color: #dc3545;
                            color: white;
                            padding: 12px 25px;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: bold;
                        }
                        .btn-anular:hover {
                            background-color: #c82333;
                        }
                        .btn-cancelar {
                            background-color: #6c757d;
                            color: white;
                            padding: 12px 25px;
                            border: none;
                            border-radius: 4px;
                            text-decoration: none;
                            display: inline-block;
                            margin-left: 10px;
                            font-size: 14px;
                        }
                        .btn-cancelar:hover {
                            background-color: #5a6268;
                        }
                    </style>
                </head>
                <body>
                    <div class="container-anular">
                        <h2>❌ Anular Factura</h2>
                        
                        <div class="alerta-advertencia">
                            <strong>⚠️ ADVERTENCIA:</strong> Esta acción anulará la factura y devolverá el inventario al almacén.
                            <br><strong>Esta operación NO puede deshacerse.</strong>
                        </div>
                        
                        <div class="info-factura">
                            <h3 style="margin-top: 0;">Información de la Factura</h3>
                            <div><strong>Número de Factura:</strong> <?= htmlspecialchars($factura['numero_factura']) ?></div>
                            <div><strong>Tipo:</strong> <?= htmlspecialchars($factura['tipo_factura_nombre']) ?></div>
                            <div><strong>Cliente:</strong> <?= htmlspecialchars($factura['cliente_nombre']) ?></div>
                            <div><strong>NIT:</strong> <?= htmlspecialchars($factura['cliente_nit']) ?></div>
                            <div><strong>Fecha Emisión:</strong> <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></div>
                            <div><strong>Total:</strong> <span style="font-size: 16px; color: #28a745;">Q <?= number_format($factura['total'], 2) ?></span></div>
                            <div><strong>Estado Actual:</strong> 
                                <span style="background-color: <?= htmlspecialchars($factura['estado_color'] ?? '#6c757d') ?>; 
                                             color: white; padding: 3px 8px; border-radius: 3px;">
                                    <?= htmlspecialchars($factura['estado_nombre']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <form method="POST" action="procesar.php?accion=anular&id=<?= $id ?>" onsubmit="return confirm('¿Está seguro que desea anular esta factura?');">
                            <div class="form-group">
                                <label for="motivo">Motivo de Anulación: *</label>
                                <textarea 
                                    name="motivo" 
                                    id="motivo" 
                                    rows="5" 
                                    required 
                                    placeholder="Ingrese el motivo por el cual se anula esta factura. Este campo es obligatorio y quedará registrado en el sistema."
                                    minlength="10"
                                    maxlength="500"
                                ></textarea>
                                <small style="color: #6c757d;">Mínimo 10 caracteres, máximo 500.</small>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn-anular">
                                    ✅ Confirmar Anulación
                                </button>
                                <a href="ver.php?id=<?= $id ?>" class="btn-cancelar">
                                    ❌ Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </body>
                </html>
                <?php
                exit;
            }
            
            // Si es POST, procesar la anulación
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $motivo = trim($_POST['motivo'] ?? '');
                
                // Validar que el motivo no esté vacío
                if (empty($motivo)) {
                    throw new Exception("Debe proporcionar un motivo para la anulación");
                }
                
                // Validar longitud del motivo
                if (strlen($motivo) < 10) {
                    throw new Exception("El motivo debe tener al menos 10 caracteres");
                }
                
                if (strlen($motivo) > 500) {
                    throw new Exception("El motivo no puede exceder 500 caracteres");
                }
                
                // Procesar la anulación
                $resultado = $controlador->anular($id, $motivo);
                
                if ($resultado['success']) {
                    $_SESSION['mensaje_exito'] = 'Factura anulada correctamente';
                    header('Location: ver.php?id=' . $id);
                } else {
                    throw new Exception($resultado['message']);
                }
            }
            break;

        case 'imprimir':
            // Redirigir a la página de impresión
            $formato = $_GET['formato'] ?? 'normal';
            header('Location: imprimir.php?id=' . $id . '&formato=' . $formato);
            exit;
            break;

        case 'enviar_email':
            // Aquí podrías implementar el envío por email
            $_SESSION['mensaje_info'] = 'Funcionalidad de envío por email próximamente';
            header('Location: ver.php?id=' . $id);
            exit;
            break;

        default:
            throw new Exception("Acción no válida: " . htmlspecialchars($accion));
    }

    exit;

} catch (Exception $e) {
    // Guardar el error en la sesión para mostrarlo en la página destino
    $_SESSION['mensaje_error'] = $e->getMessage();
    
    // Redirigir según el contexto
    if (isset($id) && !empty($id)) {
        header('Location: ver.php?id=' . $id);
    } else {
        header('Location: listar.php');
    }
    exit;
}