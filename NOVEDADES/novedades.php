<?php
// novedades.php — Vista pública de novedades (todos los usuarios)
// Muestra las novedades activas cargadas por el administrador, con paginación y filtro por tipo.

$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}
  // Ajustar la ruta según la estructura de carpetas

// ─── PAGINACIÓN ────────────────────────────────────────────────────────────────
$porPagina   = 6;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) $paginaActual = 1;
$offset = ($paginaActual - 1) * $porPagina;

// ─── FILTRO POR TIPO ───────────────────────────────────────────────────────────
$tipoFiltro = '';
$params     = [];
$types      = '';
$whereClause = "WHERE fechaExpiracionNovedad >= CURDATE()"; 

if (!empty($_GET['tipo']) && in_array($_GET['tipo'], ['importante', 'alerta', 'informativa'])) {
    $tipoFiltro   = $_GET['tipo'];
    $whereClause .= " AND tipoNovedad = ?";
    $params[]     = $tipoFiltro;
    $types       .= 's';
}

// ─── TOTAL DE REGISTROS (para paginación) ──────────────────────────────────────
$sqlTotal = "SELECT COUNT(*) AS total FROM novedades $whereClause";
$stmtTotal = $link->prepare($sqlTotal);
if (!empty($params)) {
    $stmtTotal->bind_param($types, ...$params);
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->get_result()->fetch_assoc()['total'];
$totalPaginas   = max(1, ceil($totalRegistros / $porPagina));
if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;

// ─── CONSULTA PRINCIPAL ────────────────────────────────────────────────────────
$sql = "SELECT * FROM novedades $whereClause ORDER BY fechaPublicacionNovedad DESC LIMIT ? OFFSET ?";
$paramsQuery   = array_merge($params, [$porPagina, $offset]);
$typesQuery    = $types . 'ii';
$stmt = $link->prepare($sql);
$stmt->bind_param($typesQuery, ...$paramsQuery);
$stmt->execute();
$novedades = $stmt->get_result();

// ─── HELPERS ───────────────────────────────────────────────────────────────────
function badgeNovedad(string $tipo): string {
    return match($tipo) {
        'importante'  => '<span class="badge-nov badge-imp"><i class="bi bi-star-fill me-1"></i>Importante</span>',
        'alerta'      => '<span class="badge-nov badge-alt"><i class="bi bi-cloud-lightning me-1"></i>Alerta</span>',
        default       => '<span class="badge-nov badge-info"><i class="bi bi-info-circle me-1"></i>Informativa</span>',
    };
}

function claseCard(string $tipo): string {
    return match($tipo) {
        'importante' => 'novedad-card destacada',
        'alerta'     => 'novedad-card alerta',
        default      => 'novedad-card',
    };
}

function urlFiltro(string $tipo, int $pagina = 1): string {
    $params = ['pagina' => $pagina];
    if ($tipo !== '') $params['tipo'] = $tipo;
    return 'novedades.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novedades – VuelaSeguro</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@700&display=swap" rel="stylesheet">

  <!-- Estilos del proyecto -->
  <link href="../INDEX/estilos-globales.css" rel="stylesheet">
  <link href="novedades.css" rel="stylesheet">
</head>

<body>

<!-- ═══════════════════════════════════════════════════════════ HEADER / NAV -->
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
          <a href="novedades.php" class="active">Novedades</a>
          <a href="../PROMOCIONES/promociones.php">Promociones</a>
        </div>
        <div class="nav-right">
          <?php if (isset($_SESSION['usuario'])): ?>
            <span style="color:#fff; font-size:.9rem; font-weight:600;">
              <?= htmlspecialchars($_SESSION['usuario']['nombreUsuario']) ?>
            </span>
            <?php if ($_SESSION['usuario']['tipoUsuario'] === 'administrador'): ?>
              <a href="../ADMIN/admin_novedades.php" class="btn-registro" style="text-decoration:none;">
                <i class="bi bi-gear-fill me-1"></i>Gestionar
              </a>
            <?php endif; ?>
            <a href="../logout.php" class="btn-registro" style="text-decoration:none; background:#dc3545;">
              Salir
            </a>
          <?php else: ?>
            <div class="foto-perfil" title="Foto de perfil">
              <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
                <circle cx="21" cy="10" r="9" fill="#ffffff"/>
                <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
              </svg>
            </div>
            <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
            <a href="../REGISTRO/registro.php" class="btn-registro" style="text-decoration:none; background:transparent; border:1px solid #fff;">Registrarse</a>
          <?php endif; ?>
        </div>
      </nav>

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" style="padding: 0.5rem 2rem;">
        <ol class="breadcrumb mb-0" style="background:transparent;">
          <li class="breadcrumb-item">
            <a href="../INDEX/index.php" style="color:#cbd5e0;">Inicio</a>
          </li>
          <li class="breadcrumb-item active" style="color:#90aecb;" aria-current="page">Novedades</li>
        </ol>
      </nav>
    </div>
  </section>
</header>

<!-- ═══════════════════════════════════════════════════════════ CONTENIDO -->
<main class="novedades-wrapper">

  <!-- Encabezado de sección -->
  <div class="novedades-header">
    <div>
      <h2 style="font-family:'Sora',sans-serif;">Novedades</h2>
      <p class="novedades-count mt-1">
        <?= $totalRegistros ?> novedad<?= $totalRegistros !== 1 ? 'es' : '' ?> vigente<?= $totalRegistros !== 1 ? 's' : '' ?>
      </p>
    </div>

    <!-- Filtro por tipo -->
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= urlFiltro('') ?>"
         class="btn btn-sm <?= $tipoFiltro === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Todas
      </a>
      <a href="<?= urlFiltro('importante') ?>"
         class="btn btn-sm <?= $tipoFiltro === 'importante' ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <i class="bi bi-star-fill me-1"></i>Importantes
      </a>
      <a href="<?= urlFiltro('alerta') ?>"
         class="btn btn-sm <?= $tipoFiltro === 'alerta' ? 'btn-warning' : 'btn-outline-warning' ?>">
        <i class="bi bi-cloud-lightning me-1"></i>Alertas
      </a>
      <a href="<?= urlFiltro('informativa') ?>"
         class="btn btn-sm <?= $tipoFiltro === 'informativa' ? 'btn-success' : 'btn-outline-success' ?>">
        <i class="bi bi-info-circle me-1"></i>Informativas
      </a>
    </div>
  </div>

  <!-- ─── GRILLA DE NOVEDADES ─────────────────────────────────────────────── -->
  <?php if ($novedades->num_rows === 0): ?>
    <div class="text-center py-5" style="color:var(--gris);">
      <i class="bi bi-inbox fs-1 d-block mb-3"></i>
      <p class="fs-5">No hay novedades vigentes en este momento.</p>
    </div>
  <?php else: ?>
    <div class="novedades-grid">
      <?php while ($nov = $novedades->fetch_assoc()): ?>
        <div class="<?= claseCard($nov['tipoNovedad']) ?>">
          <?= badgeNovedad($nov['tipoNovedad']) ?>
          <div class="nov-titulo-card"><?= htmlspecialchars($nov['TituloNovedad']) ?></div>
          <p class="nov-texto"><?= nl2br(htmlspecialchars($nov['textoNovedad'])) ?></p>
          <div class="nov-fecha">
            <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($nov['fechaPublicacionNovedad'])) ?></span>
            <span class="nov-vence"><i class="bi bi-clock"></i> Vence: <?= date('d/m/Y', strtotime($nov['fechaExpiracionNovedad'])) ?></span>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

  <!-- ─── PAGINACIÓN ──────────────────────────────────────────────────────── -->
  <?php if ($totalPaginas > 1): ?>
    <nav class="mt-4" aria-label="Paginación de novedades">
      <ul class="pagination justify-content-center">

        <!-- Anterior -->
        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlFiltro($tipoFiltro, $paginaActual - 1) ?>">
            <i class="bi bi-chevron-left"></i> Anterior
          </a>
        </li>

        <!-- Números de página -->
        <?php
        // Mostrar hasta 5 páginas centradas en la actual
        $inicio = max(1, $paginaActual - 2);
        $fin    = min($totalPaginas, $paginaActual + 2);
        if ($inicio > 1): ?>
          <li class="page-item">
            <a class="page-link" href="<?= urlFiltro($tipoFiltro, 1) ?>">1</a>
          </li>
          <?php if ($inicio > 2): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
          <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
            <a class="page-link" href="<?= urlFiltro($tipoFiltro, $i) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($fin < $totalPaginas): ?>
          <?php if ($fin < $totalPaginas - 1): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
          <?php endif; ?>
          <li class="page-item">
            <a class="page-link" href="<?= urlFiltro($tipoFiltro, $totalPaginas) ?>"><?= $totalPaginas ?></a>
          </li>
        <?php endif; ?>

        <!-- Siguiente -->
        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlFiltro($tipoFiltro, $paginaActual + 1) ?>">
            Siguiente <i class="bi bi-chevron-right"></i>
          </a>
        </li>

      </ul>
      <p class="text-center mt-1" style="font-size:.82rem; color:var(--gris);">
        Página <?= $paginaActual ?> de <?= $totalPaginas ?>
      </p>
    </nav>
  <?php endif; ?>

</main>

<!-- ═══════════════════════════════════════════════════════════ FOOTER -->
<section class="footer-section">
  <footer>
    <div class="row">
      <div class="col">
        <h3><strong>Contactanos</strong><div class="subrayado"></div></h3>
        <ul>
          <li><i class="bi bi-envelope-at"></i><a href="mailto:vuela@seguro.com.ar">vuela@seguro.com.ar</a></li>
          <li><i class="bi bi-whatsapp"></i><a href="#">+54 9 341 234 5678</a></li>
          <li><i class="bi bi-pen"></i><a href="../CONTACTO/contacto.html">Formulario de Contacto</a></li>
        </ul>
      </div>
      <div class="col">
        <h3><strong>Mapa de sitio</strong><div class="subrayado"></div></h3>
        <ul>
          <li><a href="../INDEX/index.php">Inicio</a></li>
          <li><a href="../VUELOS/vuelos.php">Vuelos</a></li>
          <li><a href="../PROMOCIONES/promociones.php">Promociones</a></li>
          <li><a href="novedades.php">Novedades</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $link->close(); 
session_write_close(); 
?>