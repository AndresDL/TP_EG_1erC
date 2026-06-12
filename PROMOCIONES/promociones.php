<?php
session_start();
require_once "../conexion.inc";

// ─── CONFIGURACIÓN ────────────────────────────────────────────
define('IMG_DIR', __DIR__ . '/img/');   // carpeta donde se guardan las imágenes subidas
define('IMG_URL', 'img/');              // ruta relativa para mostrarlas en el HTML
define('POR_PAGINA', 4);               // promociones por página

// ─── TIPO DE USUARIO ──────────────────────────────────────────
$tipoUsuario  = $_SESSION['tipoUsuario']  ?? 'visitante';
$codUsuario   = $_SESSION['codUsuario']   ?? null;
$nombreUsuario = $_SESSION['nombreUsuario'] ?? '';

// ─── MENSAJES DE FEEDBACK ─────────────────────────────────────
$mensaje = "";
$msgTipo = "";  // "ok" o "error"

// ─── ACCIÓN: SOLICITAR PROMOCIÓN (usuario) ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'solicitar') {
    if (!$codUsuario) {
        $mensaje = "Debés iniciar sesión para solicitar una promoción.";
        $msgTipo = "error";
    } else {
        $codPromo = (int)$_POST['codPromocion'];
        // Verificar si ya la solicitó (usamos reservas con codVuelo=0 como placeholder de promo)
        $chk = $conn->prepare("SELECT codReserva FROM reservas WHERE codUsuario=? AND codVuelo=? AND estadoReserva='promocion'");
        $chk->bind_param("ii", $codUsuario, $codPromo);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $mensaje = "Ya solicitaste esta promoción.";
            $msgTipo = "error";
        } else {
            $ins = $conn->prepare("INSERT INTO reservas (codUsuario, codVuelo, fechaReserva, estadoReserva) VALUES (?, ?, CURDATE(), 'promocion')");
            $ins->bind_param("ii", $codUsuario, $codPromo);
            $ins->execute();
            $mensaje = "¡Promoción solicitada con éxito!";
            $msgTipo = "ok";
        }
    }
}

// ─── ACCIÓN: CREAR PROMOCIÓN (CEO) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_promo') {
    if ($tipoUsuario !== 'CEO') {
        $mensaje = "No tenés permisos para crear promociones.";
        $msgTipo = "error";
    } else {
        $codAerolinea  = (int)$_POST['codAerolinea'];
        $descripcion   = trim($_POST['descripcion']);
        $descuento     = (int)$_POST['descuento'];
        $vigencia      = trim($_POST['vigencia']);
        $estado        = 'pendiente';
        $imagenFinal   = '';

        // Opción A: imagen por URL
        if (!empty(trim($_POST['imagenUrl'] ?? ''))) {
            $imagenFinal = trim($_POST['imagenUrl']);

        // Opción B: imagen subida desde carpeta
        } elseif (isset($_FILES['imagenArchivo']) && $_FILES['imagenArchivo']['error'] === UPLOAD_ERR_OK) {
            $ext       = strtolower(pathinfo($_FILES['imagenArchivo']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidos)) {
                $mensaje = "Formato de imagen no permitido. Usá JPG, PNG o WEBP.";
                $msgTipo = "error";
            } else {
                if (!is_dir(IMG_DIR)) mkdir(IMG_DIR, 0755, true);
                $nombreArchivo = 'promo_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['imagenArchivo']['tmp_name'], IMG_DIR . $nombreArchivo);
                $imagenFinal = IMG_URL . $nombreArchivo;
            }
        }

        if ($msgTipo !== "error") {
            // La tabla promociones no tiene campo imagen ni vigencia en el SQL original,
            // los agregamos con ALTER si no existen (se ejecuta solo una vez sin error si ya existen)
            $conn->query("ALTER TABLE promociones ADD COLUMN IF NOT EXISTS imagenPromocion VARCHAR(500) DEFAULT ''");
            $conn->query("ALTER TABLE promociones ADD COLUMN IF NOT EXISTS vigenciaPromocion DATE DEFAULT NULL");
            $conn->query("ALTER TABLE promociones ADD COLUMN IF NOT EXISTS descripcionCorta VARCHAR(200) DEFAULT ''");

            $ins = $conn->prepare("INSERT INTO promociones (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion, imagenPromocion, vigenciaPromocion) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("siisss", $descripcion, $descuento, $codAerolinea, $estado, $imagenFinal, $vigencia);
            $ins->execute();
            $mensaje = "Promoción creada con éxito. Quedó en estado <strong>pendiente</strong> hasta que un administrador la apruebe.";
            $msgTipo = "ok";
        }
    }
}

