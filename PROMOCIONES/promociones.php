<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/']);
    session_start();
}

// Conexión directa (igual que el test que funcionó)
$link = mysqli_connect('localhost', 'root', '', 'vuelaseguro');
if (!$link) die("Error de conexión: " . mysqli_connect_error());
mysqli_set_charset($link, 'utf8');

// ── ROLES ──────────────────────────────────────────────────────────────────
// Leemos sesión; si no hay sesión activa, usamos modo prueba
$usuario     = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
$tipoUsuario = $usuario ? $usuario['tipoUsuario'] : 'no_registrado';
$codUsuario  = $usuario ? (int)$usuario['codUsuario'] : 0;

// ═══════════════════════════════════════════════════════════════
// MODO PRUEBA — descomentá la línea del rol que querés testear
// Comentalas todas cuando el login esté listo
// ═══════════════════════════════════════════════════════════════
// $tipoUsuario = 'no_registrado'; $codUsuario = 0;
//  $tipoUsuario = 'CEO';           $codUsuario = 2;
// $tipoUsuario = 'usuario';       $codUsuario = 1;
// $tipoUsuario = 'admin';         $codUsuario = 3;
// ═══════════════════════════════════════════════════════════════

$esCEO   = ($tipoUsuario === 'CEO');
$esAdmin = ($tipoUsuario === 'admin');
$esUsuario = ($tipoUsuario === 'usuario');

$mensaje      = "";
$tipo_mensaje = "";

// ── SUBIR IMAGEN ──────────────────────────────────────────────────────────
function guardarImagen($campo) {
    if (empty($_FILES[$campo]['name'])) return '';
    $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return '';
    if (!is_dir(__DIR__ . '/img/')) mkdir(__DIR__ . '/img/', 0755, true);
    $nombre  = uniqid('promo_') . '.' . $ext;
    $destino = __DIR__ . '/img/' . $nombre;
    return move_uploaded_file($_FILES[$campo]['tmp_name'], $destino) ? 'img/' . $nombre : '';
}

// ── POST: CREAR PROMOCIÓN (CEO) ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_promo']) && $esCEO) {
    $descripcion  = mysqli_real_escape_string($link, trim($_POST['descripcionPromocion'] ?? ''));
    $descuento    = (float)($_POST['descuentoPromocion'] ?? 0);
    $vigencia     = mysqli_real_escape_string($link, trim($_POST['vigenciaPromocion'] ?? ''));
    $codAerolinea = (int)($_POST['codAerolinea'] ?? 0);

    $imagen = '';
    if (!empty($_FILES['imagenFile']['name'])) {
        $imagen = guardarImagen('imagenFile');
    } elseif (!empty($_POST['imagenUrl'])) {
        $imagen = mysqli_real_escape_string($link, trim($_POST['imagenUrl']));
    }

    if ($descripcion !== '' && $descuento > 0 && $vigencia !== '') {
        $valAero = $codAerolinea > 0 ? $codAerolinea : 'NULL';
        $sql = "INSERT INTO promociones
                    (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion, imagenPromocion, vigenciaPromocion, codCEO)
                VALUES
                    ('$descripcion', $descuento, $valAero, 'pendiente', '$imagen', '$vigencia', $codUsuario)";
        if (mysqli_query($link, $sql)) {
            $mensaje      = "Promoción enviada correctamente. Quedará pendiente hasta que el administrador la apruebe.";
            $tipo_mensaje = "success";
        } else {
            $mensaje      = "Error SQL: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    } else {
        $errores = [];
        if ($descripcion === '') $errores[] = "descripción";
        if ($descuento <= 0)     $errores[] = "descuento mayor a 0";
        if ($vigencia === '')    $errores[] = "fecha de vigencia";
        $mensaje      = "Faltan campos: " . implode(', ', $errores);
        $tipo_mensaje = "warning";
    }
}

