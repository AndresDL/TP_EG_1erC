<?php
session_start();

include_once(__DIR__ . '/../conexion.inc');
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'vuelaseguro';

$link = mysqli_connect($host, $username, $password, $database);

if (!$link) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error());
}

// MODO PRUEBA — descomento el rol que voy a prbar
// ═══════════════════════════════════════════════════════════════
// $_SESSION['usuario'] = ['nombreUsuario' => 'Mateo', 'tipoUsuario' => 'CEO', 'codAerolinea' => 2];
// $_SESSION['usuario'] = ['nombreUsuario' => 'Heis', 'tipoUsuario' => 'usuario', 'codAerolinea' => 1];
// ═══════════════════════════════════════════════════════════════

// Variables de control de rol
$esCEO = (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipoUsuario'] === 'CEO');
$codAerolineaCEO = $esCEO ? $_SESSION['usuario']['codAerolinea'] : null;

// Variables de notificación
$mensaje = "";
$tipo_mensaje = "danger";

//  Traigo lista de aerolíneas
$queryAerolineas = mysqli_query($link, "SELECT codAerolinea, nombreAerolinea FROM aerolineas ORDER BY nombreAerolinea ASC");
$aerolineas = [];
while ($row = mysqli_fetch_assoc($queryAerolineas)) {
    $aerolineas[] = $row;
}

// Vuelo seleccionado para editar
$vueloAEditar = null;
if (isset($_GET['editar_id']) && $esCEO) {
    $idGetEditar = (int)$_GET['editar_id'];
    $resEditar = mysqli_query($link, "SELECT * FROM vuelos WHERE codVuelo = $idGetEditar AND codAerolinea = $codAerolineaCEO");
    if ($resEditar) {
        $vueloAEditar = mysqli_fetch_assoc($resEditar);
    }
}

// PROCESAMIENTO DE OPERACIONES (ALTA, BAJA, MODIFICACIÓN)

// CREAR VUELO 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_vuelo']) && $esCEO) {
    if (empty($_POST['origen']) || empty($_POST['destino']) || empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['precio']) || empty($_POST['asientos']) || empty($_POST['codAerolinea'])) {
        $mensaje = "Por favor, complete todos los campos para crear el vuelo.";
    } elseif (strtotime($_POST['fecha']) < strtotime(date('Y-m-d'))) {
        $mensaje = "La fecha del vuelo no puede ser anterior a la fecha actual.";
    } else {
        $origen = mysqli_real_escape_string($link, $_POST['origen']);
        $destino = mysqli_real_escape_string($link, $_POST['destino']);
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $precio = (float)$_POST['precio'];
        $asientos = (int)$_POST['asientos'];
        $codAerolinea = (int)$_POST['codAerolinea'];

        if ($precio < 0 || $precio > 10000000) {
            $mensaje = "El precio debe estar entre 0 y 10.000.000.";
            $tipo_mensaje = "danger";
        } elseif ($asientos < 1 || $asientos > 300) {
            $mensaje = "La cantidad de asientos debe ser entre 1 y 300.";
            $tipo_mensaje = "danger";
        } else {
            $sqlInsert = "INSERT INTO vuelos (origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles, codAerolinea) 
                          VALUES ('$origen', '$destino', '$fecha', '$hora', $precio, $asientos, $codAerolinea)";

            if (mysqli_query($link, $sqlInsert)) {
                $mensaje = "El vuelo ha sido registrado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al registrar el vuelo en la base de datos.";
            }
        }
    }
}

// ELIMINAR VUELO 
if (isset($_GET['eliminar']) && $esCEO) {
    $idEliminar = (int)$_GET['eliminar'];
    $sqlDelete = "DELETE FROM vuelos WHERE codVuelo = $idEliminar";
    if (mysqli_query($link, $sqlDelete)) {
        $mensaje = "El vuelo ha sido eliminado correctamente.";
        $tipo_mensaje = "success";
    }
}

