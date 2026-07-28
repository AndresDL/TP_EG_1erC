<?php

  $link = null;
  include_once('../conexion.inc');
  include_once('../mailer.inc');
  if (!$link) {
      die("Error de conexión a la base de datos.");
  }

  $message = '';

  if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $message = '';

    $validarQuery = 'SELECT emailUsuario FROM usuarios WHERE emailUsuario = ?';
    
    $email = $_POST['email'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);

    if ($row) {

      $message = 'el email ya está registrado.';

    } else {

      $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

      $fullname = "{$_POST['nombre1']} {$_POST['nombre2']}";

      $tokenVerificacion = bin2hex(random_bytes(32));

      $stmt2 = mysqli_prepare($link,
      'INSERT INTO usuarios (codUsuario, nombreUsuario, claveUsuario, tipoUsuario, emailUsuario, telefonoUsuario, emailVerificado, tokenVerificacion)
       VALUES (NULL, ?, ?, "usuario", ?, ?, 0, ?)'); 

      if (!$stmt2) {
        $message = 'Error al preparar la consulta: ' . mysqli_error($link);
      } else {

      mysqli_stmt_bind_param($stmt2, 'sssss',
      $fullname,
      $hashedPassword,
      $_POST['email'],
      $_POST['telefono'],
      $tokenVerificacion);

      if (mysqli_stmt_execute($stmt2)) {

        mysqli_stmt_close($stmt2);

        $linkVerificacion = urlBase() . '/REGISTER/verificar.php?token=' . $tokenVerificacion;

        $html = plantillaMail(
          '¡Bienvenido a VuelaSeguro!',
          'Gracias por registrarte, ' . htmlspecialchars($fullname) . '. Para activar tu cuenta y poder iniciar sesión, confirmá tu email haciendo clic en el siguiente botón:',
          'Verificar mi cuenta',
          $linkVerificacion
        );

        enviarMail($_POST['email'], 'Confirmá tu cuenta en VuelaSeguro', $html);

        $_SESSION['message'] = 'Tu cuenta fue creada. Te enviamos un mail para verificar tu dirección de correo antes de poder iniciar sesión.';

        header('Location: ../LOGIN/login.php');
        exit;

      } else {
        $message = 'Ocurrió un error al crear la cuenta: ' . mysqli_stmt_error($stmt2);
      }
      }
    };
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Contacto</title>
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
          <li class="breadcrumb-item active" aria-current="page">Crear cuenta</li>
        </ol>
      </nav>
    </div>
  </section>

  <div class="contacto-wrapper">

    <div class="contacto-form-card">

      <h2>Creación de cuenta</h2>
      <h4>Completá el formulario con los datos requeridos para continuar</h4>

      <?php if($message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong> <?php echo $message;?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form action="registrar.php" method="POST">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" class="form-control" name="nombre1"
                 placeholder="Tu nombre"
                 pattern="[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s]{2,50}"
                 minlength="2" maxlength="50"
                 title="Solo letras y espacios, entre 2 y 50 caracteres"
                 required>
          <div class="form-text text-muted">Solo letras, mínimo 2 caracteres.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Apellido</label>
          <input type="text" class="form-control" name="nombre2"
                 placeholder="Tu apellido"
                 pattern="[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s]{2,50}"
                 minlength="2" maxlength="50"
                 title="Solo letras y espacios, entre 2 y 50 caracteres"
                 required>
          <div class="form-text text-muted">Solo letras, mínimo 2 caracteres.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="clave" id="claveReg"
                   placeholder="Mínimo 8 caracteres"
                   minlength="8" maxlength="72"
                   pattern="(?=.*[0-9])(?=.*[A-Za-z]).{8,}"
                   title="Mínimo 8 caracteres, debe incluir al menos una letra y un número"
                   required>
            <button type="button" class="btn btn-outline-secondary" tabindex="-1"
                    onclick="var i=document.getElementById('claveReg');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';"
                    aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="form-text text-muted">Mínimo 8 caracteres con al menos una letra y un número.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email"
                 placeholder="ejemplo@correo.com"
                 maxlength="100"
                 title="Ingresá un email válido, por ejemplo: nombre@correo.com"
                 required>
        </div>

        <div class="mb-3">
          <label class="form-label">Teléfono</label>
          <input type="tel" class="form-control" name="telefono"
                 placeholder="Ej: 3411234567"
                 pattern="[0-9\+\-\s]{7,20}"
                 minlength="7" maxlength="20"
                 title="Solo números, espacios, + o -, entre 7 y 20 caracteres. Ej: 3411234567"
                 required>
          <div class="form-text text-muted">Solo números, entre 7 y 20 dígitos.</div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn-enviar">Crear cuenta</button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