// ── POST: EDITAR PROMOCIÓN (CEO) ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_promo']) && $esCEO) {
    $id          = (int)$_POST['id'];
    $descripcion = mysqli_real_escape_string($link, trim($_POST['descripcionPromocion'] ?? ''));
    $descuento   = (float)($_POST['descuentoPromocion'] ?? 0);
    $vigencia    = mysqli_real_escape_string($link, trim($_POST['vigenciaPromocion'] ?? ''));

    $check = mysqli_query($link, "SELECT * FROM promociones WHERE codPromocion=$id AND codCEO=$codUsuario AND estadoPromocion='aprobada'");
    if ($check && mysqli_num_rows($check) > 0) {
        $promo  = mysqli_fetch_assoc($check);
        $imagen = $promo['imagenPromocion'];
        if (!empty($_FILES['imagenFile']['name'])) {
            $nueva = guardarImagen('imagenFile');
            if ($nueva) $imagen = $nueva;
        } elseif (!empty($_POST['imagenUrl'])) {
            $imagen = mysqli_real_escape_string($link, trim($_POST['imagenUrl']));
        }
        mysqli_query($link, "UPDATE promociones SET descripcionPromocion='$descripcion', descuentoPromocion=$descuento,
            vigenciaPromocion='$vigencia', imagenPromocion='$imagen' WHERE codPromocion=$id AND codCEO=$codUsuario");
        $mensaje = "Promoción actualizada."; $tipo_mensaje = "success";
    } else {
        $mensaje = "No tenés permiso para editar esta promoción."; $tipo_mensaje = "danger";
    }
}

// ── POST: DAR DE BAJA (CEO) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['baja_promo']) && $esCEO) {
    $id    = (int)$_POST['id'];
    $check = mysqli_query($link, "SELECT codPromocion FROM promociones WHERE codPromocion=$id AND codCEO=$codUsuario AND estadoPromocion='aprobada'");
    if ($check && mysqli_num_rows($check) > 0) {
        mysqli_query($link, "UPDATE promociones SET estadoPromocion='denegada' WHERE codPromocion=$id");
        $mensaje = "Promoción dada de baja."; $tipo_mensaje = "success";
    } else {
        $mensaje = "No podés dar de baja esta promoción."; $tipo_mensaje = "danger";
    }
}

// ── POST: APROBAR / RECHAZAR (ADMIN) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aprobar_promo']) && $esAdmin) {
    $id = (int)$_POST['id'];
    mysqli_query($link, "UPDATE promociones SET estadoPromocion='aprobada' WHERE codPromocion=$id");
    $mensaje = "Promoción aprobada."; $tipo_mensaje = "success";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechazar_promo']) && $esAdmin) {
    $id = (int)$_POST['id'];
    mysqli_query($link, "UPDATE promociones SET estadoPromocion='denegada' WHERE codPromocion=$id");
    $mensaje = "Promoción rechazada."; $tipo_mensaje = "danger";
}

// ── PAGINACIÓN Y FILTRO ────────────────────────────────────────────────────
$porPagina    = 4;
$paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
$busqueda     = mysqli_real_escape_string($link, trim($_GET['buscar'] ?? ''));

$whereBusq = $busqueda !== '' ? "AND a.nombreAerolinea LIKE '%$busqueda%'" : '';

$sqlTotal       = "SELECT COUNT(*) AS total FROM promociones p
                   LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
                   WHERE p.estadoPromocion = 'aprobada' $whereBusq";
$resTotal       = mysqli_query($link, $sqlTotal);
$totalRegistros = $resTotal ? (int)mysqli_fetch_assoc($resTotal)['total'] : 0;
$totalPaginas   = max(1, ceil($totalRegistros / $porPagina));
$paginaActual   = min($paginaActual, $totalPaginas);
$offset         = ($paginaActual - 1) * $porPagina;

