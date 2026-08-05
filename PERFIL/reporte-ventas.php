<?php
    $link = null;
    include_once('../conexion.inc');
    if (!$link) {
        die("Error de conexión a la base de datos.");
    }

    if (empty($_SESSION) || $_SESSION['tipoUsuario'] !== 'CEO') {
        header('Location: ../LOGIN/login.php');
        exit;
    }

    $codAerolinea = $_SESSION['codUsuario'];

    $fechaDesde = isset($_GET['fechaDesde']) ? $_GET['fechaDesde'] : '';
    $fechaHasta = isset($_GET['fechaHasta']) ? $_GET['fechaHasta'] : '';

    // Si el usuario invierte el rango (Hasta más vieja que Desde), lo corregimos
    if ($fechaDesde !== '' && $fechaHasta !== '' && $fechaDesde > $fechaHasta) {
        $tmp = $fechaDesde;
        $fechaDesde = $fechaHasta;
        $fechaHasta = $tmp;
    }

    $sql = "SELECT r.codReserva, u.nombreUsuario, v.codVuelo, v.origenVuelo, v.destinoVuelo,
                   v.fechaSalidaVuelo, v.precioVuelo, r.fechaReserva, r.estadoReserva
            FROM reservas r
            INNER JOIN vuelos v ON r.codVuelo = v.codVuelo
            INNER JOIN usuarios u ON r.codUsuario = u.codUsuario
            WHERE v.codAerolinea = ?
              AND r.estadoReserva = 'Confirmada'";

    $tipos = "i";
    $parametros = [$codAerolinea];

    if ($fechaDesde !== '') {
        $sql .= " AND r.fechaReserva >= ?";
        $tipos .= "s";
        $parametros[] = $fechaDesde;
    }
    if ($fechaHasta !== '') {
        $sql .= " AND r.fechaReserva <= ?";
        $tipos .= "s";
        $parametros[] = $fechaHasta;
    }

    $sql .= " ORDER BY r.fechaReserva DESC";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $ventas = [];
    $totalRecaudado = 0;
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ventas[] = $fila;
        $totalRecaudado += $fila['precioVuelo'];
    }
    $cantidadVentas = count($ventas);

    $nombreAerolinea = $_SESSION['nombreUsuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Reporte de ventas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
    <link rel="stylesheet" href="../PERFIL/perfiles.css">
</head>
<body>
<!-- ══ NAVBAR ══════════════════════════════════════════════════════════════ ---->
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
          <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Perfil</a></li>
          <li class="breadcrumb-item active" aria-current="page">Reporte de ventas</li>
        </ol>
      </nav>
    </div>
  </section>
</header>

<!-- ══ CONTENIDO ═══════════════════════════════════════════════════════════ -->
<div class="contacto-wrapper">

    <div class="contacto-header">
        <h2><i class="bi bi-cash-coin"></i> Reporte de ventas — <?php echo htmlspecialchars($nombreAerolinea); ?></h2>
    </div>

    <!-- Filtro por rango de fechas -->
    <form method="GET" class="filtro-bar">
        <span class="filtro-titulo"><i class="bi bi-funnel"></i> Filtrar por fecha de reserva</span>

        <div class="fecha-container">
            <input type="date" name="fechaDesde" id="fechaDesde" class="fecha-input" value="<?php echo htmlspecialchars($fechaDesde); ?>" <?php if ($fechaHasta !== ''): ?>max="<?php echo htmlspecialchars($fechaHasta); ?>"<?php endif; ?>>
            <div class="fecha-espejo">
                <span class="label-texto" id="labelDesde"><i class="bi bi-calendar-event"></i> Desde</span>
            </div>
        </div>

        <div class="fecha-container">
            <input type="date" name="fechaHasta" id="fechaHasta" class="fecha-input" value="<?php echo htmlspecialchars($fechaHasta); ?>" <?php if ($fechaDesde !== ''): ?>min="<?php echo htmlspecialchars($fechaDesde); ?>"<?php endif; ?>>
            <div class="fecha-espejo">
                <span class="label-texto" id="labelHasta"><i class="bi bi-calendar-event"></i> Hasta</span>
            </div>
        </div>

        <button type="submit" class="btn-buscar"><i class="bi bi-search"></i> Buscar</button>
        <a href="reporte-ventas.php" class="btn-buscar" style="background: var(--gris); text-decoration:none; display:flex; align-items:center;"><i class="bi bi-x-circle"></i></a>
    </form>

    <script>
        (function () {
            function formatearFecha(valor) {
                const partes = valor.split('-');
                if (partes.length !== 3) return valor;
                return partes[2] + '/' + partes[1] + '/' + partes[0];
            }

            function sincronizarEspejo(input, label, textoBase) {
                const icono = '<i class="bi bi-calendar-event"></i> ';
                if (input.value) {
                    label.innerHTML = icono + formatearFecha(input.value);
                } else {
                    label.innerHTML = icono + textoBase;
                }
            }

            const inputDesde = document.getElementById('fechaDesde');
            const inputHasta = document.getElementById('fechaHasta');
            const labelDesde = document.getElementById('labelDesde');
            const labelHasta = document.getElementById('labelHasta');

            sincronizarEspejo(inputDesde, labelDesde, 'Desde');
            sincronizarEspejo(inputHasta, labelHasta, 'Hasta');

            inputDesde.addEventListener('change', function () {
                sincronizarEspejo(inputDesde, labelDesde, 'Desde');
                inputHasta.min = inputDesde.value || '';
                if (inputHasta.value && inputDesde.value && inputHasta.value < inputDesde.value) {
                    inputHasta.value = inputDesde.value;
                    sincronizarEspejo(inputHasta, labelHasta, 'Hasta');
                }
            });

            inputHasta.addEventListener('change', function () {
                sincronizarEspejo(inputHasta, labelHasta, 'Hasta');
                inputDesde.max = inputHasta.value || '';
                if (inputDesde.value && inputHasta.value && inputDesde.value > inputHasta.value) {
                    inputDesde.value = inputHasta.value;
                    sincronizarEspejo(inputDesde, labelDesde, 'Desde');
                }
            });
        })();
    </script>

    <!-- Resumen -->
    <div class="row" style="margin: 0 1.5rem 1.5rem;">
        <div class="col-md-6" style="margin-bottom: 1rem;">
            <div class="contacto-form-card" style="margin:0; padding: 20px;">
                <h4 style="border:none; padding:0; margin-bottom: 4px;">Cantidad de ventas confirmadas</h4>
                <h2 style="color: var(--azul); margin:0;"><?php echo $cantidadVentas; ?></h2>
            </div>
        </div>
        <div class="col-md-6" style="margin-bottom: 1rem;">
            <div class="contacto-form-card" style="margin:0; padding: 20px;">
                <h4 style="border:none; padding:0; margin-bottom: 4px;">Total recaudado</h4>
                <h2 style="color: var(--verde); margin:0;">$<?php echo number_format($totalRecaudado, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="table-responsive" style="margin: 0 1.5rem;">
        <table class="table table-hover" style="background: #ffffff; border-radius: 12px; overflow:hidden;">
            <thead style="background: var(--azul-oscuro); color: #ffffff;">
                <tr>
                    <th>Reserva</th>
                    <th>Pasajero</th>
                    <th>Vuelo</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Fecha de salida</th>
                    <th>Fecha de reserva</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cantidadVentas === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: var(--gris);">
                            No se encontraron ventas confirmadas para el período seleccionado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($venta['codReserva']); ?></td>
                            <td><?php echo htmlspecialchars($venta['nombreUsuario']); ?></td>
                            <td>#<?php echo htmlspecialchars($venta['codVuelo']); ?></td>
                            <td><?php echo htmlspecialchars($venta['origenVuelo']); ?></td>
                            <td><?php echo htmlspecialchars($venta['destinoVuelo']); ?></td>
                            <td><?php echo htmlspecialchars($venta['fechaSalidaVuelo']); ?></td>
                            <td><?php echo htmlspecialchars($venta['fechaReserva']); ?></td>
                            <td>$<?php echo number_format($venta['precioVuelo'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- FOOTER -->
<section class="footer-section">
  <footer>
    <div class="row">
      <div class="col">
        <h3><strong>Contactanos</strong><span class="subrayado"></span></h3>
        <ul>
          <li><i class="bi bi-envelope-at"></i><a href="mailto:vuela@seguro.com.ar">vuela@seguro.com.ar</a></li>
          <li><i class="bi bi-whatsapp"></i><a href="#">+54 9 341 234 5678</a></li>
          <li><i class="bi bi-pen"></i><a href="../CONTACTO/contacto.php">Formulario de Contacto</a></li>
        </ul>
      </div>
      <div class="col">
        <h3><strong>Mapa de sitio</strong><span class="subrayado"></span></h3>
        <ul>
          <li><a href="index.php">Inicio</a></li>
          <li><a href="../VUELOS/vuelos.php">Vuelos</a></li>
          <li><a href="../PROMOCIONES/promociones.php">Promociones</a></li>
          <li><a href="../NOVEDADES/novedades.php">Novedades</a></li>
          <li><a href="">Mi Perfil</a></li>
        </ul>
      </div>
      <div class="col">
        <h3><strong>Ubicación</strong><span class="subrayado"></span></h3>
        <ul>
          <li><a href="https://maps.app.goo.gl/UvsGpUXHgk9GkpYP9" target="_blank">Zeballos 1341</a></li>
          <li><a href="https://maps.app.goo.gl/87YMeSLAp74gH9mc7" target="_blank">Rosario, Santa Fe</a></li>
          <li><a href="https://maps.app.goo.gl/u94xc8o8xowqeTuz8" target="_blank">Argentina</a></li>
        </ul>
      </div>
      <div class="col">
        <h3><strong>Newsletter</strong><span class="subrayado"></span></h3>
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
</body>
</html>