<?php


    $link = null;

    include_once('../conexion.inc');
    if (!$link) {
        die("Error de conexión a la base de datos.");
    }

    $id = $_SESSION['codUsuario'];

    $buscarQuery = 'SELECT * FROM usuarios WHERE codUsuario = ?';

    $stmt = mysqli_prepare($link, $buscarQuery);

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);

    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $id = $_SESSION['codUsuario'];

        $nombre = $_POST['nombre'];

        $email = $_POST['email'];

        $telefono = $_POST['telefono'];

        $validarQuery = 'SELECT * FROM usuarios WHERE emailUsuario = ? AND codUsuario != ?';

        $stmt = mysqli_prepare($link, $validarQuery);

        mysqli_stmt_bind_param($stmt, "si", $email, $id);

        mysqli_stmt_execute($stmt);

        $rta = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($rta) > 0) {

          $message = 'El email ingresado ya está registrado por otro usuario. Por favor, utiliza un email diferente.';
            
        } else {

            $_SESSION['nombreUsuario'] = $nombre;

            $_SESSION['emailUsuario'] = $email;

            $_SESSION['telefonoUsuario'] = $telefono;

            $updateQuery = 'UPDATE usuarios SET nombreUsuario = ?, emailUsuario = ?, telefonoUsuario = ? WHERE codUsuario = ?';

            $stmt = mysqli_prepare($link, $updateQuery);

            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $telefono, $id);

            if (mysqli_stmt_execute($stmt)) {
                
              $_SESSION['message'] = ' Perfil editato exitosamente!';

              header('Location: ../PERFIL/perfiles.php');

            } else {

              $message = 'Error al actualizar el perfil, intente nuevamente mas tarde';

            }

            mysqli_stmt_close($stmt);
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Perfil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="perfiles.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
            <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modificar Perfil</li>
            </ol>
        </nav>
    </div>
  </section>
</header>
 
  <div class="contacto-wrapper">
 
    <div class="contacto-form-card">

      <?php if (isset($message) && $message !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="bi bi-exclamation-triangle"></i> Error!</strong> <?php echo $message; $message = ''; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
 
      <h2>Modificación de perfil</h2>
      <h4>Completá el formulario con los datos requeridos para continuar</h4>
 
      <form action="perfil-modificar.php" method="POST">
 
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($row['nombreUsuario']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($row['emailUsuario']); ?>" placeholder="Tu email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Telefono</label>
          <input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($row['telefonoUsuario']); ?>" placeholder="Tu telefono" required>
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
            <li><a href="../PERFIL/perfil.php">Mi Perfil</a></li>
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