$resultPromos = mysqli_query($link,
    "SELECT p.*, a.nombreAerolinea FROM promociones p
     LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
     WHERE p.estadoPromocion = 'aprobada' $whereBusq
     ORDER BY p.codPromocion DESC
     LIMIT $porPagina OFFSET $offset");

// Pendientes para Admin
$pendientesRes  = null;
$cantPendientes = 0;
if ($esAdmin) {
    $pendientesRes  = mysqli_query($link,
        "SELECT p.*, a.nombreAerolinea, u.nombreUsuario AS nombreCEO
         FROM promociones p
         LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
         LEFT JOIN usuarios u ON p.codCEO = u.codUsuario
         WHERE p.estadoPromocion = 'pendiente' ORDER BY p.codPromocion DESC");
    $cantPendientes = $pendientesRes ? mysqli_num_rows($pendientesRes) : 0;
}

// Mis promos para CEO
$misPromosRes = null;
if ($esCEO) {
    $misPromosRes = mysqli_query($link,
        "SELECT p.*, a.nombreAerolinea FROM promociones p
         LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
         WHERE p.codCEO = $codUsuario ORDER BY p.codPromocion DESC");
}

// Aerolíneas
$aerolineasRes = mysqli_query($link, "SELECT codAerolinea, nombreAerolinea FROM aerolineas ORDER BY nombreAerolinea");

function urlPromos($pagina, $busqueda = '') {
    $p = ['pagina' => $pagina];
    if ($busqueda !== '') $p['buscar'] = $busqueda;
    return 'promociones.php?' . http_build_query($p);
}

function estadoBadge($estado) {
    return match($estado) {
        'aprobada'  => '<span class="badge bg-success">Aprobada</span>',
        'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
        'denegada'  => '<span class="badge bg-danger">Denegada</span>',
        default     => '<span class="badge bg-secondary">' . htmlspecialchars($estado) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Promociones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="../INDEX/estilos-globales.css" rel="stylesheet">
    <link rel="stylesheet" href="promociones.css">
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
      <a href="promociones.php" class="active">Promociones</a>
    </div>
    <div class="nav-right">
      <?php if ($usuario): ?>
        <span style="color:#fff;font-size:.9rem;font-weight:600;margin-right:12px;">
          <?= htmlspecialchars($usuario['nombreUsuario']) ?>
          <span style="font-size:.75rem;opacity:.75;">(<?= htmlspecialchars($tipoUsuario) ?>)</span>
        </span>
        <a href="../logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Salir</a>
      <?php else: ?>
        <div class="foto-perfil">
          <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
            <circle cx="21" cy="10" r="9" fill="#ffffff"/>
            <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
          </svg>
        </div>
        <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </nav>
  <nav aria-label="breadcrumb" style="padding:.5rem 2rem;">
    <ol class="breadcrumb mb-0" style="background:transparent;">
      <li class="breadcrumb-item"><a href="../INDEX/index.php" style="color:#cbd5e0;">Inicio</a></li>
      <li class="breadcrumb-item active" style="color:#90aecb;">Promociones</li>
    </ol>
  </nav>
</div>

<!-- MAIN -->
<main class="promociones-wrapper container mt-4 mb-5">

  <?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($mensaje) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Encabezado -->
  <div class="promociones-header flex-wrap gap-2">
    <div>
      <h2>Promociones</h2>
      <p class="mb-0" style="color:var(--gris);font-size:.9rem;">
        <?= $totalRegistros ?> promoción<?= $totalRegistros !== 1 ? 'es' : '' ?> disponible<?= $totalRegistros !== 1 ? 's' : '' ?>
      </p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <?php if ($esCEO): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearPromo">
          <i class="bi bi-plus-circle me-1"></i> Nueva Promoción
        </button>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMisPromos">
          <i class="bi bi-list-ul me-1"></i> Mis Promociones
        </button>
      <?php endif; ?>
      <?php if ($esAdmin): ?>
        <button class="btn btn-warning btn-sm position-relative" data-bs-toggle="modal" data-bs-target="#modalPendientes">
          <i class="bi bi-hourglass-split me-1"></i> Pendientes
          <?php if ($cantPendientes > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= $cantPendientes ?>
            </span>
          <?php endif; ?>
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filtro -->
  <div class="promociones-filtro">
    <form method="GET" action="promociones.php" class="d-flex gap-3 align-items-end flex-wrap">
      <div style="flex:1;min-width:200px;">
        <label class="form-label mb-1">Filtrar por aerolínea</label>
        <input type="text" name="buscar" class="filtro-input-promo"
               placeholder="Ej: Aerolíneas Argentinas"
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-search me-1"></i> Buscar
        </button>
        <?php if ($busqueda !== ''): ?>
          <a href="promociones.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Limpiar
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Grid -->
  <?php if (!$resultPromos || mysqli_num_rows($resultPromos) === 0): ?>
    <div class="text-center py-5" style="color:var(--gris);">
      <i class="bi bi-tags fs-1 d-block mb-3"></i>
      <p class="fs-5">No hay promociones aprobadas<?= $busqueda ? ' para "' . htmlspecialchars($busqueda) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <div class="promociones-grid">
      <?php while ($promo = mysqli_fetch_assoc($resultPromos)):
        $imgSrc = htmlspecialchars($promo['imagenPromocion'] ?? '');
      ?>
        <div class="promo-card">
          <?php if ($imgSrc): ?>
            <img src="<?= $imgSrc ?>" alt="Imagen promoción" onerror="this.style.display='none'">
          <?php else: ?>
            <div class="promo-sin-imagen">
              <i class="bi bi-image fs-1"></i>
              <span style="font-size:.85rem;">Sin imagen</span>
            </div>
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($promo['nombreAerolinea'] ?? 'Sin aerolínea') ?></h5>
            <p class="card-text"><?= htmlspecialchars($promo['descripcionPromocion']) ?></p>
            <div class="promo-descuento">
              <i class="bi bi-percent me-1"></i>
              <?= number_format($promo['descuentoPromocion'], 0) ?>% de descuento
            </div>
            <?php if (!empty($promo['vigenciaPromocion'])): ?>
              <p class="promo-vigencia">
                <i class="bi bi-clock me-1"></i>Vigencia hasta: <?= date('d/m/Y', strtotime($promo['vigenciaPromocion'])) ?>
              </p>
            <?php endif; ?>

            <?php if ($tipoUsuario === 'no_registrado'): ?>
              <a href="../LOGIN/login.php" class="btn-solicitar">
                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión para solicitar
              </a>
            <?php elseif ($esUsuario): ?>
              <button class="btn-solicitar"
                      onclick="alert('Promoción solicitada. Un asesor se comunicará con usted.')">
                SOLICITAR
              </button>
            <?php elseif ($esCEO && $promo['codCEO'] == $codUsuario): ?>
              <div class="mt-2 d-flex gap-2 align-items-center">
                <?= estadoBadge($promo['estadoPromocion']) ?>
                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditar<?= $promo['codPromocion'] ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#modalBaja<?= $promo['codPromocion'] ?>">
                  <i class="bi bi-arrow-down-circle"></i>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($esCEO && $promo['codCEO'] == $codUsuario): ?>
        <!-- Modal Editar -->
        <div class="modal fade" id="modalEditar<?= $promo['codPromocion'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form method="POST" action="promociones.php" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?= $promo['codPromocion'] ?>">
              <div class="modal-content">
                <div class="modal-header" style="background:var(--azul);color:#fff;">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Promoción</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                  <div class="col-md-8">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea class="form-control" name="descripcionPromocion" rows="3" required><?= htmlspecialchars($promo['descripcionPromocion']) ?></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Descuento (%)</label>
                    <input type="number" class="form-control" name="descuentoPromocion" min="1" max="100" step="0.01" value="<?= $promo['descuentoPromocion'] ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Vigencia hasta</label>
                    <input type="date" class="form-control" name="vigenciaPromocion" value="<?= $promo['vigenciaPromocion'] ?>" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-bold">Imagen (subir archivo)</label>
                    <input type="file" class="form-control" name="imagenFile" accept="image/*">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-bold">— o URL de imagen</label>
                    <input type="url" class="form-control" name="imagenUrl" placeholder="https://...">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" name="editar_promo" class="btn btn-primary">Guardar cambios</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- Modal Baja -->
        <div class="modal fade" id="modalBaja<?= $promo['codPromocion'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="promociones.php">
              <input type="hidden" name="id" value="<?= $promo['codPromocion'] ?>">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">Dar de baja</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p>¿Dar de baja <strong>"<?= htmlspecialchars($promo['descripcionPromocion']) ?>"</strong>?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" name="baja_promo" class="btn btn-danger">Sí, dar de baja</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

      <?php endwhile; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
      <nav class="mt-5">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= urlPromos($paginaActual - 1, $busqueda) ?>">Anterior</a>
          </li>
          <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
              <a class="page-link" href="<?= urlPromos($i, $busqueda) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= urlPromos($paginaActual + 1, $busqueda) ?>">Siguiente</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>

</main>

<!-- ═══ MODAL CREAR PROMO (CEO) ═══════════════════════════════════════════ -->
<?php if ($esCEO): ?>
<div class="modal fade" id="modalCrearPromo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="promociones.php" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header" style="background:var(--azul);color:#fff;">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Solicitar Nueva Promoción</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            La promoción quedará <strong>pendiente</strong> hasta que el administrador la apruebe.
          </p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Aerolínea</label>
              <?php if ($aerolineasRes && mysqli_num_rows($aerolineasRes) > 0): ?>
                <select class="form-select" name="codAerolinea">
                  <option value="0">Sin aerolínea específica</option>
                  <?php mysqli_data_seek($aerolineasRes, 0); while ($al = mysqli_fetch_assoc($aerolineasRes)): ?>
                    <option value="<?= $al['codAerolinea'] ?>"><?= htmlspecialchars($al['nombreAerolinea']) ?></option>
                  <?php endwhile; ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="codAerolinea" value="0">
                <input type="text" class="form-control" value="Sin aerolíneas en BD" disabled>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Descuento (%) <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" class="form-control" name="descuentoPromocion" min="1" max="100" step="0.01" placeholder="Ej: 30" required>
                <span class="input-group-text">%</span>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
              <textarea class="form-control" name="descripcionPromocion" rows="3" placeholder="Ej: 30% OFF en vuelos nacionales" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Vigencia hasta <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="vigenciaPromocion" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Imagen — subir archivo</label>
              <input type="file" class="form-control" name="imagenFile" accept="image/*"
                     onchange="previewImg(this,'prevCrear')">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">— o pegar URL</label>
              <input type="url" class="form-control" name="imagenUrl"
                     placeholder="https://ejemplo.com/imagen.jpg"
                     oninput="previewUrl(this.value,'prevCrear')">
              <div class="form-text">Si subís archivo, la URL se ignora.</div>
            </div>
            <div class="col-12">
              <div id="prevCrear" style="display:none;" class="promo-preview-imagen">
                <img src="" alt="Vista previa" style="max-height:180px;border-radius:10px;object-fit:cover;width:100%;">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="crear_promo" value="1" class="btn btn-primary">
            <i class="bi bi-send me-1"></i> Enviar solicitud
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Mis Promociones -->
<div class="modal fade" id="modalMisPromos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--azul-oscuro);color:#fff;">
        <h5 class="modal-title"><i class="bi bi-list-ul me-2"></i>Mis Promociones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr><th>#</th><th>Aerolínea</th><th>Descripción</th><th>Descuento</th><th>Vigencia</th><th>Estado</th></tr>
            </thead>
            <tbody>
              <?php if ($misPromosRes && mysqli_num_rows($misPromosRes) > 0):
                while ($mp = mysqli_fetch_assoc($misPromosRes)): ?>
                <tr>
                  <td><?= $mp['codPromocion'] ?></td>
                  <td><?= htmlspecialchars($mp['nombreAerolinea'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($mp['descripcionPromocion']) ?></td>
                  <td><?= number_format($mp['descuentoPromocion'], 0) ?>%</td>
                  <td><?= $mp['vigenciaPromocion'] ? date('d/m/Y', strtotime($mp['vigenciaPromocion'])) : '-' ?></td>
                  <td><?= estadoBadge($mp['estadoPromocion']) ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No tenés promociones registradas.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══ MODAL PENDIENTES (ADMIN) ══════════════════════════════════════════ -->
<?php if ($esAdmin): ?>
<div class="modal fade" id="modalPendientes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="bi bi-hourglass-split me-2"></i>Promociones Pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr><th>#</th><th>Aerolínea</th><th>CEO</th><th>Descripción</th><th>Descuento</th><th>Vigencia</th><th>Imagen</th><th>Acciones</th></tr>
            </thead>
            <tbody>
              <?php if ($pendientesRes && mysqli_num_rows($pendientesRes) > 0):
                while ($pend = mysqli_fetch_assoc($pendientesRes)):
                  $imgP = htmlspecialchars($pend['imagenPromocion'] ?? '');
              ?>
                <tr>
                  <td><?= $pend['codPromocion'] ?></td>
                  <td><?= htmlspecialchars($pend['nombreAerolinea'] ?? 'Sin aerolínea') ?></td>
                  <td><?= htmlspecialchars($pend['nombreCEO'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($pend['descripcionPromocion']) ?></td>
                  <td><?= number_format($pend['descuentoPromocion'], 0) ?>%</td>
                  <td><?= $pend['vigenciaPromocion'] ? date('d/m/Y', strtotime($pend['vigenciaPromocion'])) : '-' ?></td>
                  <td>
                    <?php if ($imgP): ?>
                      <img src="<?= $imgP ?>" style="height:48px;width:70px;object-fit:cover;border-radius:6px;" onerror="this.replaceWith('N/A')">
                    <?php else: ?><span class="text-muted small">Sin imagen</span><?php endif; ?>
                  </td>
                  <td>
                    <form method="POST" action="promociones.php" class="d-inline">
                      <input type="hidden" name="id" value="<?= $pend['codPromocion'] ?>">
                      <button type="submit" name="aprobar_promo" value="1" class="btn btn-sm btn-success"
                              onclick="return confirm('¿Aprobar?')">
                        <i class="bi bi-check-lg"></i> Aprobar
                      </button>
                    </form>
                    <form method="POST" action="promociones.php" class="d-inline ms-1">
                      <input type="hidden" name="id" value="<?= $pend['codPromocion'] ?>">
                      <button type="submit" name="rechazar_promo" value="1" class="btn btn-sm btn-danger"
                              onclick="return confirm('¿Rechazar?')">
                        <i class="bi bi-x-lg"></i> Rechazar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No hay promociones pendientes.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- FOOTER -->
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
          <li><a href="promociones.php">Promociones</a></li>
          <li><a href="../NOVEDADES/novedades.php">Novedades</a></li>
        </ul>
      </div>
      <div class="col">
        <h3><strong>Ubicación</strong><div class="subrayado"></div></h3>
        <ul>
          <li><a href="https://maps.app.goo.gl/UvsGpUXHgk9GkpYP9" target="_blank">Zeballos 1341</a></li>
          <li><a href="https://maps.app.goo.gl/87YMeSLAp74gH9mc7" target="_blank">Rosario, Santa Fe</a></li>
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
    <p class="copyright">&copy; 2026 VuelaSeguro. Todos los derechos reservados.</p>
  </footer>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImg(input, id) {
  const wrap = document.getElementById(id);
  const img  = wrap.querySelector('img');
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
    r.readAsDataURL(input.files[0]);
  }
}
function previewUrl(url, id) {
  const wrap = document.getElementById(id);
  const img  = wrap.querySelector('img');
  if (url.trim()) {
    img.src = url.trim();
    wrap.style.display = 'block';
    img.onerror = () => { wrap.style.display = 'none'; };
  } else {
    wrap.style.display = 'none';
  }
}
</script>

<?php mysqli_close($link); ?>
</body>
</html>