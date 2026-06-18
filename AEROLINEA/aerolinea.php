<?php

  include_once('../conexion.inc');

  if(!empty($_SESSION)){
    if($_SESSION['tipoUsuario'] !== 'admin'){
      header('Location: https://www.google.com/search?client=firefox-b-d&hs=plQV&sca_esv=753862decbe9c4a6&sxsrf=ANbL-n7XpUh_yqPVhp8HESrR9_zURXsM_g:1781756455732&udm=2&fbs=ADc_l-aN0CWEZBOHjofHoaMMDiKpaEWjvZ2Py1XXV8d8KvlI3h8ctYcc-oNQOArn-iW4N6B_ZBAm40m5vBjJxlKgIxwgbWULpp2jmbpEgT1h3S_Yb1Cnejh7HSY-G3-sC5c0tbHsSzk5MrK6D02YBkEu5mrI6oBsROQp6S1ETg7SNjJnwZ79AmErh7j9JYHKKx8iozW1EK8cBt6ItdrTdElNzPE9ipyfOQ&q=ladron&sa=X&ved=2ahUKEwiH4_qE-I-VAxWzq5UCHTWcCcEQtKgLegQIFhAB&biw=1920&bih=947&dpr=1
      ');
    };
  }

  $aerolinea = null;
  if(isset($_POST['id'])){

    $buscarQuery = 'SELECT * FROM aerolineas WHERE codAerolinea = ?';

    $id = $_POST['id'];

    $stmt = mysqli_prepare($link, $buscarQuery);

    mysqli_stmt_bind_param($stmt,"i",$id);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $aerolinea = mysqli_fetch_assoc($rta);
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['estado'] === 'nuevo'){

    $validarQuery = 'SELECT nombreAerolinea FROM aerolineas WHERE nombreAerolinea = ?';

    $nombre = $_POST['nombre'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "s", $nombre);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row1 = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);


    $validarQuery = 'SELECT codigoIATA FROM aerolineas WHERE codigoIATA = ?';

    $cod = $_POST['codigoIATA'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "s", $cod);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $row2 = mysqli_fetch_assoc($rta);

    mysqli_stmt_close($stmt);


    if (!empty($row1) || !empty($row2)) {

      echo 'error';

    } else {

      $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

      $stmt2 = mysqli_prepare($link, 
      'INSERT INTO aerolineas VALUES (NULL, ?, ?, ?, ?, ?)');

      mysqli_stmt_bind_param($stmt2, 'sssss',
      $_POST['nombre'],
      $_POST['codigoIATA'],
      $_POST['desc'],
      $_POST['codigoPAIS'],
      $hashedPassword,);

      mysqli_stmt_execute($stmt2);
      
      mysqli_stmt_close($stmt2);  

      echo 'aerolinea registrada';
    };
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['estado'] === 'edicion'){

    $validarQuery = 'SELECT * FROM aerolineas WHERE codAerolinea != ?';

    $nombre = $_POST['id'];

    $stmt = mysqli_prepare($link, $validarQuery);

    mysqli_stmt_bind_param($stmt, "i", $nombre);

    mysqli_stmt_execute($stmt);

    $rta = mysqli_stmt_get_result($stmt);

    $array = mysqli_fetch_all($rta, MYSQLI_ASSOC); 

    mysqli_stmt_close($stmt);


    foreach($array as $aero) {

      if($aero['nombreAerolinea'] === $_POST['nombre'] || $aero['codigoIATA'] === $_POST['codigoIATA']){

        echo'error';

        return;
      }

    }

      $id = $_POST['id'];

      $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

      $stmt2 = mysqli_prepare($link, 
      'UPDATE aerolineas SET 
        nombreAerolinea = ?,
        codigoIATA = ?,
        descripcionAerolinea = ?,
        codigoPais = ?,
        claveAerolinea = ? WHERE codAerolinea = ?'
      );

      mysqli_stmt_bind_param($stmt2, 'sssssi',
      $_POST['nombre'],
      $_POST['codigoIATA'],
      $_POST['desc'],
      $_POST['codigoPAIS'],
      $hashedPassword,
      $_POST['id']
      );

      mysqli_stmt_execute($stmt2);
      
      mysqli_stmt_close($stmt2);  

      header('Location: ../AEROLINEA/aerolinea-lista.php');
  };
  
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Contacto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
</head>
<body>
<!-- NAVBAR -->
<div class="header-wrapper">
  <nav class="navbar-custom">
    <div class="logo-wrap">
      <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
    </div>
    <div class="nav-links">
      <a href="../INDEX/index.php">Inicio</a>
      <a href="../VUELOS/vuelos.php">Vuelos</a>
      <a href="../NOVEDADES/novedades.php"  >Novedades</a>
      <a href="../PROMOCIONES/promociones.php" >Promociones</a>
    </div>
    <div class="nav-right">
      <?php if (!empty($_SESSION)): ?>
          <div class="foto-perfil">
              <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
                <circle cx="21" cy="10" r="9" fill="#ffffff"/>
                <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
              </svg>
          </div>
        <span class="text-white me-2"><a href="../PERFIL/perfiles.php" style="text-decoration: none; color: white">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?><a></strong></span>
        <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesion</a>
      <?php else: ?>
        <div class="foto-perfil">
          <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
            <circle cx="21" cy="10" r="9" fill="#ffffff"/>
            <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
          </svg>
        </div>
        <?php if(empty($_SESSION)): ?>
          <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </nav>
  
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Alta de aerolinea</li>
        </ol>
    </nav>

</div>
 
  <div class="contacto-wrapper">
 
    <div class="contacto-form-card">
 
      <h2>Alta de aerolinea</h2>
      <h4>Completá el formulario con los datos requeridos para continuar</h4>

      <?php if(empty($_POST['id'])): ?>
        <form action="aerolinea.php" method="POST">
          <input type="hidden" name="estado" value="nuevo">

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" placeholder="Nombre aerolinea" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Codigo IATA</label>
            <input type="text" class="form-control" name="codigoIATA" placeholder="Codigo IATA " required>
          </div>

          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="clave" placeholder="Contraseña para CEO" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" class="form-control" name="desc" placeholder="Descripción aerolinea" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Codigo pais</label>
            <input type="text" class="form-control" name="codigoPAIS" placeholder="Codigo del pais" required>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-enviar">Enviar</button>
          </div>
        </form>
      <?php elseif(!empty($_POST['id'])): ?>
          <form action="aerolinea.php" method="POST">

          <input type="hidden" name="id" value="<?= $aerolinea['codAerolinea'] ?>">

          <input type="hidden" name="estado" value="edicion">

          <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($aerolinea['nombreAerolinea']) ?>" required>
          </div>

          <div class="mb-3">
              <label class="form-label">Codigo IATA</label>
              <input type="text" class="form-control" name="codigoIATA" value="<?= htmlspecialchars($aerolinea['codigoIATA']) ?>" required>
          </div>


          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="clave" placeholder="Nueva contraseña para CEO" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" class="form-control" name="desc" value="<?= htmlspecialchars($aerolinea['descripcionAerolinea']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Codigo pais</label>
            <input type="text" class="form-control" name="codigoPAIS" value="<?= htmlspecialchars($aerolinea['codigoPais']) ?>" required>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-enviar">Enviar</button>
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