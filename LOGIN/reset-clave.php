<?php

  $link = null;
  include_once('../conexion.inc');
  if (!$link) {
      die("Error de conexión a la base de datos.");
  }

  $tokenValido = false;
  $message = '';
  $exito = false;

  // El token puede venir por GET (al abrir el link del mail) o por POST (al enviar el formulario)
  $token = $_GET['token'] ?? ($_POST['token'] ?? '');

  if ($token === '') {
    $message = 'Falta el token de recuperación.';
  } else {

    $buscarQuery = 'SELECT codUsuario FROM usuarios WHERE tokenReset = ? AND tokenResetExpira > NOW()';
    $stmt = mysqli_prepare($link, $buscarQuery);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $rta = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($rta);
    mysqli_stmt_close($stmt);

    if ($row) {
      $tokenValido = true;
    } else {
      $message = 'El enlace de recuperación no es válido o ya expiró. Solicitá uno nuevo.';
    }
  }

  if ($tokenValido && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['clave'] !== $_POST['clave_confirmar']) {

      $message = 'Las contraseñas no coinciden.';

    } else {

      $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

      $updateQuery = 'UPDATE usuarios SET claveUsuario = ?, tokenReset = NULL, tokenResetExpira = NULL WHERE codUsuario = ?';
      $stmt2 = mysqli_prepare($link, $updateQuery);
      mysqli_stmt_bind_param($stmt2, "si", $hashedPassword, $row['codUsuario']);
      mysqli_stmt_execute($stmt2);
      mysqli_stmt_close($stmt2);

      $exito = true;
      $message = 'Tu contraseña fue actualizada correctamente. Ya podés iniciar sesión.';
    }
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Restablecer contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="login.css">
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
          <button class="btn-registro"><a href="login.php" style="text-decoration:none; color:white;">Iniciar sesión</a></button>
        </div>
      </nav>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Restablecer contraseña</li>
        </ol>
      </nav>
    </div>
  </section>

  <div class="contacto-wrapper">

    <div class="contacto-form-card">

      <h2>Restablecer contraseña</h2>
      <h4>Ingresá tu nueva contraseña</h4>

      <?php if($message): ?>
        <div class="alert alert-<?php echo $exito ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if($tokenValido && !$exito): ?>
      <form action="reset-clave.php" method="POST">

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="mb-3">
          <label class="form-label">Nueva contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="clave" id="clave" placeholder="Nueva contraseña" required minlength="6">
            <button type="button" class="btn btn-outline-secondary" onclick="var i=document.getElementById('clave');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirmar contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="clave_confirmar" id="clave_confirmar" placeholder="Repetí la contraseña" required minlength="6">
            <button type="button" class="btn btn-outline-secondary" onclick="var i=document.getElementById('clave_confirmar');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn-enviar">Actualizar contraseña</button>
        </div>

      </form>
      <?php elseif($exito): ?>
        <a href="login.php" class="btn-enviar" style="text-decoration:none; display:inline-block;">Iniciar sesión</a>
      <?php else: ?>
        <a href="olvide-clave.php" class="btn-enviar" style="text-decoration:none; display:inline-block;">Solicitar un nuevo link</a>
      <?php endif; ?>

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
            <?php if (isset($_SESSION['nombreUsuario'])): ?>
                <li><a href="../PERFIL/perfiles.php">Mi Perfil</a></li>
            <?php else: ?>
                <li><a href="../LOGIN/login.php">Mi Perfil</a></li>
            <?php endif; ?>
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

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