// ─── FILTRO Y PAGINACIÓN ──────────────────────────────────────
$filtroAerolinea = trim($_GET['aerolinea'] ?? '');
$pagina          = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina       = POR_PAGINA; // bind_param necesita variables, no constantes
$offset          = ($pagina - 1) * $porPagina;

// Contar total para paginación
$sqlCount = "SELECT COUNT(*) as total
             FROM promociones p
             JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
             WHERE p.estadoPromocion = 'activa'";
$params      = [];
$tipos        = "";

if ($filtroAerolinea !== '') {
    $sqlCount .= " AND a.nombreAerolinea LIKE ?";
    $like      = "%$filtroAerolinea%";
    $params[]  = $like;
    $tipos    .= "s";
}

$stmtCount = $conn->prepare($sqlCount);
if ($tipos) $stmtCount->bind_param($tipos, ...$params);
$stmtCount->execute();
$totalPromos = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, ceil($totalPromos / POR_PAGINA));

// Traer promociones de la página actual
$sqlPromos = "SELECT p.*, a.nombreAerolinea
              FROM promociones p
              JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
              WHERE p.estadoPromocion = 'activa'";

if ($filtroAerolinea !== '') {
    $sqlPromos .= " AND a.nombreAerolinea LIKE ?";
}
$sqlPromos .= " LIMIT ? OFFSET ?";

$stmtPromos = $conn->prepare($sqlPromos);
if ($filtroAerolinea !== '') {
    $like = "%$filtroAerolinea%";
    $stmtPromos->bind_param("sii", $like, $porPagina, $offset);
} else {
    $stmtPromos->bind_param("ii", $porPagina, $offset);
}
$stmtPromos->execute();
$promociones = $stmtPromos->get_result();

// Aerolíneas para el select del CEO
$aerolineas = $conn->query("SELECT codAerolinea, nombreAerolinea FROM aerolineas ORDER BY nombreAerolinea");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Promociones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="../INDEX/estilos-globales.css" rel="stylesheet">
    <link rel="stylesheet" href="promociones.css">
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
        <a href="../PROMOCIONES/promociones.php" class="active">Promociones</a>
      </div>
      <div class="nav-right">
        <?php if ($codUsuario): ?>
          <span style="color:#fff; font-size:.85rem; font-weight:600;">
            <?= htmlspecialchars($nombreUsuario) ?>
            <?php if ($tipoUsuario === 'CEO'): ?>
              <span class="badge-ceo ms-1">CEO</span>
            <?php endif; ?>
          </span>
          <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;">Cerrar sesión</a>
        <?php else: ?>
          <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </nav>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Promociones</li>
      </ol>
    </nav>
  </div>
</section>

