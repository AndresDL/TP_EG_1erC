<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

$validarQuery = 'SELECT * FROM aerolineas';

$stmt = mysqli_prepare($link, $validarQuery);

mysqli_stmt_execute($stmt);

$rta = mysqli_stmt_get_result($stmt);

$array = mysqli_fetch_all($rta, MYSQLI_ASSOC); 

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Aerolineas-listado</title>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="aerolinea.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
     
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
          <a href="../NOVEDADES/novedades.php" class="active">Novedades</a>
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
                <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Perfil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Listado de aerolineas</li>
            </ol>
        </nav>
    </div>
  </section>
</header>

    <div class="aerolineas-wrapper">
        <div class="aerolineas-header">
            <h2>Aerolineas activas</h2>
            <button class="btn-accion btn-primary-sm">
                <i class="bi bi-plus-circle"></i><a href="aerolinea.php" style="text-decoration: none; color: white;"> Agregar</a>
            </button>
        </div>
        <div class="d-flex flex-column gap-3">
            <?php foreach($array as $aero){
                echo   
                    '<div class="aerolinea-card">
                        <div class="aerolinea-img">'.$aero['codigoIATA'].'</div>
                        <div class="aerolinea-info">
                            <div class="aerolinea-nombre">'.$aero['nombreAerolinea'].'</div>
                            <div class="aerolinea-categoria">'.$aero['drescripcionAerolinea'].'</div>
                            <span class="aerolinea-badge badge-stock">'.$aero['codigoPais'].'</span>
                        </div>
                        <div class="aerolinea-acciones">
                            <button class="btn-accion">Editar</button>
                            <button class="btn-accion btn-danger-sm">Eliminar</button>
                        </div>
                    </div>'
                ;
            } ?>
        </div>
    </div>

    <!-- FOOTER -->
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
</body>
</html>