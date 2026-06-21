<?php


  $link = null;
  include_once('../conexion.inc');
  if (!$link) {
      die("Error de conexión a la base de datos.");
  }


  if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $_SESSION['message'] = ' su cuenta ha sido creada exitosamente.';

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

      $message = ' el email ya está registrado.';

    } else {
      $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

      $fullname = "{$_POST['nombre1']} {$_POST['nombre2']}";

      $stmt2 = mysqli_prepare($link, 
      'INSERT INTO usuarios VALUES (NULL, ?, ?, "usuario", ?, ?)');

      mysqli_stmt_bind_param($stmt2, 'ssss',
      $fullname,
      $hashedPassword,
      $_POST['email'],
      $_POST['telefono']);

      mysqli_stmt_execute($stmt2);
      
      mysqli_stmt_close($stmt2);  

      header('Location: ../LOGIN/login.php');
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

      <?php if(isset($message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong> <?php echo $message;?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form action="registrar.php" method="POST">
 
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" class="form-control" name="nombre1" placeholder="Tu nombre" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Apellido</label>
          <input type="text" class="form-control" name="nombre2" placeholder="Tu apellido" required>
        </div>
 
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input type="password" class="form-control" name="clave" placeholder="Tu contraseña" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" placeholder="Tu email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Telefono</label>
          <input type="tel" class="form-control" name="telefono" placeholder="Tu telefono" required>
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