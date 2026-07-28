<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}
// include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.inc');

// Variables de control de rol
$esCEO = (isset($_SESSION['tipoUsuario']) && $_SESSION['tipoUsuario'] === 'CEO');
$codAerolineaCEO = $esCEO ? $_SESSION['codUsuario'] : null;

// Variables de notificación
$mensaje = "";
$tipo_mensaje = "danger";

// Usuario reserva vuelo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar_vuelo'])) {
  if (empty($_SESSION) || $_SESSION['tipoUsuario'] !== 'usuario') {
    header('Location: ../LOGIN/login.php');
    exit;
  }
  $codVueloCompra    = (int)$_POST['codVuelo'];
  $codUsuarioCompra  = (int)$_SESSION['codUsuario'];
  $cantidadPasajeros = max(1, min(9, (int)($_POST['cantidadPasajeros'] ?? 1)));

  $resVuelo  = mysqli_query($link, "SELECT asientosDisponibles, fechaSalidaVuelo, horaSalidaVuelo FROM vuelos WHERE codVuelo = $codVueloCompra");
  $vueloData = mysqli_fetch_assoc($resVuelo);

  $fechaHoraVuelo = $vueloData ? strtotime($vueloData['fechaSalidaVuelo'] . ' ' . $vueloData['horaSalidaVuelo']) : false;

  if (!$vueloData || $fechaHoraVuelo < time()) {
    $mensaje = "No es posible reservar este vuelo porque su fecha de salida ya pasó.";
    $tipo_mensaje = "danger";
  } elseif ($vueloData['asientosDisponibles'] < $cantidadPasajeros) {
    $mensaje = "Lo sentimos, no hay suficientes asientos disponibles para este vuelo. Asientos disponibles: {$vueloData['asientosDisponibles']}.";
    $tipo_mensaje = "danger";
  } else {
    $resExiste = mysqli_query($link, "SELECT codReserva FROM reservas WHERE codUsuario = $codUsuarioCompra AND codVuelo = $codVueloCompra AND estadoReserva = 'Pendiente de pago'");
    if (mysqli_num_rows($resExiste) > 0) {
      $mensaje = "Ya tenés una reserva pendiente de pago para este vuelo. Revisá tu perfil.";
      $tipo_mensaje = "warning";
    } else {
      $hoy = date('Y-m-d');
      $sqlInsertReserva = "INSERT INTO reservas (codUsuario, codVuelo, fechaReserva, estadoReserva, cantidadPasajeros) VALUES ($codUsuarioCompra, $codVueloCompra, '$hoy', 'Pendiente de pago', $cantidadPasajeros)";
      if (mysqli_query($link, $sqlInsertReserva)) {
        // Decremento asientos según cantidad elegida
        mysqli_query($link, "UPDATE vuelos SET asientosDisponibles = asientosDisponibles - $cantidadPasajeros WHERE codVuelo = $codVueloCompra");
        header('Location: vuelos.php?msg=reservado');
        exit;
      } else {
        $mensaje = "Error al procesar la reserva: " . mysqli_error($link);
        $tipo_mensaje = "danger";
      }
    }
  }
}
    
// mensajes de éxito o
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'creado') {
        $mensaje = "El vuelo ha sido registrado exitosamente.";
        $tipo_mensaje = "success";
    } elseif ($_GET['msg'] === 'actualizado') {
        $mensaje = "Los datos del vuelo han sido actualizados con éxito.";
        $tipo_mensaje = "success";
    } elseif ($_GET['msg'] === 'eliminado') {
        $mensaje = "El vuelo ha sido eliminado correctamente.";
        $tipo_mensaje = "success";
    } elseif ($_GET['msg'] === 'reservado') {
        $mensaje = "¡Reserva realizada con éxito! Revisá tu perfil en Reservas activas para confirmar el pago.";
        $tipo_mensaje = "info";
    }
}

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
    if (empty($_POST['origen']) || empty($_POST['destino']) || empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['precio']) || empty($_POST['asientos'])) {
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
        $codAerolinea = $codAerolineaCEO;
        if($_POST['fechaVuelta'] === ''){
          $fechaVuelta = NULL;
        } else {
          $fechaVuelta = $_POST['fechaVuelta'];
        }
        $horaVuelta = $_POST['horaVuelta'];
        if ($precio < 0 || $precio > 10000000) {
            $mensaje = "El precio debe estar entre 0 y 10.000.000.";
            $tipo_mensaje = "danger";
        } elseif ($asientos < 1 || $asientos > 300) {
            $mensaje = "La cantidad de asientos debe ser entre 1 y 300.";
            $tipo_mensaje = "danger";
        } else {
            $sqlInsert = "INSERT INTO vuelos (origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles, codAerolinea, fechaVuelta, horaVuelta) 
                          VALUES ('$origen', '$destino', '$fecha', '$hora', $precio, $asientos, $codAerolinea, '$fechaVuelta', '$horaVuelta')";

            if (mysqli_query($link, $sqlInsert)) {
                header('Location: vuelos.php?msg=creado');
                exit;
            } else {
                $mensaje = "Error al registrar el vuelo en la base de datos.";
            }
        }
    }
}

