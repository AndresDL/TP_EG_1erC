<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/']);
    session_start();
}

include_once('../mailer.inc');


$mensaje      = "";
$tipo_mensaje = "";
$enviado      = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre          = trim($_POST['nombre']  ?? '');
    $email           = trim($_POST['email']   ?? '');
    $mensaje_usuario = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $email === '' || $mensaje_usuario === '') {
        $mensaje      = "Por favor completá todos los campos.";
        $tipo_mensaje = "warning";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje      = "El email ingresado no es válido.";
        $tipo_mensaje = "warning";
    } else {
        $html = plantillaMail(
            'Nueva consulta de contacto',
            '<strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '<br>' .
            '<strong>Email:</strong> ' . htmlspecialchars($email) . '<br><br>' .
            '<strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($mensaje_usuario)),
            'Responder a ' . htmlspecialchars($nombre),
            'mailto:' . rawurlencode($email)
        );

        if (enviarMail(MAIL_REMITENTE_EMAIL, 'Nueva consulta de contacto – VuelaSeguro', $html)) {
            $enviado      = true;
            $tipo_mensaje = "success";
        } else {
            $mensaje      = "Hubo un error al enviar el mensaje. Por favor intentá de nuevo más tarde.";
            $tipo_mensaje = "danger";
        }
    }
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
    <link rel="stylesheet" href="contacto.css">
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
          <span class="text-white me-2"><a href="../USUARIO/usuario.php" style="text-decoration: none; color: white">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?><a></strong></span>
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
          <li class="breadcrumb-item active" aria-current="page">Formulario de contacto</li>
        </ol>
    </nav>
  </div>

  <div class="contacto-wrapper">
    <div class="contacto-form-card">

      <?php if ($enviado): ?>

        <!-- ── Pantalla de éxito ── -->
        <div class="text-center py-3">
          <div style="width:70px;height:70px;background:#e8f8ee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="bi bi-check-lg" style="font-size:2rem;color:var(--verde);"></i>
          </div>
          <h2 style="color:var(--verde);">¡Mensaje enviado!</h2>
          <p style="color:var(--gris);margin-top:10px;">
            Recibimos tu consulta y nos pondremos en contacto a la brevedad en <strong><?= htmlspecialchars($email) ?></strong>.
          </p>
          <a href="../INDEX/index.php" class="btn-enviar d-inline-block mt-4" style="text-decoration:none;">
            <i class="bi bi-house me-1"></i> Volver al inicio
          </a>
        </div>

      <?php else: ?>

        <h2>Contacto</h2>
        <h4>Completá el formulario y nos ponemos en contacto a la brevedad.</h4>

        <?php if ($mensaje !== ''): ?>
          <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" action="contacto.php">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   placeholder="Tu nombre"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                   required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="Tu email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
          </div>
          <div class="mb-4">
            <label class="form-label">Mensaje</label>
            <textarea name="mensaje" class="form-control textarea-contacto" rows="6"
                      placeholder="Escribí tu mensaje acá..." required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-enviar">
              <i class="bi bi-send me-1"></i> Enviar
            </button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>