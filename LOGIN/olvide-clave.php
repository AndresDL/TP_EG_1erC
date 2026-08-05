<?php

  $link = null;
  include_once('../conexion.inc');
  include_once('../mailer.inc');
  if (!$link) {
      die("Error de conexión a la base de datos.");
  }

  $message = '';
  $enviado = false;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['mail'];

    $buscarQuery = 'SELECT codUsuario, nombreUsuario FROM usuarios WHERE emailUsuario = ?';

    $stmt = mysqli_prepare($link, $buscarQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $rta = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($rta);
    mysqli_stmt_close($stmt);

    // Por seguridad mostramos el mismo mensaje exista o no el email,
    // así no revelamos qué direcciones están registradas.
    $message = 'Si el email ingresado existe en nuestro sistema, te enviamos un link para restablecer tu contraseña.';
    $enviado = true;

    if ($row) {

      $tokenReset = bin2hex(random_bytes(32));

      $updateQuery = 'UPDATE usuarios SET tokenReset = ?, tokenResetExpira = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE codUsuario = ?';
      $stmt2 = mysqli_prepare($link, $updateQuery);
      mysqli_stmt_bind_param($stmt2, "si", $tokenReset, $row['codUsuario']);
      mysqli_stmt_execute($stmt2);
      mysqli_stmt_close($stmt2);

      $linkReset = urlBase() . '/LOGIN/reset-clave.php?token=' . $tokenReset;

      $html = plantillaMail(
        'Restablecé tu contraseña',
        'Hola ' . htmlspecialchars($row['nombreUsuario']) . ', recibimos una solicitud para restablecer tu contraseña en VuelaSeguro. Este link es válido por 1 hora. Si no fuiste vos, podés ignorar este mail.',
        'Restablecer contraseña',
        $linkReset
      );

      enviarMail($email, 'Restablecé tu contraseña – VuelaSeguro', $html);
    }
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Recuperar contraseña</title>
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
          <li class="breadcrumb-item active" aria-current="page">Recuperar contraseña</li>
        </ol>
      </nav>
    </div>
  </section>

  <div class="contacto-wrapper">

    <div class="contacto-form-card">

      <h2>Recuperar contraseña</h2>
      <h4>Ingresá el email de tu cuenta y te mandamos un link para restablecerla</h4>

      <?php if($enviado): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if(!$enviado): ?>
      <form action="olvide-clave.php" method="POST">

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="mail" placeholder="Tu email" required>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn-enviar">Enviar link</button>
        </div>

      </form>
      <?php endif; ?>

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