// ELIMINAR VUELO 
if (isset($_GET['eliminar']) && $esCEO) {
    $idEliminar = (int)$_GET['eliminar'];
    $sqlDelete = "DELETE FROM vuelos WHERE codVuelo = $idEliminar AND codAerolinea = $codAerolineaCEO";
    if (mysqli_query($link, $sqlDelete)) {
        header('Location: vuelos.php?msg=eliminado');
        exit;
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
  $codAerolinea = $codAerolineaCEO;
  $fechaVuelta = $_POST['fechaVuelta'];
  $horaVuelta = $_POST['horaVuelta'];

    if (empty($_POST['origen']) || empty($_POST['destino']) || empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['precio']) || empty($_POST['asientos'])) {
        $mensaje = "Por favor, complete todos los campos para actualizar el vuelo.";
        $tipo_mensaje = "danger";
    } elseif ($precio < 0 || $precio > 10000000) {
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
                        codAerolinea=$codAerolinea, 
                        fechaVuelta='$fechaVuelta', 
                        horaVuelta='$horaVuelta' 
                      WHERE codVuelo=$idEditar AND codAerolinea = $codAerolineaCEO";

        if (mysqli_query($link, $sqlUpdate)) {
            header('Location: vuelos.php?msg=actualizado');
            exit;
        } else {
            $mensaje = "Error al intentar actualizar la información del vuelo.";
        }
    }
}

// consulta y compara precios para cartelito "mas barato"
$precioMinSql = "SELECT MIN(precioVuelo) as minimo FROM vuelos";
if ($esCEO) {
    $precioMinSql .= " WHERE codAerolinea = $codAerolineaCEO";
}
$queryPrecioMin = mysqli_query($link, $precioMinSql);
$fetchMin = mysqli_fetch_assoc($queryPrecioMin);
$precioMasBaratoReal = $fetchMin['minimo'] ?? 0;

// OBTENER VUELOS 
$filtroOrigen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$filtroDestino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$filtroFechaIda = isset($_GET['fechaIda']) ? $_GET['fechaIda'] : '';
$filtroFechaVuelta = isset($_GET['fechaVuelta']) ? $_GET['fechaVuelta'] : '';


$condiciones = [];
if ($esCEO) {
    $condiciones[] = "v.codAerolinea = $codAerolineaCEO";
} else {
    // Usuarios y visitantes solo ven vuelos futuros o de hoy
    $condiciones[] = "v.fechaSalidaVuelo >= CURDATE()";
}
if ($filtroOrigen) {
    $filtroOrigenEsc = mysqli_real_escape_string($link, $filtroOrigen);
    $condiciones  [] = "v.origenVuelo LIKE '%$filtroOrigenEsc%'";
}
if ($filtroDestino) {
    $filtroDestinoEsc = mysqli_real_escape_string($link, $filtroDestino);
    $condiciones[] = "v.destinoVuelo LIKE '%$filtroDestinoEsc%'";
}
if ($filtroFechaIda) {
    $filtroFechaIdaEsc = mysqli_real_escape_string($link, $filtroFechaIda);
    $condiciones[] = "v.fechaSalidaVuelo = '$filtroFechaIdaEsc'";
}
if ($filtroFechaVuelta) {
    $filtroFechaVueltaEsc = mysqli_real_escape_string($link, $filtroFechaVuelta);
    $condiciones[] = "v.fechaVuelta = '$filtroFechaVueltaEsc'";
} else if ($filtroFechaIda && !$filtroFechaVuelta) {
    $condiciones[] = "(v.fechaVuelta IS NULL OR v.fechaVuelta = '0000-00-00')";
}