<!-- CONTENIDO -->
<div class="promociones-wrapper">

  <!-- Encabezado + botón CEO -->
  <div class="promociones-header">
    <h2>Promociones disponibles</h2>
    <div style="display:flex; align-items:center; gap:12px;">
      <span style="color:#666666;"><?= $totalPromos ?> resultado<?= $totalPromos !== 1 ? 's' : '' ?></span>
      <?php if ($tipoUsuario === 'CEO'): ?>
        <button class="btn-solicitar" style="width:auto; padding:10px 20px;" data-bs-toggle="modal" data-bs-target="#modalCrearPromo">
          <i class="bi bi-plus-circle me-1"></i> Nueva promoción
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mensaje de feedback -->
  <?php if ($mensaje): ?>
    <div style="background:<?= $msgTipo==='ok' ? '#e8f8ee' : '#fff5f5' ?>;
                border:1px solid <?= $msgTipo==='ok' ? '#9dd8b5' : 'var(--rojo)' ?>;
                color:<?= $msgTipo==='ok' ? '#198754' : 'var(--rojo)' ?>;
                border-radius:10px; padding:14px; margin-bottom:20px; font-weight:600;">
      <i class="bi bi-<?= $msgTipo==='ok' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
      <?= $mensaje ?>
    </div>
  <?php endif; ?>

  <!-- FILTRO -->
  <form method="GET" action="">
    <div class="promociones-filtro" style="display:flex; gap:12px; align-items:flex-end;">
      <div style="flex:1;">
        <label class="promociones-filtro label">Filtrar por aerolínea</label>
        <input type="text" name="aerolinea" class="filtro-input-promo"
               placeholder="Ej: Aerolíneas Argentinas"
               value="<?= htmlspecialchars($filtroAerolinea) ?>">
      </div>
      <button type="submit" class="btn-solicitar" style="width:auto; padding:12px 24px;">Buscar</button>
      <?php if ($filtroAerolinea): ?>
        <a href="promociones.php" class="btn-solicitar"
           style="width:auto; padding:12px 18px; background:var(--gris); text-decoration:none; text-align:center;">
          Limpiar
        </a>
      <?php endif; ?>
    </div>
  </form>

  <!-- GRID DE PROMOCIONES -->
  <?php if ($promociones->num_rows === 0): ?>
    <div style="text-align:center; padding:60px; color:var(--gris);">
      <i class="bi bi-search" style="font-size:2rem;"></i>
      <p style="margin-top:12px;">No se encontraron promociones<?= $filtroAerolinea ? " para \"$filtroAerolinea\"" : '' ?>.</p>
    </div>
  <?php else: ?>
    <div class="promociones-grid">
      <?php while ($promo = $promociones->fetch_assoc()): ?>
        <div class="promo-card">
          <img src="<?= htmlspecialchars($promo['imagenPromocion'] ?? 'img/default.jpg') ?>"
               alt="<?= htmlspecialchars($promo['nombreAerolinea']) ?>"
               onerror="this.src='https://placehold.co/600x220/d9dee7/666?text=Sin+imagen'">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($promo['nombreAerolinea']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($promo['descripcionPromocion']) ?></p>
            <p class="card-text" style="font-size:1.1rem; font-weight:700; color:var(--verde);">
              <?= $promo['descuentoPromocion'] ?>% OFF
            </p>
            <?php if (!empty($promo['vigenciaPromocion'])): ?>
              <p class="promo-vigencia">
                <i class="bi bi-clock me-1"></i>
                Vigencia hasta <?= date('d/m/Y', strtotime($promo['vigenciaPromocion'])) ?>
              </p>
            <?php endif; ?>

            <!-- Botón solicitar: solo para usuarios logueados -->
            <?php if ($tipoUsuario === 'usuario'): ?>
              <form method="POST">
                <input type="hidden" name="accion" value="solicitar">
                <input type="hidden" name="codPromocion" value="<?= $promo['codPromocion'] ?>">
                <button type="submit" class="btn-solicitar">SOLICITAR</button>
              </form>
            <?php elseif (!$codUsuario): ?>
              <a href="../LOGIN/login.php" class="btn-solicitar"
                 style="display:block; text-align:center; text-decoration:none;">
                Iniciá sesión para solicitar
              </a>
            <?php endif; ?>

          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- PAGINACIÓN -->
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?pagina=<?= $pagina-1 ?>&aerolinea=<?= urlencode($filtroAerolinea) ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?pagina=<?= $i ?>&aerolinea=<?= urlencode($filtroAerolinea) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
          <a class="page-link" href="?pagina=<?= $pagina+1 ?>&aerolinea=<?= urlencode($filtroAerolinea) ?>">Siguiente</a>
        </li>
      </ul>
    </nav>

  <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════
     MODAL CREAR PROMOCIÓN (solo CEO)
══════════════════════════════════════════ -->
<?php if ($tipoUsuario === 'CEO'): ?>
<div class="modal fade" id="modalCrearPromo" tabindex="-1" aria-labelledby="modalCrearPromoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius:16px; border:1px solid var(--borde);">

      <div class="modal-header" style="border-bottom:1px solid var(--borde);">
        <h5 class="modal-title" id="modalCrearPromoLabel" style="font-weight:700;">
          <i class="bi bi-plus-circle me-2" style="color:var(--azul);"></i>Nueva promoción
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="crear_promo">

        <div class="modal-body" style="display:flex; flex-direction:column; gap:16px; padding:24px;">

          <!-- Aerolínea -->
          <div>
            <label class="modal-label">Aerolínea</label>
            <select name="codAerolinea" class="modal-form-control" required>
              <option value="">Seleccioná una aerolínea</option>
              <?php
              $aerolineas->data_seek(0);
              while ($al = $aerolineas->fetch_assoc()):
              ?>
                <option value="<?= $al['codAerolinea'] ?>"><?= htmlspecialchars($al['nombreAerolinea']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Descripción -->
          <div>
            <label class="modal-label">Descripción de la promoción</label>
            <input type="text" name="descripcion" class="modal-form-control"
                   placeholder="Ej: 30% OFF en vuelos nacionales" required>
          </div>

          <!-- Descuento + Vigencia -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
              <label class="modal-label">Descuento (%)</label>
              <input type="number" name="descuento" class="modal-form-control"
                     min="1" max="100" placeholder="Ej: 30" required>
            </div>
            <div>
              <label class="modal-label">Vigencia hasta</label>
              <input type="date" name="vigencia" class="modal-form-control" required>
            </div>
          </div>

          <!-- Imagen por URL -->
          <div>
            <label class="modal-label">Imagen (URL de Google u otra web)</label>
            <input type="text" name="imagenUrl" id="inputUrl" class="modal-form-control"
                   placeholder="https://... (copiá la URL de la imagen)">
          </div>

          <div class="separador-o">O</div>

          <!-- Imagen subida desde carpeta -->
          <div>
            <label class="modal-label">Imagen (subir desde tu computadora)</label>
            <input type="file" name="imagenArchivo" id="inputArchivo"
                   class="modal-form-control" accept="image/*">
          </div>

          <!-- Preview -->
          <img id="previewImagen" src="" alt="Preview">

        </div>

        <div class="modal-footer" style="border-top:1px solid var(--borde);">
          <button type="button" class="btn-solicitar"
                  style="width:auto; padding:10px 20px; background:var(--gris);"
                  data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-solicitar" style="width:auto; padding:10px 24px;">
            Enviar para revisión
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  // Preview de imagen: URL
  document.getElementById('inputUrl').addEventListener('input', function() {
    const preview = document.getElementById('previewImagen');
    if (this.value.trim()) {
      preview.src = this.value.trim();
      preview.style.display = 'block';
      document.getElementById('inputArchivo').value = '';
    } else {
      preview.style.display = 'none';
    }
  });

  // Preview de imagen: archivo
  document.getElementById('inputArchivo').addEventListener('change', function() {
    const preview = document.getElementById('previewImagen');
    const file = this.files[0];
    if (file) {
      preview.src = URL.createObjectURL(file);
      preview.style.display = 'block';
      document.getElementById('inputUrl').value = '';
    } else {
      preview.style.display = 'none';
    }
  });
</script>
<?php endif; ?>

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