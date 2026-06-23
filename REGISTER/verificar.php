<?php

  $link = null;
  include_once('../conexion.inc');
  if (!$link) {
      die("Error de conexión a la base de datos.");
  }

  $exito = false;
  $mensaje = '';

  if (isset($_GET['token']) && $_GET['token'] !== '') {

    $token = $_GET['token'];

    $buscarQuery = 'SELECT codUsuario, emailVerificado FROM usuarios WHERE tokenVerificacion = ?';

    $stmt = mysqli_prepare($link, $buscarQuery);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $rta = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($rta);
    mysqli_stmt_close($stmt);

    if ($row) {

      $updateQuery = 'UPDATE usuarios SET emailVerificado = 1, tokenVerificacion = NULL WHERE codUsuario = ?';
      $stmt2 = mysqli_prepare($link, $updateQuery);
      mysqli_stmt_bind_param($stmt2, "i", $row['codUsuario']);
      mysqli_stmt_execute($stmt2);
      mysqli_stmt_close($stmt2);

      $exito = true;
      $mensaje = 'Tu cuenta fue verificada correctamente. Ya podés iniciar sesión.';

    } else {
      $mensaje = 'El link de verificación no es válido o ya fue utilizado.';
    }

  } else {
    $mensaje = 'Falta el token de verificación.';
  }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Verificación de cuenta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="register.css">
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
          <button class="btn-registro"><a href="../LOGIN/login.php" style="text-decoration: none; color: white;">Iniciar sesión</a></button>
        </div>
      </nav>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Verificación de cuenta</li>
        </ol>
      </nav>
    </div>
  </section>

  <div class="contacto-wrapper">

    <div class="contacto-form-card" style="text-align:center;">

      <?php if ($exito): ?>
        <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: var(--verde);"></i>
        <h2 style="margin-top: 16px;">¡Cuenta verificada!</h2>
        <h4 style="border-bottom:none;"><?php echo htmlspecialchars($mensaje); ?></h4>
        <a href="../LOGIN/login.php" class="btn-enviar" style="text-decoration:none; display:inline-block; margin-top: 10px;">Iniciar sesión</a>
      <?php else: ?>
        <i class="bi bi-x-circle-fill" style="font-size: 3rem; color: var(--rojo);"></i>
        <h2 style="margin-top: 16px;">No pudimos verificar tu cuenta</h2>
        <h4 style="border-bottom:none;"><?php echo htmlspecialchars($mensaje); ?></h4>
        <a href="../REGISTER/registrar.php" class="btn-enviar" style="text-decoration:none; display:inline-block; margin-top: 10px;">Volver a registrarme</a>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