$sql = "SELECT v.*, a.nombreAerolinea FROM vuelos v LEFT JOIN aerolineas a ON v.codAerolinea = a.codAerolinea";
if (!empty($condiciones)) {
    $sql = $sql . " WHERE " . implode(" AND ", $condiciones);
}
$sql = $sql . " ORDER BY v.fechaSalidaVuelo ASC";
$result = mysqli_query($link, $sql);
$totalVuelos = mysqli_num_rows($result);

// Solo mostrar promos que el usuario logueado haya solicitado
$promosMap = [];
$codUsuarioVuelos = (int)($_SESSION['codUsuario'] ?? 0);
if ($codUsuarioVuelos > 0 && ($_SESSION['tipoUsuario'] ?? '') === 'usuario') {
    $resPromosVuelos = mysqli_query($link,
        "SELECT p.codAerolinea, p.codPromocion, p.descripcionPromocion, p.descuentoPromocion, p.vigenciaPromocion
         FROM promociones p
         INNER JOIN solicitudes_promo s ON p.codPromocion = s.codPromocion
         WHERE p.estadoPromocion = 'aprobada'
         AND (p.vigenciaPromocion IS NULL OR p.vigenciaPromocion >= CURDATE())
         AND s.codUsuario = $codUsuarioVuelos
         ORDER BY p.descuentoPromocion DESC");
    if ($resPromosVuelos) {
        while ($pv = mysqli_fetch_assoc($resPromosVuelos)) {
            $cod = $pv['codAerolinea'];
            if (!isset($promosMap[$cod])) {
                $promosMap[$cod] = $pv;
            }
        }
    }
}
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
          
          <?php if (isset($_SESSION['nombreUsuario'])): ?>
              <span class="text-white me-2"><a href="../PERFIL/perfiles.php" style="text-decoration: none; color: white">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?></strong></a></span>
              <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesion</a>
          <?php else: ?>
              <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration: none; color: white;">Iniciar sesión</a>
          <?php endif; ?>
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
        <input class="sidebar-input" type="text" id="sb_origen" value="<?php echo htmlspecialchars($filtroOrigen); ?>">
      </div>
      <div class="sidebar-grupo">
        <label>Destino</label>
        <input class="sidebar-input" type="text" id="sb_destino" value="<?php echo htmlspecialchars($filtroDestino); ?>">
      </div>
      <div class="sidebar-grupo">
        <label>Ida fecha</label>
        <input class="sidebar-input-date" type="date" id="sb_fechaIda" value="<?php echo htmlspecialchars($filtroFechaIda); ?>">
      </div>
      <div class="sidebar-grupo">
        <label>Vuelta fecha<span style="color: var(--gris);"> (opcional)</span></label>
        <input class="sidebar-input-date" type="date" id="sb_fechaVuelta" 
               value="<?php echo htmlspecialchars($filtroFechaVuelta); ?>"
               title="Fecha de vuelta (opcional)">
        <small style="color: var(--gris); font-size: .78rem;">(opcional)</small>
      </div>
      <div class="sidebar-grupo">
        <label>Cantidad pasajeros</label>
        <input class="sidebar-input" type="number" id ="sb_pasajeros" placeholder="Ej: 2" min="1" value="<?php echo htmlspecialchars(isset($_GET['pasajeros']) ? $_GET['pasajeros'] : ''); ?>">
      </div>
      <button class="btn-aplicar" onclick="aplicarFiltros()"> Buscar </button>
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
                  <div >
                    <span class="ciudad-nombre"><?php echo htmlspecialchars($vuelo['origenVuelo']); ?></span>
                    <span class="ciudad-horario">Horario salida ida: <?php echo date('H:i', strtotime($vuelo['horaSalidaVuelo'])); ?> hs</span>
                  </div>
                  <div>
                    <span class="ciudad-nombre"><?php echo htmlspecialchars($vuelo['destinoVuelo']); ?></span>
                    <span class="ciudad-horario">Fecha ida: <?php echo date('d/m/Y', strtotime($vuelo['fechaSalidaVuelo'])); ?></span>
                  </div>
                  <?php if (!empty($vuelo['fechaVuelta']) && $vuelo['fechaVuelta'] !== '0000-00-00'): ?>
                    <div>
                      <span class="ciudad-nombre"> &nbsp; </span>
                      <span class="ciudad-horario">Horario salida vuelta: <?php echo date('H:i', strtotime($vuelo['horaVuelta'])); ?> hs</span>
                    </div>
                    <div>
                      <span class="ciudad-nombre"> &nbsp; </span>
                      <span class="ciudad-horario">Fecha vuelta: <?php echo date('d/m/Y', strtotime($vuelo['fechaVuelta'])); ?></span>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="vuelo-detalles-row">
                  <div>Asientos libres: <strong><?php echo $vuelo['asientosDisponibles']; ?></strong></div>
                </div>
              </div>
              
              <div class="vuelo-precio-col">
                <?php
                  $promoVuelo = $promosMap[$vuelo['codAerolinea']] ?? null;
                  $precioOriginal = (float)$vuelo['precioVuelo'];
                  $precioConDescuento = $promoVuelo
                    ? round($precioOriginal * (1 - $promoVuelo['descuentoPromocion'] / 100))
                    : null;
                ?>
                <span class="precio-label">PRECIO</span>
                <?php if ($promoVuelo): ?>
                  <span class="precio-valor" style="text-decoration:line-through; color:var(--gris); font-size:1rem;">
                    $<?php echo number_format($precioOriginal, 0, ',', '.'); ?>
                  </span>
                  <span class="precio-valor" style="color:var(--verde);">
                    $<?php echo number_format($precioConDescuento, 0, ',', '.'); ?>
                  </span>
                  <span style="background:#e8f8ee;color:var(--verde);border:1px solid #9dd8b5;border-radius:8px;padding:3px 10px;font-size:.78rem;font-weight:700;margin-top:2px;">
                    <i class="bi bi-tag-fill me-1"></i><?php echo number_format($promoVuelo['descuentoPromocion'],0); ?>% OFF — <?php echo htmlspecialchars($promoVuelo['descripcionPromocion']); ?>
                  </span>
                <?php else: ?>
                  <span class="precio-valor">$<?php echo number_format($precioOriginal, 0, ',', '.'); ?></span>
                <?php endif; ?>

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
                      data-fecha-vuelta="<?php echo $vuelo['fechaVuelta']; ?>"
                      data-hora-vuelta="<?php echo date('H:i', strtotime($vuelo['horaVuelta'])); ?>"
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
                  <?php if (isset($_SESSION['tipoUsuario']) && $_SESSION ['tipoUsuario'] === 'usuario'): ?>
                    <button type="button" class="btn-comprar"
                      onclick="confirmarCompra(
                        <?php echo $idSeguroVuelo; ?>,
                        '<?php echo addslashes($vuelo['origenVuelo']); ?>',
                        '<?php echo addslashes($vuelo['destinoVuelo']); ?>',
                        <?php echo $precioOriginal; ?>,
                        <?php echo $precioConDescuento ?? 'null'; ?>,
                        '<?php echo $promoVuelo ? addslashes($promoVuelo['descripcionPromocion']) : ''; ?>'
                      )">
                      COMPRAR
                    </button>
                  <?php else: ?>
                    <a href="../LOGIN/login.php" class="btn-comprar" style="text-align: center;">COMPRAR</a>
                  <?php endif; ?>
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
                <input type="hidden" name="codAerolinea" value="<?php echo $codAerolineaCEO; ?>">
                <div class="col-md-12">
                  <label class="form-label">Aerolínea</label>
                  <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['nombreUsuario'] ?? ''); ?>" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fecha de Vuelta<span style="color: var(--gris);"> (opcional)</span></label>
                  <input type="date" name="fechaVuelta" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Hora de Vuelta<span style="color: var(--gris);"> (opcional)</span></label>
                  <input type="text" name="horaVuelta" class="form-control" placeholder="HH:MM" maxlength="5" pattern="^([01]\\d|2[0-3]):([0-5]\\d)$">
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
      <div class="modal-dialog modal-dialog-centered">
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
      var horaInput = form.querySelector('input[name="hora"]');
        var horaVueltaInput = form.querySelector('input[name="horaVuelta"]');
        var setupHoraInput = function(input){
          if (!input) return;
          input.setAttribute('placeholder','HH:MM');
          input.setAttribute('maxlength','5');
          input.setAttribute('pattern','^([01]\\d|2[0-3]):([0-5]\\d)$');

          input.addEventListener('input', function(){
            var v = input.value || '';
            var digits = v.replace(/\D/g, '');
            if (digits.length > 4) digits = digits.slice(0,4);
            if (digits.length <= 2) {
              input.value = digits;
            } else {
              input.value = digits.slice(0,2) + ':' + digits.slice(2);
            }
          });

          input.addEventListener('blur', function(){
            var v = input.value || '';
            if (!v) return;
            var m = v.match(/^(\d{1,2}):?(\d{1,2})$/);
            if (m) {
              var hh = m[1].padStart(2,'0');
              var mm = (m[2]||'0').padStart(2,'0');
              if (parseInt(hh,10) > 23) hh = '23';
              if (parseInt(mm,10) > 59) mm = '59';
              input.value = hh + ':' + mm;
            } else {
              input.value = '';
            }
          });
        };

        setupHoraInput(horaInput);
        setupHoraInput(horaVueltaInput);
      document.querySelectorAll('.btn-edit').forEach(function(btn){
        btn.addEventListener('click', function(){
          var dataset = btn.dataset;
          // panel de editar vuelo con header amarilo
          modalHeader.classList.remove('bg-primary','text-white');
          modalHeader.classList.add('bg-warning','text-dark');
          modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Modificar Vuelo';
          
          // carga los datos del vuelo en el form antes de abrir el modal
          form.querySelector('input[name="origen"]').value = dataset.origen || '';
          form.querySelector('input[name="destino"]').value = dataset.destino || '';
          form.querySelector('input[name="fecha"]').value = dataset.fecha || '';
          form.querySelector('input[name="hora"]').value = dataset.hora || '';
          form.querySelector('input[name="precio"]').value = dataset.precio || '';
          form.querySelector('input[name="asientos"]').value = dataset.asientos || '';
          form.querySelector('input[name="fechaVuelta"]').value = dataset.fechaVuelta || '';
          form.querySelector('input[name="horaVuelta"]').value = dataset.horaVuelta || '';
          
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
    function aplicarFiltros(){
      var params = new URLSearchParams();
      var origen = document.getElementById('sb_origen').value.trim();
      var destino = document.getElementById('sb_destino').value.trim();
      var fechaIda = document.getElementById('sb_fechaIda').value;
      var fechaVuelta = document.getElementById('sb_fechaVuelta').value;
      var pasajeros = document.getElementById('sb_pasajeros').value.trim();
      if (origen) params.append('origen', origen);
      if (destino) params.append('destino', destino);
      if (fechaIda) params.append('fechaIda', fechaIda);
      if (fechaVuelta) params.append('fechaVuelta', fechaVuelta);
      if (pasajeros) params.append('pasajeros', pasajeros);
      window.location.href = 'vuelos.php?' + params.toString();
    }
    function confirmarCompra(codVuelo, origen, destino, precioOriginal, precioConPromo, promoDesc) {
      document.getElementById('codVueloComprar').value = codVuelo;
      document.getElementById('modalRutaTexto').textContent = origen + ' → ' + destino;

      var bloquePromo = document.getElementById('bloquePromo');
      var bloqueNormal = document.getElementById('bloquePrecionormal');

      if (precioConPromo !== null && precioConPromo !== undefined) {
        // Hay promo disponible
        bloquePromo.style.display = 'block';
        bloqueNormal.style.display = 'none';
        document.getElementById('modalPromoDesc').textContent = promoDesc;
        document.getElementById('modalPrecioSin').textContent = '$' + precioOriginal.toLocaleString('es-AR');
        document.getElementById('modalPrecioConPromo').textContent = '$' + precioConPromo.toLocaleString('es-AR');
        // Reset radio
        document.getElementById('promoSi').checked = true;
      } else {
        // Sin promo
        bloquePromo.style.display = 'none';
        bloqueNormal.style.display = 'block';
        document.getElementById('modalPrecioTexto').textContent = '$' + precioOriginal.toLocaleString('es-AR');
      }

      var modal = new bootstrap.Modal(document.getElementById('modalConfirmarCompra'));
      modal.show();
    }

    function actualizarPrecioModal() {
      // Solo visual — la lógica de descuento se puede aplicar en el servidor si se agrega un campo hidden
    }
  </script>

<!-- MODAL CONFIRMAR COMPRA -->
<div class="modal fade" id="modalConfirmarCompra" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Confirmar reserva</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <p style="font-size:1rem;margin-bottom:12px;">
          <strong id="modalRutaTexto"></strong>
        </p>
        <!-- Precio sin promo -->
        <div id="bloquePrecionormal">
          <p style="font-size:1rem;margin:0;">
            Precio: <strong id="modalPrecioTexto"></strong>
          </p>
        </div>
        <!-- Selector de promo si hay -->
        <div id="bloquePromo" style="display:none; margin-top:12px; background:#e8f8ee; border:1px solid #9dd8b5; border-radius:10px; padding:14px;">
          <p style="font-weight:700; color:var(--verde); margin-bottom:8px;">
            <i class="bi bi-tag-fill me-1"></i> Hay una promoción disponible para este vuelo
          </p>
          <p id="modalPromoDesc" style="font-size:.9rem; color:#333; margin-bottom:10px;"></p>
          <div class="d-flex gap-3 align-items-center">
            <div>
              <span style="font-size:.85rem; color:var(--gris);">Sin promo:</span><br>
              <strong id="modalPrecioSin" style="text-decoration:line-through; color:var(--gris);"></strong>
            </div>
            <div>
              <span style="font-size:.85rem; color:var(--verde);">Con promo:</span><br>
              <strong id="modalPrecioConPromo" style="color:var(--verde); font-size:1.1rem;"></strong>
            </div>
          </div>
          <div class="mt-3">
            <label style="font-weight:600; font-size:.9rem;">¿Aplicar promoción?</label><br>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="radio" name="aplicarPromo" id="promoSi" value="si" checked onchange="actualizarPrecioModal()">
              <label class="form-check-label" for="promoSi">Sí, aplicar descuento</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="aplicarPromo" id="promoNo" value="no" onchange="actualizarPrecioModal()">
              <label class="form-check-label" for="promoNo">No, precio normal</label>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <label for="cantidadPasajeros" style="font-weight:600; font-size:.9rem;">Cantidad de pasajeros</label>
          <input type="number" class="form-control mt-1" id="cantidadPasajeros" name="cantidadPasajeros" min="1" max="9" value="1" style="width:120px;">
          <div id="errorPasajeros" class="text-danger mt-1" style="font-size:.85rem; display:none;">Ingresá una cantidad válida (entre 1 y 9).</div>
        </div>
        <p class="mt-3 mb-0" style="font-size:.88rem; color:var(--gris);">Podrás pagar desde tu perfil en <strong>Reservas activas</strong>.</p>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form method="POST" action="vuelos.php" id="formComprar" onsubmit="var c=document.getElementById('cantidadPasajeros'),e=document.getElementById('errorPasajeros'),v=parseInt(c.value);if(!v||v<1||v>9){e.style.display='block';return false;}e.style.display='none';document.getElementById('cantidadPasajerosHidden').value=v;">
          <input type="hidden" name="comprar_vuelo" value="1">
          <input type="hidden" name="codVuelo" id="codVueloComprar" value="">
          <input type="hidden" name="cantidadPasajeros" id="cantidadPasajerosHidden" value="1">
          <button type="submit" class="btn btn-success" style="font-weight:600;">
            <i class="bi bi-check-lg me-1"></i>Sí, reservar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>



</body>
</html>