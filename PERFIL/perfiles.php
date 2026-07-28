<?php
    $link = null;
    include_once('../conexion.inc');
    if (!$link) {
        die("Error de conexión a la base de datos.");
    }

$porPagina   = 6;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) $paginaActual = 1;
$offset = ($paginaActual - 1) * $porPagina;

// Pago la reserva, cambio a confirmada
if (isset($_GET['pagar']) && !empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario') {
    $codReservaPagar = (int)$_GET['pagar'];
    $codUsuarioPagar = (int)$_SESSION['codUsuario'];
    mysqli_query($link, "UPDATE reservas SET estadoReserva = 'Confirmada' WHERE codReserva = $codReservaPagar AND codUsuario = $codUsuarioPagar AND estadoReserva = 'Pendiente de pago'");
     header('Location: perfiles.php?msg=pagado');
     exit;
}
// Cancelo reserva
if (isset($_GET['cancelar']) && !empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario') {
    $codReservaCancelar = (int)$_GET['cancelar'];
    $codUsuarioCancelar = (int)$_SESSION['codUsuario'];
    // Recuperar asineto
    $resReservaCancelar = mysqli_query($link, "SELECT codVuelo FROM reservas WHERE codReserva = $codReservaCancelar AND codUsuario = $codUsuarioCancelar");
    $dataCancelar = mysqli_fetch_assoc($resReservaCancelar);
    if ($dataCancelar) {
        mysqli_query($link, "UPDATE vuelos SET asientosDisponibles = asientosDisponibles + 1 WHERE codVuelo = ". (int)$dataCancelar['codVuelo']);
        mysqli_query($link, "DELETE FROM reservas WHERE codReserva =$codReservaCancelar AND codUsuario = $codUsuarioCancelar");
    }
    header('Location: perfiles.php?msg=cancelado');
    exit;
}

// Los mensajeeeees
$msgPerfil = '';
$tipoPerfil = 'success';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'pagado') { 
        $msgPerfil = "¡Pago confirmado! Tu reserva fue registrada exitosamente";
      } elseif ($_GET['msg'] === 'cancelado') { 
        $msgPerfil = "Reserva cancelada correctamente."; 
        $tipoPerfil = "warning";
      }
}

