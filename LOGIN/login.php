<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

$message = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
  if(isset($_SESSION['codUsuario'])){
    header('Location: ../INDEX/index.php');
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $message = '';

    $validarQuery = 'SELECT * FROM usuarios WHERE emailUsuario = ?';

    $email = $_POST['mail'];

    $clave = $_POST['clave'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);

    if ($row) {

      $claveHash = $row['claveUsuario'];

      if(password_verify($clave, $claveHash)){

        if (isset($row['emailVerificado']) && $row['emailVerificado'] == 0) {

          $message = 'Tenés que verificar tu email antes de iniciar sesión. Revisá tu casilla de correo.';

        } else {

          $_SESSION['codUsuario'] = $row['codUsuario'];

          $_SESSION['nombreUsuario'] = $row['nombreUsuario'];

          $_SESSION['tipoUsuario'] = $row['tipoUsuario'];

          $_SESSION['emailUsuario'] = $row['emailUsuario'];

          $_SESSION['telefonoUsuario'] = $row['telefonoUsuario'];

          header('Location: ../INDEX/index.php');
          exit;

        }

      } else {

        $message = ' la contraseña es incorrecta.';

      }

    } else {
      
      $message = ' el email no está registrado.';

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
    <link rel="stylesheet" href="login.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
 
  <!-- NAVBAR -->
  <section class="navbar-section">
    <div class="header-wrapper">
      <nav class="navbar-custom">
        <div class="logo-wrap">
          <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
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
          <li class="breadcrumb-item active" aria-current="page">Inicio de sesión</li>
        </ol>
      </nav>
    </div>
  </section>
 
  <div class="contacto-wrapper">
 
    <div class="contacto-form-card">
      
      <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-check-circle"></i> Éxito!</strong> <?php echo $_SESSION['message']; $_SESSION['message'] = ''; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (isset($message) && $message !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong> <?php echo $message; $message = ''; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
 
      <h2>Ingresa a tu cuenta</h2>
      <h4>Completa con los datos de tu cuenta</h4>

      <?php if($message): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
 
      <form action="login.php" method="POST">
 
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="mail" placeholder="Tu email" required>
        </div>
 
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="clave" id="clave" placeholder="Tu contraseña" required>
            <button type="button" class="btn btn-outline-secondary" onclick="var i=document.getElementById('clave');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="form-text" id="basic-addon4">No tenes una cuenta? <a href="../REGISTER/registrar.php">Registrate aquí</a></div>

        <div class="form-text" id="basic-addon4">Representas a una aerolinea? <a href="../AEROLINEA/aerolinea-login.php">Ingresa aquí</a></div>

        <div class="form-text" id="basic-addon4">Olvidaste tu contraseña? <a href="olvide-clave.php">Recuperala aquí</a></div>
 
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
          <h3><strong>Contactanos</strong><div class="subrayado"></div></h3>
          <ul>
            <li><i class="bi bi-envelope-at"></i><a>vuela@seguro.com.ar</a></li>
            <li><i class="bi bi-whatsapp"></i><a>+54 9 341 234 5678</a></li>
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
