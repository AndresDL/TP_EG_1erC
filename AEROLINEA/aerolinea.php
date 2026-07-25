<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

if (!empty($_SESSION) && $_SESSION['tipoUsuario'] !== 'admin') {
    header('Location: ../LOGIN/login.php');
    exit();
}

$esEdicion = false;
$aerolinea = null;
$message   = '';

// ── Cargar datos para edición (viene por POST con estado=pendiente desde la lista) ──
if (isset($_POST['id']) && $_POST['estado'] === 'pendiente') {
    $esEdicion = true;
    $id = (int)$_POST['id'];
    $stmt = mysqli_prepare($link, 'SELECT * FROM aerolineas WHERE codAerolinea = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $aerolinea = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// ── ALTA ────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['estado'] === 'nuevo') {

    $nombre = trim($_POST['nombre']);
    $cod    = strtoupper(trim($_POST['codigoIATA']));
    $clave  = $_POST['clave'];
    $desc   = trim($_POST['desc']);
    $pais   = strtoupper(trim($_POST['codigoPAIS']));

    // Verificar nombre duplicado
    $stmt = mysqli_prepare($link, 'SELECT codAerolinea FROM aerolineas WHERE nombreAerolinea = ?');
    mysqli_stmt_bind_param($stmt, 's', $nombre);
    mysqli_stmt_execute($stmt);
    $row1 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Verificar IATA duplicado
    $stmt = mysqli_prepare($link, 'SELECT codAerolinea FROM aerolineas WHERE codigoIATA = ?');
    mysqli_stmt_bind_param($stmt, 's', $cod);
    mysqli_stmt_execute($stmt);
    $row2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!empty($row1)) {
        $message = 'El nombre de la aerolínea ya está registrado.';
    } elseif (!empty($row2)) {
        $message = 'El código IATA ya está registrado.';
    } else {
        $hash  = password_hash($clave, PASSWORD_BCRYPT);
        $stmt2 = mysqli_prepare($link,
            'INSERT INTO aerolineas (nombreAerolinea, codigoIATA, descripcionAerolinea, codigoPais, claveAerolinea)
             VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt2, 'sssss', $nombre, $cod, $desc, $pais, $hash);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $_SESSION['message'] = 'Aerolínea creada exitosamente.';
        header('Location: ../AEROLINEA/aerolinea-lista.php');
        exit;
    }
}

