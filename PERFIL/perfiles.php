<?php
    $link = null;
    include_once('../conexion.inc');
    if (!$link) {
        die("Error de conexión a la base de datos.");
    }
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
                    <a href="../AEROLINEA/aerolinea.php" style="text-decoration: none; color:white;"> Añadir Aerolinea</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i>
                <a href="../AEROLINEA/aerolinea-lista.php" style="text-decoration: none; color:white;"> Listado de Aerolineas</a> 
            </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i> Listado de Promociones</button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-journal"></i> Reportes</button>
            </div>
        </div>
    </div>

<!-- PERFIL DE USUARIO -->
<?php elseif(!empty($_SESSION) && $_SESSION['tipoUsuario'] === 'usuario'): ?>
    <div class="contacto-wrapper">
        <div class="contacto-form-card">

            <h2>Panel de <?php echo $_SESSION['tipoUsuario'] ?></h2>
            <h4>Podes utilizar las funciones de abajo.</h4>
            
            <div class="d-flex justify-content-center">
                <button class="btn-enviar"><i class="bi bi-airplane"></i> 
                    <a href="" style="text-decoration: none; color:white;"> Resevas activas</a>
                </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-list"></i>
                <a href="" style="text-decoration: none; color:white;"> Historial de compras</a> 
            </button>
            </div>
            <div class="d-flex justify-content-center" style="margin-top: 10px;">
                <button class="btn-enviar"><i class="bi bi-journal-plus"></i> Modificar perfil</button>
            </div>
        </div>
    </div>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>