// Reservas activas del usuario
$reservasActivas = [];
if (!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario') {
    $codUsuarioRes = (int)$_SESSION['codUsuario'];
    $resActivas = mysqli_query($link, 
    "SELECT r.codReserva, r.estadoReserva, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.horaSalidaVuelo, v.fechaVuelta, v.horaVuelta, v.precioVuelo, v.codAerolinea, a.nombreAerolinea,
            p.descuentoPromocion, p.descripcionPromocion
     FROM reservas r
     JOIN vuelos v ON r.codVuelo = v.codVuelo
     LEFT JOIN aerolineas a ON v.codAerolinea = a.codAerolinea
     LEFT JOIN promociones p ON p.codAerolinea = v.codAerolinea
         AND p.estadoPromocion = 'aprobada'
         AND (p.vigenciaPromocion IS NULL OR p.vigenciaPromocion >= CURDATE())
     WHERE r.codUsuario = $codUsuarioRes AND r.estadoReserva = 'Pendiente de pago'
     GROUP BY r.codReserva
     ORDER BY r.fechaReserva DESC");
    if (!$resActivas) {
        die("Error SQL (reservas activas): " . mysqli_error($link));
    }
    while ($row = mysqli_fetch_assoc($resActivas)) {
        $reservasActivas[] = $row;
    }
}

// Historial de compras del usuario (reservas confirmadas, es decir ya pagadas)
$historialCompras = [];
$totalHistorial = 0;
$totalPaginasHistorial = 1;
if (!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario') {
    $codUsuarioHist = (int)$_SESSION['codUsuario'];

    // Cuento el total de compras confirmadas para calcular la cantidad de páginas
    $resConteo = mysqli_query($link,
    "SELECT COUNT(DISTINCT r.codReserva) AS total
     FROM reservas r
     WHERE r.codUsuario = $codUsuarioHist AND r.estadoReserva = 'Confirmada'");
    if (!$resConteo) {
        die("Error SQL (conteo historial): " . mysqli_error($link));
    }
    $filaConteo = mysqli_fetch_assoc($resConteo);
    $totalHistorial = (int)$filaConteo['total'];
    $totalPaginasHistorial = max(1, (int)ceil($totalHistorial / $porPagina));
    if ($paginaActual > $totalPaginasHistorial) $paginaActual = $totalPaginasHistorial;
    $offset = ($paginaActual - 1) * $porPagina;

    $resHistorial = mysqli_query($link,
    "SELECT r.codReserva, r.estadoReserva, r.fechaReserva, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.horaSalidaVuelo, v.fechaVuelta, v.horaVuelta, v.precioVuelo, v.codAerolinea, a.nombreAerolinea,
            p.descuentoPromocion, p.descripcionPromocion
     FROM reservas r
     JOIN vuelos v ON r.codVuelo = v.codVuelo
     LEFT JOIN aerolineas a ON v.codAerolinea = a.codAerolinea
     LEFT JOIN promociones p ON p.codAerolinea = v.codAerolinea
         AND p.estadoPromocion = 'aprobada'
         AND (p.vigenciaPromocion IS NULL OR p.vigenciaPromocion >= CURDATE())
     WHERE r.codUsuario = $codUsuarioHist AND r.estadoReserva = 'Confirmada'
     GROUP BY r.codReserva
     ORDER BY r.fechaReserva DESC
     LIMIT $porPagina OFFSET $offset");
    if (!$resHistorial) {
        die("Error SQL (historial de compras): " . mysqli_error($link));
    }
    while ($row = mysqli_fetch_assoc($resHistorial)) {
        $historialCompras[] = $row;
    }
}

$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Panel usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
    <link rel="stylesheet" href="../VUELOS/vuelos.css">
</head>

<body>
<!-- se me subia el footer no se pq, si ven esto y saben como arreglarlo con los .css genial -->
<style>
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.footer-section {
    margin-top: auto;
}
</style>

<!-- ══ NAVBAR ══════════════════════════════════════════════════════════════ -->
<header>
  <section class="navbar-section">
    <div class="header-wrapper">
      <nav class="navbar-custom">
        <div class="logo-wrap">
          <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
        </div>

        <div class="nav-links">
          <a href="../INDEX/index.php">Inicio</a>
          <a href="../VUELOS/vuelos.php">Vuelos</a>
          <a href="../NOVEDADES/novedades.php" >Novedades</a>
          <a href="../PROMOCIONES/promociones.php">Promociones</a>
        </div>
        
        <div class="nav-right">
          <div class="foto-perfil" title="Perfil">
            <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <circle cx="21" cy="10" r="9" fill="#ffffff"/>
              <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
            </svg>
          </div>
          
          <?php if (!empty($_SESSION)): ?>
              <span class="text-white me-2"><a href="../PERFIL/perfiles.php" style="text-decoration: none; color: white">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?><a></strong></span>
              <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesion</a>
          <?php else: ?>
              <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration: none; color: white;">Iniciar sesión</a>
          <?php endif; ?>
        </div>
      </nav>
      
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Perfil</li>
        </ol>
      </nav>
    </div>
  </section>
</header>


<!-- PERFIL DE ADMINISTRADOR -->
<?php if(!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'admin'): ?>
    <div class="contacto-wrapper">
        <div class="contacto-form-card">

            <h2>Panel de <?php echo $_SESSION['tipoUsuario'] ?></h2>
            <h4>Podes utilizar las funciones de abajo.</h4>
            
            <div class="d-flex justify-content-center">
                <button class="btn-enviar"><i class="bi bi-airplane"></i> 
                    <a href="../AEROLINEA/aerolinea.php" style="text-decoration: none; color:white;"> Alta de Aerolineas</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i>
                <a href="../AEROLINEA/aerolinea-lista.php" style="text-decoration: none; color:white;"> Listado de Aerolineas</a> 
            </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i>
                    <a href="../PERFIL/admin-reporte-usuarios.php" style="text-decoration: none; color:white;"> Reportes de usuarios</a> 
                </button>
            </div>
        </div>
    </div>

<!-- PERFIL DE USUARIO -->
<?php elseif(!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario'): ?>
    <?php if ($seccion !== 'reservas' && $seccion !== 'historial'): ?>
    <div class="contacto-wrapper">
        <div class="contacto-form-card">
            
            <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-check-circle"></i> Éxito!</strong> <?php echo $_SESSION['message']; $_SESSION['message'] = ''; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h2>Panel de <?php echo $_SESSION['tipoUsuario'] ?></h2>
            <h4>Podes utilizar las funciones de abajo.</h4>
            <div class="d-flex justify-content-center">
                <button class="btn-enviar"><i class="bi bi-airplane"></i>
                    <a href="perfiles.php?seccion=reservas" style="text-decoration: none; color:white;"> Reservas activas</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i>
                    <a href="perfiles.php?seccion=historial" style="text-decoration: none; color:white;"> Historial de compras</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-journal-plus"></i>
                    <a href="perfil-modificar.php" style="text-decoration: none; color:white;"> Modificar perfil</a>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

<!-- TABLA RESERVAS ACTIVAS -->
        <?php if ($seccion === 'reservas'): ?>
<div style="max-width:900px;margin:32px auto;padding:0 16px;">
    <h3 style="font-family:'Sora',sans-serif;color:var(--azul-oscuro);margin-bottom:20px;">
        <i class="bi bi-airplane me-2" style="color:var(--azul);"></i>Reservas activas
    </h3>    

                 <?php if (!empty($msgPerfil)): ?>
                <div class="alert alert-<?php echo $tipoPerfil; ?> alert-dismissible fade show" role="alert">
                    <?php echo $msgPerfil; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (empty($reservasActivas)): ?>
                    <p style="text-align:center;color:var(--gris);padding:30px 0;">
                        No tenés reservas pendientes. <a href="../VUELOS/vuelos.php">¡Buscá un vuelo!</a>
                    </p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach ($reservasActivas as $res): ?>
        <div class="vuelo-card" style="display:flex;justify-content:space-between;align-items:center;background:#fff;border:1px solid var(--borde);border-radius:16px;padding:16px 20px;gap:16px;">
        <div class="vuelo-info" style="flex:1;">
            <div class="vuelo-aerolinea-row" style="margin-bottom:8px;">
            <span class="vuelo-aerolinea">Aerolínea: <?php echo htmlspecialchars($res['nombreAerolinea'] ?? '—'); ?></span>
            <span style="background:#fff3cd;color:#856404;border-radius:6px;padding:2px 10px;font-size:.78rem;font-weight:600;">Pendiente de pago</span>
            </div>
            <div class="vuelo-ruta">
            <div>
                <span class="ciudad-nombre"><?php echo htmlspecialchars($res['origenVuelo']); ?></span>
                <span class="ciudad-horario">Salida: <?php echo date('d/m/Y', strtotime($res['fechaSalidaVuelo'])); ?> — <?php echo date('H:i', strtotime($res['horaSalidaVuelo'])); ?> hs</span>
            </div>
            <div>
                <span class="ciudad-nombre"><?php echo htmlspecialchars($res['destinoVuelo']); ?></span>
                <?php if (!empty($res['fechaVuelta']) && $res['fechaVuelta'] !== '0000-00-00'): ?>
                <span class="ciudad-horario">Vuelta: <?php echo date('d/m/Y', strtotime($res['fechaVuelta'])); ?> — <?php echo date('H:i', strtotime($res['horaVuelta'])); ?> hs</span>
                <?php else: ?>
                <span class="ciudad-horario" style="color:var(--gris);">Solo ida</span>
                <?php endif; ?>
            </div>
            </div>
        </div>
  <div class="vuelo-precio-col" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:140px;gap:8px;">
    <span class="precio-label">PRECIO</span>
    <?php
      $descRes = !empty($res['descuentoPromocion']) ? (float)$res['descuentoPromocion'] : 0;
      $precioFinalRes = $descRes > 0
        ? round((float)$res['precioVuelo'] * (1 - $descRes / 100))
        : (float)$res['precioVuelo'];
    ?>
    <?php if ($descRes > 0): ?>
      <span style="text-decoration:line-through;color:var(--gris);font-size:.9rem;">
        $<?php echo number_format((float)$res['precioVuelo'], 0, ',', '.'); ?>
      </span>
      <span class="precio-valor" style="color:var(--verde);">
        $<?php echo number_format($precioFinalRes, 0, ',', '.'); ?>
      </span>
      <span style="background:#e8f8ee;color:var(--verde);border:1px solid #9dd8b5;border-radius:8px;padding:2px 8px;font-size:.75rem;font-weight:700;">
        <?php echo number_format($descRes, 0); ?>% OFF
      </span>
    <?php else: ?>
      <span class="precio-valor">$<?php echo number_format((float)$res['precioVuelo'], 0, ',', '.'); ?></span>
    <?php endif; ?>
    <button type="button"
        class="btn btn-success btn-sm w-100"
        style="border-radius:8px;font-weight:600;padding:8px;"
        onclick="confirmarPago(<?php echo $res['codReserva']; ?>)">
        <i class="bi bi-credit-card me-1"></i>Pagar
    </button>
    <button type="button"
        class="btn btn-outline-danger btn-sm w-100"
        style="border-radius:8px;font-weight:600;padding:8px;"
        onclick="confirmarCancelar(<?php echo $res['codReserva']; ?>)">
        <i class="bi bi-x-circle me-1"></i>Cancelar
    </button>
  </div>
    </div>
    <?php endforeach; ?>
    </div>
     <?php endif; ?>
    </div>
    <?php endif; ?>

<!-- TABLA HISTORIAL DE COMPRAS --------------------------------------->
        <?php if ($seccion === 'historial'): ?>
<div style="max-width:900px;margin:32px auto;padding:0 16px;">
    <h3 style="font-family:'Sora',sans-serif;color:var(--azul-oscuro);margin-bottom:20px;">
        <i class="bi bi-bag-check me-2" style="color:var(--azul);"></i>Historial de compras
    </h3>

                <?php if (empty($historialCompras)): ?>
                    <p style="text-align:center;color:var(--gris);padding:30px 0;">
                        Todavía no tenés compras confirmadas. <a href="../VUELOS/vuelos.php">¡Buscá un vuelo!</a>
                    </p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach ($historialCompras as $com): ?>
        <div class="vuelo-card" style="display:flex;justify-content:space-between;align-items:center;background:#fff;border:1px solid var(--borde);border-radius:16px;padding:16px 20px;gap:16px;">
        <div class="vuelo-info" style="flex:1;">
            <div class="vuelo-aerolinea-row" style="margin-bottom:8px;">
            <span class="vuelo-aerolinea">Aerolínea: <?php echo htmlspecialchars($com['nombreAerolinea'] ?? '—'); ?></span>
            <span style="background:#d4edda;color:#155724;border-radius:6px;padding:2px 10px;font-size:.78rem;font-weight:600;">Confirmada</span>
            </div>
            <div class="vuelo-ruta">
            <div>
                <span class="ciudad-nombre"><?php echo htmlspecialchars($com['origenVuelo']); ?></span>
                <span class="ciudad-horario">Salida: <?php echo date('d/m/Y', strtotime($com['fechaSalidaVuelo'])); ?> — <?php echo date('H:i', strtotime($com['horaSalidaVuelo'])); ?> hs</span>
            </div>
            <div>
                <span class="ciudad-nombre"><?php echo htmlspecialchars($com['destinoVuelo']); ?></span>
                <?php if (!empty($com['fechaVuelta']) && $com['fechaVuelta'] !== '0000-00-00'): ?>
                <span class="ciudad-horario">Vuelta: <?php echo date('d/m/Y', strtotime($com['fechaVuelta'])); ?> — <?php echo date('H:i', strtotime($com['horaVuelta'])); ?> hs</span>
                <?php else: ?>
                <span class="ciudad-horario" style="color:var(--gris);">Solo ida</span>
                <?php endif; ?>
            </div>
            </div>
            <div style="margin-top:8px;">
                <span class="ciudad-horario" style="color:var(--gris);">Comprado el <?php echo date('d/m/Y', strtotime($com['fechaReserva'])); ?> — Reserva #<?php echo $com['codReserva']; ?></span>
            </div>
        </div>
  <div class="vuelo-precio-col" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:140px;gap:8px;">
    <span class="precio-label">PRECIO</span>
    <?php
      $descCom = !empty($com['descuentoPromocion']) ? (float)$com['descuentoPromocion'] : 0;
      $precioFinalCom = $descCom > 0
        ? round((float)$com['precioVuelo'] * (1 - $descCom / 100))
        : (float)$com['precioVuelo'];
    ?>
    <?php if ($descCom > 0): ?>
      <span style="text-decoration:line-through;color:var(--gris);font-size:.9rem;">
        $<?php echo number_format((float)$com['precioVuelo'], 0, ',', '.'); ?>
      </span>
      <span class="precio-valor" style="color:var(--verde);">
        $<?php echo number_format($precioFinalCom, 0, ',', '.'); ?>
      </span>
      <span style="background:#e8f8ee;color:var(--verde);border:1px solid #9dd8b5;border-radius:8px;padding:2px 8px;font-size:.75rem;font-weight:700;">
        <?php echo number_format($descCom, 0); ?>% OFF — <?php echo htmlspecialchars($com['descripcionPromocion']); ?>
      </span>
    <?php else: ?>
      <span class="precio-valor">$<?php echo number_format((float)$com['precioVuelo'], 0, ',', '.'); ?></span>
    <?php endif; ?>
  </div>
    </div>
    <?php endforeach; ?>
    </div>

                    <?php if ($totalPaginasHistorial > 1): ?>
                    <nav aria-label="Paginación historial de compras" style="margin-top:24px;">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $paginaActual <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="perfiles.php?seccion=historial&pagina=<?php echo $paginaActual - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPaginasHistorial; $p++): ?>
                            <li class="page-item <?php echo $p === $paginaActual ? 'active' : ''; ?>">
                                <a class="page-link" href="perfiles.php?seccion=historial&pagina=<?php echo $p; ?>"><?php echo $p; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $paginaActual >= $totalPaginasHistorial ? 'disabled' : ''; ?>">
                                <a class="page-link" href="perfiles.php?seccion=historial&pagina=<?php echo $paginaActual + 1; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
     <?php endif; ?>
    </div>
    <?php endif; ?>

<!-- PERFIL DE CEO -->
<?php elseif(!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'CEO'): ?>
    <div class="contacto-wrapper">
        <div class="contacto-form-card">
            <h2>Panel de <?php echo $_SESSION['tipoUsuario'] ?></h2>
            <h4>Podes utilizar las funciones de abajo.</h4>
            
            <div class="d-flex justify-content-center">
                <button class="btn-enviar"><i class="bi bi-cash-coin"></i> 
                    <a href="reporte-ventas.php" style="text-decoration: none; color:white;"> Reporte de ventas</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 40px;">
                <button class="btn-enviar"><i class="bi bi-airplane"></i>
                <a href="reporte-ocupacion.php" style="text-decoration: none; color:white;"> Reporte de ocupación de vuelos</a> 
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- FOOTER -------------------------------------->
<section class="footer-section">
    <footer>
        <div class="row">
            <div class="col">
                <h3><strong>Contactanos</strong><div class="subrayado"></div></h3>
                <ul>
                <li><i class="bi bi-envelope-at"></i><a href="mailto:vuela@seguro.com.ar">vuela@seguro.com.ar</a></li>
                <li><i class="bi bi-whatsapp"></i><a href="#">+54 9 341 234 5678</a></li>
                <li><i class="bi bi-pen"></i><a href="../CONTACTO/contacto.php">Formulario de Contacto</a></li>
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
        <p class="copyright">&copy; 2026 VuelaSeguro. Todos los derechos reservados. Licenciado bajo
        <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons BY 4.0</a>.
        </p>
    </footer>
</section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- boton confirmar pago -->
<div class="modal fade" id="modalConfirmarPago" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Confirmar pago</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <p style="font-size:1rem;margin:0;">¿Confirmás el pago de esta reserva? La reserva pasará a estado <strong>Confirmada</strong>.</p>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a id="linkPagar" href="#" class="btn btn-success" style="font-weight:600;">
          <i class="bi bi-check-lg me-1"></i>Sí, pagar
        </a>
      </div>
    </div>
  </div>
</div>

<!--- boton cancelar --->
<div class="modal fade" id="modalConfirmarCancelar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Cancelar reserva</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <p style="font-size:1rem;margin:0;">¿Estás seguro que querés cancelar esta reserva? Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
        <a id="linkCancelar" href="#" class="btn btn-danger" style="font-weight:600;">
          <i class="bi bi-trash me-1"></i>Sí, cancelar
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function confirmarPago(codReserva) {
    document.getElementById('linkPagar').href = 'perfiles.php?pagar=' + codReserva + '&seccion=reservas';
    new bootstrap.Modal(document.getElementById('modalConfirmarPago')).show();
}
function confirmarCancelar(codReserva) {
    document.getElementById('linkCancelar').href = 'perfiles.php?cancelar=' + codReserva;
    new bootstrap.Modal(document.getElementById('modalConfirmarCancelar')).show();
}
</script>

</body>
</html>