// ── EDICIÓN ──────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['estado'] === 'edicion') {

    $esEdicion = true;
    $id        = (int)$_POST['id'];
    $nombre    = trim($_POST['nombre']);
    $cod       = strtoupper(trim($_POST['codigoIATA']));
    $desc      = trim($_POST['desc']);
    $pais      = strtoupper(trim($_POST['codigoPAIS']));
    $nuevaClave = trim($_POST['clave']);

    // Volver a cargar datos actuales para comparar contraseña
    $stmt = mysqli_prepare($link, 'SELECT * FROM aerolineas WHERE codAerolinea = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $aerolinea = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Verificar nombre duplicado (excluyendo la propia)
    $stmt = mysqli_prepare($link, 'SELECT codAerolinea FROM aerolineas WHERE nombreAerolinea = ? AND codAerolinea != ?');
    mysqli_stmt_bind_param($stmt, 'si', $nombre, $id);
    mysqli_stmt_execute($stmt);
    $row1 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Verificar IATA duplicado (excluyendo la propia)
    $stmt = mysqli_prepare($link, 'SELECT codAerolinea FROM aerolineas WHERE codigoIATA = ? AND codAerolinea != ?');
    mysqli_stmt_bind_param($stmt, 'si', $cod, $id);
    mysqli_stmt_execute($stmt);
    $row2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!empty($row1)) {
        $message = 'El nombre de la aerolínea ya está registrado por otra aerolínea.';
    } elseif (!empty($row2)) {
        $message = 'El código IATA ya está registrado por otra aerolínea.';
    } elseif ($nuevaClave !== '' && password_verify($nuevaClave, $aerolinea['claveAerolinea'])) {
        $message = 'La nueva contraseña debe ser diferente a la actual.';
    } else {
        // Si se ingresó nueva clave, hashearla; si no, mantener la anterior
        if ($nuevaClave !== '') {
            $hash = password_hash($nuevaClave, PASSWORD_BCRYPT);
        } else {
            $hash = $aerolinea['claveAerolinea'];
        }
        $stmt2 = mysqli_prepare($link,
            'UPDATE aerolineas SET
                nombreAerolinea = ?,
                codigoIATA = ?,
                descripcionAerolinea = ?,
                codigoPais = ?,
                claveAerolinea = ?
             WHERE codAerolinea = ?');
        mysqli_stmt_bind_param($stmt2, 'sssssi', $nombre, $cod, $desc, $pais, $hash, $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $_SESSION['message'] = 'Aerolínea editada exitosamente.';
        header('Location: ../AEROLINEA/aerolinea-lista.php');
        exit;
    }
}

$tituloForm     = $esEdicion ? 'Editar aerolínea'   : 'Alta de aerolínea';
$tituloBread    = $esEdicion ? 'Editar aerolínea'   : 'Alta de aerolínea';
$subtituloForm  = $esEdicion ? 'Modificá los datos de la aerolínea. Dejá la contraseña en blanco para no cambiarla.' : 'Completá el formulario con los datos requeridos para continuar.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – <?= $tituloForm ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
      <a href="../NOVEDADES/novedades.php">Novedades</a>
      <a href="../PROMOCIONES/promociones.php">Promociones</a>
    </div>
    <div class="nav-right">
      <?php if (!empty($_SESSION)): ?>
        <div class="foto-perfil">
          <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
            <circle cx="21" cy="10" r="9" fill="#ffffff"/>
            <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
          </svg>
        </div>
        <span class="text-white me-2">
          <a href="../PERFIL/perfiles.php" style="text-decoration:none;color:white;">Hola, <strong><?= htmlspecialchars($_SESSION['nombreUsuario']) ?></strong></a>
        </span>
        <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesión</a>
      <?php else: ?>
        <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </nav>

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
      <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Perfil</a></li>
      <li class="breadcrumb-item"><a href="../AEROLINEA/aerolinea-lista.php">Aerolíneas</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= $tituloBread ?></li>
    </ol>
  </nav>
</div>

<div class="contacto-wrapper">
  <div class="contacto-form-card">

    <h2><?= $tituloForm ?></h2>
    <h4><?= $subtituloForm ?></h4>

    <?php if ($message !== ''): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle"></i> Error:</strong> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form action="aerolinea.php" method="POST">

      <input type="hidden" name="estado" value="<?= $esEdicion ? 'edicion' : 'nuevo' ?>">
      <?php if ($esEdicion): ?>
        <input type="hidden" name="id" value="<?= (int)$aerolinea['codAerolinea'] ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre"
               value="<?= $esEdicion ? htmlspecialchars($aerolinea['nombreAerolinea']) : '' ?>"
               placeholder="Nombre de la aerolínea"
               minlength="2" maxlength="100"
               title="Ingresá el nombre de la aerolínea (mínimo 2 caracteres)"
               required>
      </div>

      <div class="mb-3">
        <label class="form-label">Código IATA <small class="text-muted">(2 o 3 letras, ej: AA, LAN)</small></label>
        <input type="text" class="form-control" name="codigoIATA"
               value="<?= $esEdicion ? htmlspecialchars($aerolinea['codigoIATA']) : '' ?>"
               placeholder="Ej: AA"
               pattern="[A-Za-z]{2,3}" maxlength="3"
               title="El código IATA debe tener 2 o 3 letras (ej: AA, LAN)"
               required>
      </div>

      <div class="mb-3">
        <label class="form-label">
          <?= $esEdicion ? 'Nueva contraseña <small class="text-muted fw-normal">(dejá en blanco para no cambiarla)</small>' : 'Contraseña para CEO' ?>
        </label>
        <div class="input-group">
          <input type="password" class="form-control" name="clave" id="claveAero"
                 placeholder="<?= $esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña para el CEO' ?>"
                 <?= $esEdicion ? '' : 'required' ?>>
          <button type="button" class="btn btn-outline-secondary" tabindex="-1"
                  onclick="var i=document.getElementById('claveAero');i.type=i.type==='password'?'text':'password';this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';"
                  aria-label="Mostrar u ocultar contraseña">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <?php if ($esEdicion): ?>
          <div class="form-text text-muted">Si ingresás una nueva contraseña, debe ser diferente a la actual.</div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <input type="text" class="form-control" name="desc"
               value="<?= $esEdicion ? htmlspecialchars($aerolinea['descripcionAerolinea']) : '' ?>"
               placeholder="Descripción de la aerolínea"
               minlength="5" maxlength="200"
               title="Ingresá una descripción (mínimo 5 caracteres)"
               required>
      </div>

      <div class="mb-3">
        <label class="form-label">País <small class="text-muted">(el código se completa automáticamente)</small></label>
        <select class="form-control" name="pais_nombre" id="selectPais"
                onchange="document.getElementById('codigoPAIS').value=this.options[this.selectedIndex].dataset.cod;"
                required>
          <option value="" data-cod="">-- Seleccioná un país --</option>
          <?php
          $paises = [
            'Argentina'=>'AR','Bolivia'=>'BO','Brasil'=>'BR','Chile'=>'CL','Colombia'=>'CO',
            'Ecuador'=>'EC','Paraguay'=>'PY','Perú'=>'PE','Uruguay'=>'UY','Venezuela'=>'VE',
            'México'=>'MX','Cuba'=>'CU','Costa Rica'=>'CR','Panamá'=>'PA','Guatemala'=>'GT',
            'Honduras'=>'HN','El Salvador'=>'SV','Nicaragua'=>'NI','República Dominicana'=>'DO',
            'Puerto Rico'=>'PR','España'=>'ES','Estados Unidos'=>'US','Canadá'=>'CA',
            'Reino Unido'=>'GB','Francia'=>'FR','Alemania'=>'DE','Italia'=>'IT','Portugal'=>'PT',
            'Países Bajos'=>'NL','Bélgica'=>'BE','Suiza'=>'CH','Austria'=>'AT','Polonia'=>'PL',
            'Suecia'=>'SE','Noruega'=>'NO','Dinamarca'=>'DK','Finlandia'=>'FI','Rusia'=>'RU',
            'China'=>'CN','Japón'=>'JP','Corea del Sur'=>'KR','India'=>'IN','Australia'=>'AU',
            'Nueva Zelanda'=>'NZ','Sudáfrica'=>'ZA','Emiratos Árabes'=>'AE','Qatar'=>'QA',
            'Israel'=>'IL','Turquía'=>'TR','Marruecos'=>'MA','Egipto'=>'EG',
          ];
          asort($paises);
          $codigoPaisActual = $esEdicion ? strtoupper($aerolinea['codigoPais']) : '';
          foreach ($paises as $nombre => $cod):
            $selected = ($codigoPaisActual === $cod) ? 'selected' : '';
          ?>
            <option value="<?= $nombre ?>" data-cod="<?= $cod ?>" <?= $selected ?>><?= $nombre ?> (<?= $cod ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Código de país <small class="text-muted">(se completa solo al elegir el país)</small></label>
        <input type="text" class="form-control" name="codigoPAIS" id="codigoPAIS"
               value="<?= $esEdicion ? htmlspecialchars($aerolinea['codigoPais']) : '' ?>"
               placeholder="Ej: AR"
               pattern="[A-Za-z]{2,3}" maxlength="3"
               title="Código de país ISO de 2 o 3 letras"
               readonly
               style="background:#f5f7fb; cursor:not-allowed;"
               required>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a href="../AEROLINEA/aerolinea-lista.php" class="btn btn-outline-secondary" style="border-radius:10px; padding:12px 24px; font-weight:600;">Cancelar</a>
        <button type="submit" class="btn-enviar"><?= $esEdicion ? 'Guardar cambios' : 'Crear aerolínea' ?></button>
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
          <li><a href="../PERFIL/perfiles.php">Mi Perfil</a></li>
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
