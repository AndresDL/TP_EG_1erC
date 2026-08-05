<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

  if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $validarQuery = 'SELECT * FROM aerolineas WHERE nombreAerolinea = ?';

    $email = $_POST['nombre'];

    $clave = $_POST['clave'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);

    if ($row) {

      $claveHash = $row['claveAerolinea'];

      if(password_verify($clave, $claveHash)){

        $_SESSION['codUsuario'] = $row['codAerolinea'];
        
        $_SESSION['nombreUsuario'] = $row['nombreAerolinea'];

        $_SESSION['tipoUsuario'] = 'CEO';

        $_SESSION['codigoIATA'] = $row['codigoIATA'];

        $_SESSION['codigoPais'] = $row['codigoPais'];

        header('Location: ../INDEX/index.php');

      } else {

        $message = 'Contraseña incorrecta!';

      }

    } else {
      
      $message = 'Aerolinea inexistente!';

    };
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Contacto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
</head>
<body>
 
  <!-- NAVBAR -->
  <section class="navbar-section">
    <div class="header-wrapper">
      <nav class="navbar-custom">

         <div class="logo-wrap">
          <a href="../INDEX/index.php">
            <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
          </a>
        </div>
        
        <div class="nav-links">
          <a href="../INDEX/index.php">Inicio</a>
          <a href="../VUELOS/vuelos.php">Vuelos</a>
          <a href="../NOVEDADES/novedades.php">Novedades</a>
          <a href="../PROMOCIONES/promociones.php">Promociones</a>
        </div>
        <div class="nav-right">
          <div class="foto-perfil" title="Foto de perfil">
            <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <circle cx="21" cy="10" r="9" fill="#ffffff"/>
              <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
            </svg>
          </div>
          <button class="btn-registro">Iniciar sesión</button>
        </div>
      </nav>
 
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Inicio de sesión aerolinea</li>
        </ol>
      </nav>
    </div>
  </section>
 
  <div class="contacto-wrapper">
 
    <div class="contacto-form-card">

      <?php if (isset($message) && $message !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong> <?php echo $message; $message = ''; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
 
      <h2>Ingresa a la cuenta de tu aerolinea</h2>
      <h4>Completa con los datos de la cuenta</h4>
 
      <form action="aerolinea-login.php" method="POST">
 
        <div class="mb-3">
          <label class="form-label">Nombre de aerolinea</label>
          <input type="text" class="form-control" name="nombre" placeholder="Nombre de la aerolinea" required>
        </div>
 
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="clave" id="claveLogin" placeholder="Contraseña provista por el administrador" required>
            <button type="button" class="btn btn-outline-secondary" tabindex="-1"
                    onclick="var i=document.getElementById('claveLogin');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';"
                    aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
 
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn-enviar">Enviar</button>
        </div>
        
      </form>
 
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
          <?php if (isset($_SESSION['nombreUsuario'])): ?>
              <li><a href="../PERFIL/perfiles.php">Mi Perfil</a></li>
          <?php else: ?>
              <li><a href="../LOGIN/login.php">Mi Perfil</a></li>
          <?php endif; ?>
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
 
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>