// ACTUALIZAR VUELO 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_vuelo']) && $esCEO) {
  $idEditar = (int)$_POST['id_vuelo'];
  $origen = mysqli_real_escape_string($link, $_POST['origen']);
  $destino = mysqli_real_escape_string($link, $_POST['destino']);
  $fecha = $_POST['fecha'];
  $hora = $_POST['hora'];
  $precio = (float)$_POST['precio'];
  $asientos = (int)$_POST['asientos'];
    $codAerolinea = (int)$_POST['codAerolinea'];

    if ($precio < 0 || $precio > 10000000) {
        $mensaje = "El precio debe estar entre 0 y 10.000.000.";
        $tipo_mensaje = "danger";
    } elseif ($asientos < 1 || $asientos > 300) {
        $mensaje = "La cantidad de asientos debe ser entre 1 y 300.";
        $tipo_mensaje = "danger";
    } else {
        $sqlUpdate = "UPDATE vuelos SET 
                        origenVuelo='$origen', 
                        destinoVuelo='$destino', 
                        fechaSalidaVuelo='$fecha', 
                        horaSalidaVuelo='$hora', 
                        precioVuelo=$precio, 
                        asientosDisponibles=$asientos, 
                        codAerolinea=$codAerolinea 
                      WHERE codVuelo=$idEditar";

        if (mysqli_query($link, $sqlUpdate)) {
            $mensaje = "Los datos del vuelo han sido actualizados con éxito.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al intentar actualizar la información del vuelo.";
        }
    }
}

// CONSULTA Y DETERMINACIÓN DE PRECIO MÍNIMO
$queryPrecioMin = mysqli_query($link, "SELECT MIN(precioVuelo) as minimo FROM vuelos");
$fetchMin = mysqli_fetch_assoc($queryPrecioMin);
$precioMasBaratoReal = $fetchMin['minimo'] ?? 0;

// OBTENER VUELOS 
$sql = "SELECT v.*, a.nombreAerolinea FROM vuelos v LEFT JOIN aerolineas a ON v.codAerolinea = a.codAerolinea ORDER BY v.fechaSalidaVuelo ASC";

$sql = "SELECT * FROM vuelos"; 
$result = mysqli_query($link, $sql);
$totalVuelos = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>VuelaSeguro – Resultados de búsqueda</title>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>

  <link href="../INDEX/estilos-globales.css" rel="stylesheet">
  <link href="vuelos.css" rel="stylesheet">
</head>

