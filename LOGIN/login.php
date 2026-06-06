<?php
session_start();
require_once "../CONEXION/conexion.php";

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION['codUsuario'])) {
    header("Location: ../INDEX/index.php");
    exit();
}
$errorLogin    = "";
$errorRegistro = "";
$okRegistro    = "";
$tabActiva = $_GET['tab'] ?? 'login';

// ─── ACCIÓN: LOGIN ────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
    $tabActiva = "login";
    $email = trim($_POST['email']);
    $clave = trim($_POST['clave']);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE emailUsuario = ? AND claveUsuario = ?");
    $stmt->bind_param("ss", $email, $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['codUsuario']    = $usuario['codUsuario'];
        $_SESSION['nombreUsuario'] = $usuario['nombreUsuario'];
        $_SESSION['tipoUsuario']   = $usuario['tipoUsuario'];
        header("Location: ../INDEX/index.php");
        exit();
    } else {
        $errorLogin = "Email o contraseña incorrectos.";
    }
}

// ─── ACCIÓN: REGISTRO ─────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
    $tabActiva = "registro";
    $nombre    = trim($_POST['nombre']);
    $email     = trim($_POST['emailReg']);
    $clave     = trim($_POST['claveReg']);
    $claveConf = trim($_POST['claveConf']);
    $telefono  = trim($_POST['telefono']);

    // Validaciones
    if (empty($nombre) || empty($email) || empty($clave)) {
        $errorRegistro = "Completá todos los campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorRegistro = "El email no tiene un formato válido.";
    } elseif (strlen($clave) > 8) {
        $errorRegistro = "La contraseña no puede tener más de 8 caracteres (límite de la BD).";
    } elseif ($clave !== $claveConf) {
        $errorRegistro = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el email ya existe
        $chk = $conn->prepare("SELECT codUsuario FROM usuarios WHERE emailUsuario = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errorRegistro = "Ya existe una cuenta con ese email.";
        } else {
            $ins = $conn->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, emailUsuario, telefonoUsuario) VALUES (?, ?, 'usuario', ?, ?)");
            $ins->bind_param("ssss", $nombre, $clave, $email, $telefono);
            $ins->execute();

            // Loguear automáticamente
            $_SESSION['codUsuario']    = $conn->insert_id;
            $_SESSION['nombreUsuario'] = $nombre;
            $_SESSION['tipoUsuario']   = 'usuario';
            header("Location: ../INDEX/index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="../INDEX/estilos-globales.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
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
        <a href="login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
      </div>
    </nav>
  </div>
</section>

<!-- CARD -->
<div class="auth-card">

  <!-- PESTAÑAS -->
  <div class="auth-tabs">
    <a class="auth-tab <?= $tabActiva === 'login' ? 'activa' : '' ?>"
       href="?tab=login">
      <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
    </a>
    <a class="auth-tab <?= $tabActiva === 'registro' ? 'activa' : '' ?>"
       href="?tab=registro">
      <i class="bi bi-person-plus me-1"></i>Registrarse
    </a>
  </div>

  <!-- ══ FORM LOGIN ══ -->
  <?php if ($tabActiva === 'login'): ?>

    <?php if ($errorLogin): ?>
      <div class="alerta alerta-error">
        <i class="bi bi-exclamation-circle"></i><?= $errorLogin ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="accion" value="login">

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               placeholder="Tu email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="mb-4">
        <label class="form-label">Contraseña</label>
        <input type="password" name="clave" class="form-control"
               placeholder="Tu contraseña" required>
      </div>

      <button type="submit" class="btn-auth">
        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
      </button>
    </form>

    <p style="text-align:center; margin-top:20px; font-size:0.88rem; color:var(--gris);">
      ¿No tenés cuenta?
      <a href="?tab=registro" style="color:var(--azul); font-weight:600;">Registrate acá</a>
    </p>

  <!-- ══ FORM REGISTRO ══ -->
  <?php else: ?>

    <?php if ($errorRegistro): ?>
      <div class="alerta alerta-error">
        <i class="bi bi-exclamation-circle"></i><?= $errorRegistro ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="accion" value="registro">

      <div class="mb-3">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="nombre" class="form-control"
               placeholder="Tu nombre y apellido" required
               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="emailReg" class="form-control"
               placeholder="Tu email" required
               value="<?= htmlspecialchars($_POST['emailReg'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">
          Teléfono <span class="campo-opcional">(opcional)</span>
        </label>
        <input type="text" name="telefono" class="form-control"
               placeholder="Ej: 341 234 5678"
               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña <span class="campo-opcional">(máx. 8 caracteres)</span></label>
        <input type="password" name="claveReg" class="form-control"
               placeholder="Elegí una contraseña" maxlength="8" required>
      </div>

      <div class="mb-4">
        <label class="form-label">Confirmar contraseña</label>
        <input type="password" name="claveConf" class="form-control"
               placeholder="Repetí tu contraseña" maxlength="8" required>
      </div>

      <button type="submit" class="btn-auth">
        <i class="bi bi-person-check me-2"></i>Crear cuenta
      </button>
    </form>

    <p style="text-align:center; margin-top:20px; font-size:0.88rem; color:var(--gris);">
      ¿Ya tenés cuenta?
      <a href="?tab=login" style="color:var(--azul); font-weight:600;">Iniciá sesión</a>
    </p>

  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Si la URL tiene ?tab=registro, mostrar pestaña registro sin recargar
  const params = new URLSearchParams(window.location.search);
  if (params.get('tab') === 'registro') {
    // Ya lo maneja PHP, pero prevenimos el scroll al top
    document.querySelector('.auth-card')?.scrollIntoView({ behavior: 'smooth' });
  }
</script>

</body>
</html>