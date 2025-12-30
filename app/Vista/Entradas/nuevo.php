<?php
/* nuevo.php – Formulario de Nueva Entrada con filtro de proveedor */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/EntradasAlmacenControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);
include '../../../includes/sidebar.php';

$controlador     = new EntradasAlmacenControlador();
$almacenes       = $controlador->obtenerAlmacenes();
$proveedores     = $controlador->obtenerProveedores();
$tipos_entrada   = $controlador->obtenerTiposEntrada();
$numero_entrada  = $controlador->generarNumeroEntrada();
$usuario_actual  = $_SESSION['nombre_usuario'] ?? 'Desconocido';

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error']   ?? '';
?>

<link rel="stylesheet" href="/assets/css/entrada.css">
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <h2>➕ Nueva Entrada de Mercadería</h2>

      <?php if ($mensaje): ?>
        <div class="alert alert-success">
          <?php
          switch ($mensaje) {
              case 'creada':     echo '✅ Entrada creada exitosamente'; break;
              case 'autorizada': echo '✅ Entrada autorizada y procesada'; break;
              case 'cancelada':  echo '✅ Entrada cancelada'; break;
              default:           echo '✅ Operación exitosa';
          }
          ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="info-doc">
        <strong>Número de Entrada:</strong> <?= htmlspecialchars($numero_entrada) ?><br>
        <strong>Usuario Registra:</strong> <?= htmlspecialchars($usuario_actual) ?><br>
        <strong>Fecha de Registro:</strong> <?= date('d/m/Y H:i') ?><br>
        <strong>Estado Inicial:</strong> Registrada
      </div>

      <form action="guardar.php" method="POST" id="formEntrada">
        <h3>📋 Datos Generales</h3>

        <div class="form-group">
          <label>Proveedor</label>
          <select name="proveedor_id" id="proveedor_id" class="form-control" required>
            <option value="">-- Seleccione un proveedor --</option>
            <?php foreach ($proveedores as $prov): ?>
              <option value="<?= $prov['id'] ?>">
                <?= htmlspecialchars($prov['codigo'] . ' - ' . $prov['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Tipo de Entrada</label>
          <select name="tipo_entrada_id" class="form-control" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($tipos_entrada as $t): ?>
              <option value="<?= $t['id'] ?>">
                <?= htmlspecialchars($t['nombre']) ?>
                <?= $t['requiere_autorizacion'] ? '(Requiere autorización)' : '(Automática)' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Almacén Destino</label>
          <select name="almacen_id" class="form-control" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($almacenes as $a): ?>
              <option value="<?= $a['id'] ?>">
                <?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Documento de Referencia</label>
          <input type="text" name="documento_referencia" class="form-control"
                 placeholder="Factura, orden de compra, etc.">
        </div>

        <div class="form-group">
          <label>Motivo</label>
          <textarea name="motivo" rows="3" class="form-control"></textarea>
        </div>

        <hr>

        <h3>📦 Productos a Ingresar</h3>
        <div id="productos-container">
          <!-- Los productos se cargarán dinámicamente -->
        </div>

        <button type="button" class="btn btn-primary btn-sm" id="btnAgregarProducto" disabled>
          ➕ Agregar Producto
        </button>
        <small class="text-muted" id="mensaje-proveedor">Primero selecciona un proveedor</small>
        <br><br>

        <button type="submit" class="btn btn-success">💾 Registrar Entrada</button>
        <a href="listar.php" class="btn btn-default">⬅️ Volver al listado</a>
      </form>
    </div>
  </div>
</section>

<script>
let contadorProductos = 0;
let productosDisponibles = [];

// Cuando cambia el proveedor
document.getElementById('proveedor_id').addEventListener('change', function() {
  const proveedor_id = this.value;
  const btnAgregar = document.getElementById('btnAgregarProducto');
  const mensaje = document.getElementById('mensaje-proveedor');
  const container = document.getElementById('productos-container');
  
  // Limpiar productos existentes
  container.innerHTML = '';
  contadorProductos = 0;
  
  if (!proveedor_id) {
    btnAgregar.disabled = true;
    mensaje.textContent = 'Primero selecciona un proveedor';
    return;
  }
  
  // Cargar productos del proveedor
  fetch('obtener_productos_proveedor.php?proveedor_id=' + proveedor_id)
    .then(r => r.json())
    .then(data => {
      if (data.success && data.productos.length > 0) {
        productosDisponibles = data.productos;
        btnAgregar.disabled = false;
        mensaje.textContent = `${data.productos.length} producto(s) disponible(s)`;
        mensaje.className = 'text-success';
        
        // Agregar automáticamente el primer renglón
        agregarProducto();
      } else {
        btnAgregar.disabled = true;
        mensaje.textContent = 'Este proveedor no tiene productos activos';
        mensaje.className = 'text-danger';
      }
    })
    .catch(e => {
      console.error(e);
      btnAgregar.disabled = true;
      mensaje.textContent = 'Error al cargar productos';
      mensaje.className = 'text-danger';
    });
});

document.getElementById('btnAgregarProducto').addEventListener('click', agregarProducto);

function agregarProducto(){
  if (productosDisponibles.length === 0) {
    alert('No hay productos disponibles para este proveedor');
    return;
  }
  
  const container = document.getElementById('productos-container');
  const index = contadorProductos;
  
  let optionsHTML = '<option value="">-- Seleccione --</option>';
  productosDisponibles.forEach(p => {
    optionsHTML += `<option value="${p.id}">${p.codigo} - ${p.nombre}</option>`;
  });
  
  const row = `
  <div class="producto-item">
    <table class="table table-sm">
      <tr>
        <td width="40%">
          <label>Producto</label>
          <select name="productos[${index}][producto_id]" class="form-control producto-select" required>
            ${optionsHTML}
          </select>
        </td>
        <td width="15%">
          <label>Cantidad</label>
          <input type="number" step="0.01" name="productos[${index}][cantidad]"
                 class="form-control" required min="0.01">
        </td>
        <td width="15%">
          <label>Precio Unitario</label>
          <input type="number" step="0.01" name="productos[${index}][precio_unitario]"
                 class="form-control precio-input" required min="0">
          <small class="precio-info text-muted"></small>
        </td>
        <td width="20%">
          <label>Observaciones</label>
          <input type="text" name="productos[${index}][observaciones]" class="form-control">
        </td>
        <td width="10%" class="align-middle">
          <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">
            ❌
          </button>
        </td>
      </tr>
    </table>
  </div>`;
  
  container.insertAdjacentHTML('beforeend', row);
  contadorProductos++;
  aplicarListeners();
}

function eliminarProducto(btn) {
  btn.closest('.producto-item').remove();
}

function aplicarListeners(){
  document.querySelectorAll('.producto-select').forEach(select => {
    const nuevo = select.cloneNode(true);
    select.parentNode.replaceChild(nuevo, select);
    
    nuevo.addEventListener('change', function(){
      const id = this.value;
      const item = this.closest('.producto-item');
      const input = item.querySelector('.precio-input');
      const info = item.querySelector('.precio-info');
      
      if (!id) {
        input.value = '';
        info.innerHTML = '';
        input.disabled = false;
        return;
      }

      info.innerHTML = '<span class="text-muted">Cargando precio...</span>';
      input.disabled = true;
      
      fetch('obtener_precio.php?producto_id=' + id)
        .then(r => r.json())
        .then(d => {
          if (d.success && d.encontrado) {
            input.value = parseFloat(d.precio).toFixed(2);
            info.innerHTML = `<span class="text-success">✓ Precio sugerido (${d.tipo_precio||'Lista'})</span>`;
          } else {
            input.value = '0.00';
            info.innerHTML = '<span class="text-warning">⚠ Sin precio en lista</span>';
          }
          input.disabled = false;
          input.focus();
        })
        .catch(e => {
          console.error(e);
          input.disabled = false;
          info.innerHTML = '<span class="text-danger">✗ Error</span>';
        });
    });
  });
}
</script>