<body>

  <section class="navbar-section">
    <div class="header-wrapper">
      <nav class="navbar-custom">
        <div class="logo-wrap">
          <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
        </div>

        <div class="nav-links">
          <a href="../INDEX/index.php">Inicio</a>
          <a href="../VUELOS/vuelos.php" class="active">Vuelos</a>
          <a href="../NOVEDADES/novedades.php">Novedades</a>
          <a href="../PROMOCIONES/promociones.php">Promociones</a>
        </div>
        
        <div class="nav-right">
          <div class="foto-perfil" title="Perfil">
            <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <circle cx="21" cy="10" r="9" fill="#ffffff"/>
              <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
            </svg>
          </div>
          
          <?php if (isset($_SESSION['usuario'])): ?>
              <span class="text-white me-2">Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombreUsuario']); ?></strong> (<?php echo htmlspecialchars($_SESSION['usuario']['tipoUsuario']); ?>)</span>
              <a href="../LOGIN/logout.php" class="btn-registro bg-danger" style="text-decoration: none;">Salir</a>
          <?php else: ?>
              <a href="../LOGIN/login.php" class="btn-registro">Iniciar Sesión</a>
          <?php endif; ?>

          <a href="../LOGIN/login.php" class="btn-registro">Iniciar Sesión</a>

        </div>
      </nav>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Vuelos</li>
        </ol>
      </nav>
    </div>
  </section>

  <?php if (!empty($mensaje)): ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show vuelos-alert" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
  <?php endif; ?>

  <div class="resultados-wrapper">

    <aside class="sidebar">
      <h3>MODIFICAR BÚSQUEDA</h3>
      <div class="sidebar-grupo">
        <label>Origen</label>
        <input class="sidebar-input" type="text">
      </div>
      <div class="sidebar-grupo">
        <label>Destino</label>
        <input class="sidebar-input" type="text">
      </div>
      <div class="sidebar-grupo">
        <label>Ida fecha</label>
        <input class="sidebar-input-date" type="date">
      </div>
      <div class="sidebar-grupo">
        <label>Vuelta fecha</label>
        <input class="sidebar-input-date" type="date">
      </div>
      <div class="sidebar-grupo">
        <label>Cantidad pasajeros</label>
        <input class="sidebar-input" type="number">
      </div>
      <button class="btn-aplicar">Buscar</button>
    </aside>

    <!-- LISTA DE VUELOS DISPONIBLES -->
    <div class="vuelos-lista">
      <div class="vuelos-header d-flex justify-content-between align-items-center">
        <div>
            <h2>Vuelos disponibles</h2>
            <span class="vuelos-count"><?php echo $totalVuelos; ?></span>
        </div>
        
        <?php if ($esCEO): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearVuelo" style="border-radius: 8px; font-weight: 500; padding: 10px 20px;">
                <i class="bi bi-plus-circle me-2"></i>Cargar Nuevo Vuelo
            </button>
        <?php endif; ?>
      </div>

      <!-- RENDERIZAR TARJETAS DE VUELOS -->
      <?php if ($totalVuelos > 0): ?>
          <?php while($vuelo = mysqli_fetch_assoc($result)): ?>
            <?php 
              // Control definitivo: Si el id del vuelo no existe en la BD o viene vacío, le forzamos un valor para que el HTML no se rompa
              $idSeguroVuelo = !empty($vuelo['codVuelo']) ? (int)$vuelo['codVuelo'] : 0; 
            ?>
            <div class="vuelo-card">
              <div class="vuelo-info">
                <div class="vuelo-aerolinea-row">
                  <span class="vuelo-aerolinea">Aerolínea: <?php echo htmlspecialchars($vuelo['nombreAerolinea'] ?? 'No disponible'); ?></span>
                  <?php if ($vuelo['precioVuelo'] == $precioMasBaratoReal): ?>
                      <span class="badge-barato">MÁS BARATO</span>
                  <?php endif; ?>
                </div>
                <div class="vuelo-ruta">
                  <div>
                    <span class="ciudad-nombre"><?php echo htmlspecialchars($vuelo['origenVuelo']); ?></span>
                    <span class="ciudad-horario">Salida: <?php echo date('H:i', strtotime($vuelo['horaSalidaVuelo'])); ?> hs</span>
                  </div>
                  <div>
                    <span class="ciudad-nombre"><?php echo htmlspecialchars($vuelo['destinoVuelo']); ?></span>
                    <span class="ciudad-horario">Fecha: <?php echo date('d/m/Y', strtotime($vuelo['fechaSalidaVuelo'])); ?></span>
                  </div>
                </div>
                <div class="vuelo-detalles-row">
                  <div>Asientos libres: <strong><?php echo $vuelo['asientosDisponibles']; ?></strong></div>
                </div>
              </div>
              
              <div class="vuelo-precio-col">
                <span class="precio-label">PRECIO</span>
                <span class="precio-valor">$<?php echo number_format($vuelo['precioVuelo'], 0, ',', '.'); ?></span>
                
                <?php if ($esCEO): ?>
                  <div class="d-flex flex-column gap-2 w-100 mt-2">
                    <button type="button"
                      class="btn btn-warning btn-sm btn-edit text-white w-100"
                      style="border-radius:8px; font-weight:500; padding:10px 20px;"
                      data-id="<?php echo $idSeguroVuelo; ?>"
                      data-origen="<?php echo htmlspecialchars($vuelo['origenVuelo']); ?>"
                      data-destino="<?php echo htmlspecialchars($vuelo['destinoVuelo']); ?>"
                      data-fecha="<?php echo $vuelo['fechaSalidaVuelo']; ?>"
                      data-hora="<?php echo date('H:i', strtotime($vuelo['horaSalidaVuelo'])); ?>"
                      data-precio="<?php echo $vuelo['precioVuelo']; ?>"
                      data-asientos="<?php echo $vuelo['asientosDisponibles']; ?>"
                      data-codAerolinea="<?php echo $vuelo['codAerolinea']; ?>"
                    >Editar</button>
                    <button type="button"
                      class="btn btn-danger btn-sm btn-delete w-100"
                      style="border-radius:8px; font-weight:500; padding:10px 20px;"
                      data-bs-toggle="modal"
                      data-bs-target="#modalEliminarVuelo"
                      data-id="<?php echo $idSeguroVuelo; ?>"
                      data-origen="<?php echo htmlspecialchars($vuelo['origenVuelo']); ?>"
                      data-destino="<?php echo htmlspecialchars($vuelo['destinoVuelo']); ?>"
                    >Eliminar</button>
                  </div>
                <?php else: ?>
                  <button class="btn-comprar">COMPRAR</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align: center; margin-top: 10px; color: var(--gris); border: 1px solid var(--borde); border-radius: 8px; padding: 40px 20px; background-color: var(--gris-claro);">No hay vuelos disponibles en este momento.</p>
      <?php endif; ?>

    </div> 
  </div> 

  <!-- MODALES (SOLO PARA CEO) -->
  <?php if ($esCEO): ?>
    <!-- MODAL: CREAR/EDITAR VUELO -->
    <div class="modal fade" id="modalCrearVuelo" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
              <div class="modal-header bg-primary text-white" id="modalHeader">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-airplane-fill me-2"></i>Registrar Nuevo Vuelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="" method="POST" id="formCrearEditar">
                <input type="hidden" name="id_vuelo" id="id_vuelo_input" value="">
            <div class="modal-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Origen</label>
                  <input type="text" name="origen" class="form-control" placeholder="Ej: Rosario" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Destino</label>
                  <input type="text" name="destino" class="form-control" placeholder="Ej: Mendoza" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fecha de Salida</label>
                  <input type="date" name="fecha" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Hora de Salida</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                    <input type="text" name="hora" class="form-control" placeholder="HH:MM" maxlength="5" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Precio ($)</label>
                  <input type="number" step="0.01" name="precio" class="form-control" placeholder="Ej: 90000" min="0" max="10000000" inputmode="decimal" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Asientos Disponibles</label>
                  <input type="number" name="asientos" class="form-control" placeholder="Cantidad" min="1" max="300" inputmode="numeric" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Aerolínea</label>
                  <select name="codAerolinea" class="form-select" id="aerolineaSelect" required>
                    <option value="">Elige una aerolínea</option>
                    <?php foreach($aerolineas as $aero): ?>
                      <option value="<?php echo $aero['codAerolinea']; ?>"><?php echo htmlspecialchars($aero['nombreAerolinea']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
              <div class="modal-footer bg-light">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" name="crear_vuelo" class="btn btn-success" id="modalSubmitBtn">Guardar Vuelo</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN DE VUELO -->
    <div class="modal fade" id="modalEliminarVuelo" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form action="" method="GET" id="formEliminarVuelo">
          <input type="hidden" name="eliminar" id="eliminar_vuelo_id" value="">
          <div class="modal-content text-start" style="font-weight: normal;">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">Eliminar Vuelo</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p>¿Estás seguro que deseas eliminar el vuelo de <strong id="eliminar-origen"></strong> a <strong id="eliminar-destino"></strong>?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
    <!-- LISTA VUELOS -->

<div class="vuelos-lista">

  <div class="vuelos-header">
    <h2>Vuelos disponibles</h2>
    <span class="vuelos-count"><?php echo $totalVuelos; ?></span>
  </div>

  <?php if ($totalVuelos > 0) { ?>
    <div class="vuelo-card">
      <div class="vuelo-info">
        <div class="vuelo-aerolinea-row">
          <span class="vuelo-aerolinea">Aerolíneas Argentinas</span>
          <span class="badge-barato">MÁS BARATO</span>
        </div>
        <div class="vuelo-ruta">
          <div>
            <span class="ciudad-nombre">Buenos Aires</span>
            <span class="ciudad-horario">Salida: 06:45 hs</span>
          </div>
          <div>
            <span class="ciudad-nombre">Mendoza</span>
            <span class="ciudad-horario">Llegada: 08:40 hs</span>
          </div>
        </div>
        <div class="vuelo-detalles-row">
          <div>Pasajeros: <strong>1</strong></div>
          <div>Duración: <strong>1h 55m</strong></div>
          <div>Equipaje incluido: <span class="equipaje-si">✓</span></div>
        </div>
      </div>
      <div class="vuelo-precio-col">
        <span class="precio-label">PRECIO</span>
        <span class="precio-valor">$89.990</span>
        <button class="btn-comprar">COMPRAR</button>
      </div>
    </div>
  <?php } else { ?>
    <p style="text-align: center; margin-top: 10px; color: var(--gris); border: 1px solid var(--borde); border-radius: 8px; padding: 40px 20px; background-color: var(--gris-claro);">No hay vuelos disponibles en este momento.</p>
  <?php } ?>

</div> </div> 

  <!-- FOOTER -->

  <section class="footer-section">
    <footer>
      <div class="row">
        <div class="col">
          <h3><strong>Contactanos</strong><div class="subrayado"></div></h3>
          <ul>
            <li><i class="bi bi-envelope-at"></i><a>vuela@seguro.com.ar</a></li>
            <li><i class="bi bi-whatsapp"></i><a>+54 9 341 234 5678</a></li>
            <li><i class="bi bi-pen"></i><a href="../CONTACTO/contacto.html">Formulario de Contacto</a></li>
          </ul>
        </div>
        <div class="col">
          <h3><strong>Mapa de sitio</strong><div class="subrayado"></div></h3>
          <ul>
            <li><a href="../INDEX/index.php">Inicio</a></li>
            <li><a href="../VUELOS/vuelos.php">Vuelos</a></li>
            <li><a href="../PROMOCIONES/promociones.php">Promociones</a></li>
            <li><a href="../NOVEDADES/novedades.php">Novedades</a></li>
            <li><a href="">Mi Perfil</a></li>
          </ul>
        </div>
        <div class="col">
          <h3><strong>Ubicación</strong><div class="subrayado"></div></h3>
          <ul>
            <li><a href="https://maps.app.goo.gl/UvsGpUXHgk9GkpYP9" target="_blank">Zeballos 1341</a></li>
            <li><a href="https://maps.app.goo.gl/87YMeSLAp74gH9mc7" target="_blank">Rosario, Santa Fe</a></li>
            <li><a href="https://maps.app.goo.gl/u94xc8o8xowqeTuz8" target="_blank">Argentina</a></li>
          </ul>
        </div>
        <div class="col">
          <h3><strong>Newsletter</strong><div class="subrayado"></div></h3>
          <form>
            <i class="bi bi-envelope"></i>
            <input type="email" placeholder="Ingrese su mail">
            <button type="submit"><i class="bi bi-arrow-return-left"></i></button>
          </form>
          <div class="iconos-redes">
            <i class="bi bi-facebook"></i>
            <i class="bi bi-instagram"></i>
            <i class="bi bi-twitter-x"></i>
          </div>
        </div>
      </div>
      <hr>
      <p class="copyright">© 2026 VuelaSeguro. Todos los derechos reservados.</p>
    </footer>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      var modalEl = document.getElementById('modalCrearVuelo');
      if (!modalEl) return;
      var modal = new bootstrap.Modal(modalEl);
      var modalHeader = document.getElementById('modalHeader');
      var modalTitle = document.getElementById('modalTitle');
      var modalSubmitBtn = document.getElementById('modalSubmitBtn');
      var idInput = document.getElementById('id_vuelo_input');
      var form = document.getElementById('formCrearEditar');
      // Hora input: allow typing digits and auto-insert colon (HH:MM)
      var horaInput = form.querySelector('input[name="hora"]');
      if (horaInput) {
        // enforce attributes in case browser reset
        horaInput.setAttribute('placeholder','HH:MM');
        horaInput.setAttribute('maxlength','5');
        horaInput.setAttribute('pattern','^([01]\\d|2[0-3]):([0-5]\\d)$');

        horaInput.addEventListener('input', function(){
          var v = horaInput.value || '';
          // keep only digits
          var digits = v.replace(/\D/g, '');
          if (digits.length > 4) digits = digits.slice(0,4);
          if (digits.length <= 2) {
            horaInput.value = digits;
          } else {
            horaInput.value = digits.slice(0,2) + ':' + digits.slice(2);
          }
        });

        horaInput.addEventListener('blur', function(){
          var v = horaInput.value || '';
          if (!v) return;
          var m = v.match(/^(\d{1,2}):?(\d{1,2})$/);
          if (m) {
            var hh = m[1].padStart(2,'0');
            var mm = (m[2]||'0').padStart(2,'0');
            if (parseInt(hh,10) > 23) hh = '23';
            if (parseInt(mm,10) > 59) mm = '59';
            horaInput.value = hh + ':' + mm;
          } else {
            horaInput.value = '';
          }
        });
      }

      document.querySelectorAll('.btn-edit').forEach(function(btn){
        btn.addEventListener('click', function(){
          var dataset = btn.dataset;
          // set header style to warning (amarillo)
          modalHeader.classList.remove('bg-primary','text-white');
          modalHeader.classList.add('bg-warning','text-dark');
          modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Modificar Vuelo';
          
          // Asignar valores al form ANTES de mostrar
          form.querySelector('input[name="origen"]').value = dataset.origen || '';
          form.querySelector('input[name="destino"]').value = dataset.destino || '';
          form.querySelector('input[name="fecha"]').value = dataset.fecha || '';
          form.querySelector('input[name="hora"]').value = dataset.hora || '';
          form.querySelector('input[name="precio"]').value = dataset.precio || '';
          form.querySelector('input[name="asientos"]').value = dataset.asientos || '';
          form.querySelector('[name="codAerolinea"]').value = dataset.codAerolinea || '';
          
          // set hidden id and change submit button a editar
          idInput.value = dataset.id || '';
          modalSubmitBtn.textContent = 'Actualizar Vuelo';
          modalSubmitBtn.classList.remove('btn-success');
          modalSubmitBtn.classList.add('btn-warning','text-white');
          modalSubmitBtn.name = 'editar_vuelo';

          modal.show();
        });
      });

      document.querySelectorAll('.btn-delete').forEach(function(btn){
        btn.addEventListener('click', function(){
          var dataset = btn.dataset;
          document.getElementById('eliminar_vuelo_id').value = dataset.id || '';
          document.getElementById('eliminar-origen').textContent = dataset.origen || '';
          document.getElementById('eliminar-destino').textContent = dataset.destino || '';
        });
      });

      // When modal is hidden, reset to create mode
      modalEl.addEventListener('hidden.bs.modal', function(){
        modalHeader.classList.remove('bg-warning','text-dark');
        modalHeader.classList.add('bg-primary','text-white');
        modalTitle.innerHTML = '<i class="bi bi-airplane-fill me-2"></i>Registrar Nuevo Vuelo';
        modalSubmitBtn.textContent = 'Guardar Vuelo';
        modalSubmitBtn.classList.remove('btn-warning','text-white');
        modalSubmitBtn.classList.add('btn-success');
        modalSubmitBtn.name = 'crear_vuelo';
        idInput.value = '';
        form.reset();
      });
    })();
  </script>
</body>
</html>