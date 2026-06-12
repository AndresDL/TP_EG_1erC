<?php
// session_start() ANTES del include, por si conexion.inc ya lo llama o no
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

// ─── ROLES ────────────────────────────────────────────────────────────────────
$usuario     = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
$tipoUsuario = $usuario ? $usuario['tipoUsuario'] : 'no_registrado';
$esCEO       = ($tipoUsuario === 'CEO');
$esAdmin     = ($tipoUsuario === 'admin');
$esUsuario   = ($tipoUsuario === 'usuario');
$codUsuario  = $usuario ? (int)$usuario['codUsuario'] : 0;

// Para pruebas rápidas, comentar/descomentar según rol a testear:
// $tipoUsuario = 'admin'; $esAdmin = true; $esCEO = false; $esUsuario = false; $codUsuario = 3;
 $tipoUsuario = 'CEO';   $esCEO   = true; $esAdmin = false; $esUsuario = false; $codUsuario = 2;
// $tipoUsuario = 'usuario'; $esUsuario = true; $esAdmin = false; $esCEO = false; $codUsuario = 1;

$mensaje      = "";
$tipo_mensaje = "";

// ─── MANEJO DE IMAGEN ─────────────────────────────────────────────────────────
function guardarImagen($campo): string {
    if (!empty($_FILES[$campo]['name'])) {
        $ext  = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $permitidos)) return '';
        $nombre = uniqid('promo_') . '.' . $ext;
        $destino = '../PROMOCIONES/img/' . $nombre;
        if (!is_dir('../PROMOCIONES/img/')) mkdir('../PROMOCIONES/img/', 0755, true);
        if (move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
            return 'img/' . $nombre;
        }
    }
    return '';
}

// ─── ACCIONES POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── DEBUG: descomentá estas líneas si el formulario no hace nada ──
    //echo '<pre style="background:#fff3cd;padding:15px;border:2px solid #f0ad4e;margin:10px;z-index:9999;position:relative;">';
    //echo "POST recibido:\n"; print_r($_POST);
    //echo "\nesCEO: " . ($esCEO ? 'SI' : 'NO');
    //echo "\ntipoUsuario: $tipoUsuario";
    //echo "\ncodUsuario: $codUsuario";
    //echo "\nSESSION: "; print_r($_SESSION);
    //echo '</pre>'; exit;

    // === CEO: Crear petición de promoción (queda en 'pendiente') ===
    if ($esCEO && !empty($_POST['crear_promo'])) {
        $codAerolinea = (int)($_POST['codAerolinea'] ?? 0);
        $descripcion  = mysqli_real_escape_string($link, trim($_POST['descripcionPromocion'] ?? ''));
        $descuento    = (float)($_POST['descuentoPromocion'] ?? 0);
        $vigencia     = mysqli_real_escape_string($link, trim($_POST['vigenciaPromocion'] ?? ''));

        $imagen = '';
        if (!empty($_FILES['imagenFile']['name'])) {
            $imagen = guardarImagen('imagenFile');
        } elseif (!empty($_POST['imagenUrl'])) {
            $imagen = mysqli_real_escape_string($link, trim($_POST['imagenUrl']));
        }

        if ($descripcion !== '' && $descuento > 0 && $vigencia !== '') {
            $valAero  = $codAerolinea > 0 ? $codAerolinea : 'NULL';
            $valCEO   = $codUsuario   > 0 ? $codUsuario   : 'NULL';
            $sql = "INSERT INTO promociones 
                        (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion, imagenPromocion, vigenciaPromocion, codCEO)
                    VALUES 
                        ('$descripcion', $descuento, $valAero, 'pendiente', '$imagen', '$vigencia', $valCEO)";
            if (mysqli_query($link, $sql)) {
                $mensaje = "Promoción enviada correctamente. Quedará pendiente hasta que el administrador la apruebe.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error SQL al crear: " . mysqli_error($link) . " | Query: " . $sql;
                $tipo_mensaje = "danger";
            }
        } else {
            $errores = [];
            if ($descripcion === '') $errores[] = "descripción";
            if ($descuento <= 0)     $errores[] = "descuento válido (mayor a 0)";
            if ($vigencia === '')    $errores[] = "fecha de vigencia";
            $mensaje = "Faltan campos obligatorios: " . implode(', ', $errores) . ".";
            $tipo_mensaje = "warning";
        }
    }

    // === CEO: Editar promoción propia (solo si fue aprobada) ===
    if ($esCEO && !empty($_POST['editar_promo'])) {
        $id          = (int)$_POST['id'];
        $descripcion = mysqli_real_escape_string($link, trim($_POST['descripcionPromocion']));
        $descuento   = (float)$_POST['descuentoPromocion'];
        $vigencia    = mysqli_real_escape_string($link, $_POST['vigenciaPromocion']);

        // Verificar que la promo pertenece a este CEO y está aprobada
        $check = mysqli_query($link, "SELECT * FROM promociones WHERE codPromocion=$id AND codCEO=$codUsuario AND estadoPromocion='aprobada'");
        if ($check && mysqli_num_rows($check) > 0) {
            $promo = mysqli_fetch_assoc($check);
            $imagen = $promo['imagenPromocion'];

            if (!empty($_FILES['imagenFile']['name'])) {
                $nueva = guardarImagen('imagenFile');
                if ($nueva) $imagen = $nueva;
            } elseif (!empty($_POST['imagenUrl'])) {
                $imagen = mysqli_real_escape_string($link, trim($_POST['imagenUrl']));
            }

            $sql = "UPDATE promociones SET descripcionPromocion='$descripcion', descuentoPromocion=$descuento,
                    vigenciaPromocion='$vigencia', imagenPromocion='$imagen'
                    WHERE codPromocion=$id AND codCEO=$codUsuario";
            if (mysqli_query($link, $sql)) {
                $mensaje = "Promoción actualizada correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar: " . mysqli_error($link);
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "No tenés permiso para editar esta promoción.";
            $tipo_mensaje = "danger";
        }
    }

    // === CEO: Dar de baja (cambiar estado a 'denegada') propia y aprobada ===
    if ($esCEO && !empty($_POST['baja_promo'])) {
        $id = (int)$_POST['id'];
        $check = mysqli_query($link, "SELECT codPromocion FROM promociones WHERE codPromocion=$id AND codCEO=$codUsuario AND estadoPromocion='aprobada'");
        if ($check && mysqli_num_rows($check) > 0) {
            mysqli_query($link, "UPDATE promociones SET estadoPromocion='denegada' WHERE codPromocion=$id");
            $mensaje = "Promoción dada de baja correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "No podés dar de baja esta promoción.";
            $tipo_mensaje = "danger";
        }
    }

    // === ADMIN: Aprobar promoción ===
    if ($esAdmin && !empty($_POST['aprobar_promo'])) {
        $id = (int)$_POST['id'];
        mysqli_query($link, "UPDATE promociones SET estadoPromocion='aprobada' WHERE codPromocion=$id");
        $mensaje = "Promoción aprobada.";
        $tipo_mensaje = "success";
    }

    // === ADMIN: Rechazar promoción ===
    if ($esAdmin && !empty($_POST['rechazar_promo'])) {
        $id = (int)$_POST['id'];
        mysqli_query($link, "UPDATE promociones SET estadoPromocion='denegada' WHERE codPromocion=$id");
        $mensaje = "Promoción rechazada.";
        $tipo_mensaje = "danger";
    }
}

// ─── FILTRO Y PAGINACIÓN ──────────────────────────────────────────────────────
$porPagina    = 4;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset       = ($paginaActual - 1) * $porPagina;
$busqueda     = isset($_GET['buscar']) ? mysqli_real_escape_string($link, trim($_GET['buscar'])) : '';

// Solo usuarios públicos/logueados ven las aprobadas; admin ve todas; CEO ve las suyas
if ($esAdmin) {
    $whereVista = "WHERE 1=1";
} elseif ($esCEO) {
    // En la vista pública, el CEO ve aprobadas; sus pendientes se muestran en sección aparte
    $whereVista = "WHERE p.estadoPromocion = 'aprobada'";
} else {
    $whereVista = "WHERE p.estadoPromocion = 'aprobada'";
}

$whereBusq = '';
if ($busqueda !== '') {
    $whereBusq = " AND a.nombreAerolinea LIKE '%$busqueda%'";
}

// Total
$sqlTotal = "SELECT COUNT(*) AS total FROM promociones p
             LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
             $whereVista $whereBusq";
$resTotal       = mysqli_query($link, $sqlTotal);
$totalRegistros = $resTotal ? mysqli_fetch_assoc($resTotal)['total'] : 0;
$totalPaginas   = max(1, ceil($totalRegistros / $porPagina));
if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;
$offset = ($paginaActual - 1) * $porPagina;

// Consulta principal
$sqlPromos = "SELECT p.*, a.nombreAerolinea FROM promociones p
              LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
              $whereVista $whereBusq
              ORDER BY p.codPromocion DESC
              LIMIT $porPagina OFFSET $offset";
$resultPromos = mysqli_query($link, $sqlPromos);

// ─── Pendientes para Admin ─────────────────────────────────────────────────────
$pendientesRes = null;
if ($esAdmin) {
    $pendientesRes = mysqli_query($link, "SELECT p.*, a.nombreAerolinea, u.nombreUsuario AS nombreCEO
        FROM promociones p
        LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
        LEFT JOIN usuarios u ON p.codCEO = u.codUsuario
        WHERE p.estadoPromocion = 'pendiente'
        ORDER BY p.codPromocion DESC");
}

// ─── Mis Promos para CEO ───────────────────────────────────────────────────────
$misPromosRes = null;
if ($esCEO) {
    $misPromosRes = mysqli_query($link, "SELECT p.*, a.nombreAerolinea FROM promociones p
        LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
        WHERE p.codCEO = $codUsuario
        ORDER BY p.codPromocion DESC");
}

// ─── Aerolíneas para el select del CEO ────────────────────────────────────────
$aerolineasRes = mysqli_query($link, "SELECT codAerolinea, nombreAerolinea FROM aerolineas ORDER BY nombreAerolinea");

// ─── Helper URL paginación ────────────────────────────────────────────────────
function urlPromos(int $pagina, string $busqueda = ''): string {
    $p = ['pagina' => $pagina];
    if ($busqueda !== '') $p['buscar'] = $busqueda;
    return 'promociones.php?' . http_build_query($p);
}

function estadoBadge(string $estado): string {
    return match($estado) {
        'aprobada'  => '<span class="badge bg-success">Aprobada</span>',
        'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
        'denegada'  => '<span class="badge bg-danger">Denegada / Baja</span>',
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@700&display=swap" rel="stylesheet"/>
    <link href="../INDEX/estilos-globales.css" rel="stylesheet">
    <link rel="stylesheet" href="promociones.css">
</head>
<body>

<!-- ═══════════════════════════════ NAVBAR ═══════════════════════════════════ -->
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
          <a href="../NOVEDADES/novedades.php">Novedades</a>
          <a href="promociones.php" class="active">Promociones</a>
        </div>
        <div class="nav-right">
          <?php if ($usuario): ?>
            <span style="color:#fff; font-size:.9rem; font-weight:600; margin-right:12px;">
              <?= htmlspecialchars($usuario['nombreUsuario']) ?>
              <span style="font-size:.75rem; opacity:.75;">(<?= htmlspecialchars($tipoUsuario) ?>)</span>
            </span>
            <a href="../logout.php" class="btn-registro" style="text-decoration:none; background:#dc3545;">Salir</a>
          <?php else: ?>
            <div class="foto-perfil" title="Foto de perfil">
              <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
                <circle cx="21" cy="10" r="9" fill="#ffffff"/>
                <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
              </svg>
            </div>
            <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none; border:1px solid #fff;">Iniciar sesión</a>
          <?php endif; ?>
        </div>
      </nav>

      <nav aria-label="breadcrumb" style="padding: 0.5rem 2rem;">
        <ol class="breadcrumb mb-0" style="background:transparent;">
          <li class="breadcrumb-item"><a href="../INDEX/index.php" style="color:#cbd5e0;">Inicio</a></li>
          <li class="breadcrumb-item active" style="color:#90aecb;" aria-current="page">Promociones</li>
        </ol>
      </nav>
    </div>
  </section>
</header>

<!-- ═══════════════════════════════ MAIN ════════════════════════════════════ -->
<main class="promociones-wrapper container mt-4 mb-5">

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($mensaje) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- ══ ENCABEZADO ══ -->
  <div class="promociones-header flex-wrap gap-2">
    <div>
      <h2 style="font-family:'Sora',sans-serif;">Promociones</h2>
      <p class="mb-0" style="color:var(--gris); font-size:.9rem;">
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
        <?php
        $cantPendientes = mysqli_num_rows($pendientesRes);
        mysqli_data_seek($pendientesRes, 0);
        ?>
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

  <!-- ══ FILTRO ══ -->
  <div class="promociones-filtro">
    <form method="GET" action="promociones.php" class="d-flex gap-3 align-items-end flex-wrap">
      <div style="flex:1; min-width:200px;">
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

  <!-- ══ GRID DE PROMOCIONES ══ -->
  <?php if (!$resultPromos || mysqli_num_rows($resultPromos) === 0): ?>
    <div class="text-center py-5" style="color:var(--gris);">
      <i class="bi bi-tags fs-1 d-block mb-3"></i>
      <p class="fs-5">No hay promociones disponibles<?= $busqueda ? ' para "' . htmlspecialchars($busqueda) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <div class="promociones-grid">
      <?php
      $modales_ceo_html = "";
      while ($promo = mysqli_fetch_assoc($resultPromos)):
        $imgSrc = '';
        if (!empty($promo['imagenPromocion'])) {
            if (str_starts_with($promo['imagenPromocion'], 'http') || str_starts_with($promo['imagenPromocion'], '//')) {
                $imgSrc = htmlspecialchars($promo['imagenPromocion']);
            } else {
                $imgSrc = htmlspecialchars($promo['imagenPromocion']);
            }
        }
      ?>
        <div class="promo-card">
          <?php if ($imgSrc): ?>
            <img src="<?= $imgSrc ?>" alt="Imagen promoción" onerror="this.style.display='none'">
          <?php else: ?>
            <div class="promo-sin-imagen">
              <i class="bi bi-image fs-1" style="opacity:.3;"></i>
              <span style="opacity:.4; font-size:.85rem;">Sin imagen</span>
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
                <i class="bi bi-clock me-1"></i>Vigencia hasta: <?= htmlspecialchars($promo['vigenciaPromocion']) ?>
              </p>
            <?php endif; ?>

            <?php if ($esAdmin): ?>
              <div class="mt-2"><?= estadoBadge($promo['estadoPromocion']) ?></div>
            <?php endif; ?>

            <?php if ($tipoUsuario === 'no_registrado'): ?>
              <a href="../LOGIN/login.php" class="btn-solicitar mt-auto">
                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión para solicitar
              </a>
            <?php elseif ($esUsuario): ?>
              <button class="btn-solicitar mt-auto"
                      onclick="alert('Promoción solicitada correctamente. Un asesor se comunicará con usted.')">
                SOLICITAR
              </button>
            <?php elseif ($esCEO): ?>
              <!-- El CEO puede ver pero no solicitar sus propias promos -->
              <div class="mt-2">
                <?php if ($promo['codCEO'] == $codUsuario): ?>
                  <?= estadoBadge($promo['estadoPromocion']) ?>
                  <?php if ($promo['estadoPromocion'] === 'aprobada'): ?>
                    <button class="btn btn-sm btn-outline-primary ms-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarPromo<?= $promo['codPromocion'] ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger ms-1"
                            data-bs-toggle="modal"
                            data-bs-target="#modalBajaPromo<?= $promo['codPromocion'] ?>">
                      <i class="bi bi-arrow-down-circle"></i>
                    </button>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php
        // Modales editar/baja para CEO (solo sus promos aprobadas)
        if ($esCEO && !empty($promo['codCEO']) && $promo['codCEO'] == $codUsuario && $promo['estadoPromocion'] === 'aprobada'):
            ob_start();
        ?>
        <!-- Modal Editar CEO -->
        <div class="modal fade" id="modalEditarPromo<?= $promo['codPromocion'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header" style="background:var(--azul); color:#fff;">
                  <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Promoción</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $promo['codPromocion'] ?>">
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="form-label fw-bold">Descripción</label>
                      <textarea class="form-control" name="descripcionPromocion" rows="3" required><?= htmlspecialchars($promo['descripcionPromocion']) ?></textarea>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Descuento (%)</label>
                      <input type="number" class="form-control" name="descuentoPromocion" min="1" max="100" step="0.01"
                             value="<?= $promo['descuentoPromocion'] ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Vigencia hasta</label>
                      <input type="date" class="form-control" name="vigenciaPromocion"
                             value="<?= $promo['vigenciaPromocion'] ?>" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-bold">Imagen — subir archivo</label>
                      <input type="file" class="form-control" name="imagenFile" accept="image/*">
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-bold">— o pegar URL de imagen</label>
                      <input type="url" class="form-control" name="imagenUrl"
                             placeholder="https://ejemplo.com/imagen.jpg"
                             value="<?= (str_starts_with($promo['imagenPromocion'] ?? '', 'http')) ? htmlspecialchars($promo['imagenPromocion']) : '' ?>">
                      <div class="form-text">Si subís un archivo, la URL será ignorada.</div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" name="editar_promo" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Guardar cambios
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Baja CEO -->
        <div class="modal fade" id="modalBajaPromo<?= $promo['codPromocion'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title"><i class="bi bi-arrow-down-circle me-2"></i>Dar de baja</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $promo['codPromocion'] ?>">
                  <p>¿Dar de baja la promoción <strong>"<?= htmlspecialchars($promo['descripcionPromocion']) ?>"</strong>?</p>
                  <p class="text-muted small">El estado pasará a <em>denegada</em> y ya no será visible para los usuarios.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" name="baja_promo" class="btn btn-danger">Sí, dar de baja</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php
            $modales_ceo_html .= ob_get_clean();
        endif;
        ?>

      <?php endwhile; ?>
    </div>

    <?= $modales_ceo_html ?>

    <!-- ══ PAGINACIÓN ══ -->
    <?php if ($totalPaginas > 1): ?>
      <nav class="mt-5" aria-label="Paginación promociones">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= urlPromos($paginaActual - 1, $busqueda) ?>">
              <i class="bi bi-chevron-left"></i> Anterior
            </a>
          </li>
          <?php
          $ini = max(1, $paginaActual - 2);
          $fin = min($totalPaginas, $paginaActual + 2);
          if ($ini > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= urlPromos(1, $busqueda) ?>">1</a></li>
            <?php if ($ini > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
          <?php endif; ?>
          <?php for ($i = $ini; $i <= $fin; $i++): ?>
            <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
              <a class="page-link" href="<?= urlPromos($i, $busqueda) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($fin < $totalPaginas): ?>
            <?php if ($fin < $totalPaginas - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= urlPromos($totalPaginas, $busqueda) ?>"><?= $totalPaginas ?></a></li>
          <?php endif; ?>
          <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= urlPromos($paginaActual + 1, $busqueda) ?>">
              Siguiente <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
        <p class="text-center mt-2" style="font-size:.85rem; color:var(--gris);">
          Página <?= $paginaActual ?> de <?= $totalPaginas ?>
        </p>
      </nav>
    <?php endif; ?>

  <?php endif; ?>

</main>

<!-- ═══════════════════════ MODAL: CEO – Crear Promoción ═════════════════════ -->
<?php if ($esCEO): ?>
<div class="modal fade" id="modalCrearPromo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="promociones.php" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header" style="background:var(--azul); color:#fff;">
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
              <?php
              $hayAerolineas = false;
              if ($aerolineasRes) {
                  mysqli_data_seek($aerolineasRes, 0);
                  $hayAerolineas = mysqli_num_rows($aerolineasRes) > 0;
              }
              ?>
              <?php if ($hayAerolineas): ?>
                <select class="form-select" name="codAerolinea">
                  <option value="0" selected>Sin aerolínea específica</option>
                  <?php mysqli_data_seek($aerolineasRes, 0); while ($al = mysqli_fetch_assoc($aerolineasRes)): ?>
                    <option value="<?= $al['codAerolinea'] ?>"><?= htmlspecialchars($al['nombreAerolinea']) ?></option>
                  <?php endwhile; ?>
                </select>
              <?php else: ?>
                <input type="text" class="form-control" placeholder="Ej: Aerolíneas Argentinas (tabla vacía)"
                       name="nombreAerolineaManual" style="color:var(--gris);" readonly>
                <input type="hidden" name="codAerolinea" value="0">
                <div class="form-text text-warning"><i class="bi bi-exclamation-triangle me-1"></i>No hay aerolíneas cargadas en la BD todavía.</div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Descuento (%) <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" class="form-control" name="descuentoPromocion"
                       min="1" max="100" step="0.01" placeholder="Ej: 30" required>
                <span class="input-group-text">%</span>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
              <textarea class="form-control" name="descripcionPromocion" rows="3"
                        placeholder="Ej: 30% OFF en vuelos nacionales" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Vigencia hasta <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="vigenciaPromocion"
                     min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-12">
              <hr class="my-1">
              <label class="form-label fw-bold mt-2">Imagen — subir archivo</label>
              <input type="file" class="form-control" name="imagenFile" accept="image/*"
                     onchange="previewImagen(this, 'previewCrear')">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">— o pegar URL de imagen</label>
              <input type="url" class="form-control" name="imagenUrl"
                     placeholder="https://ejemplo.com/imagen.jpg"
                     oninput="previewUrl(this.value, 'previewCrear')">
              <div class="form-text">Si subís un archivo, la URL será ignorada.</div>
            </div>
            <div class="col-12">
              <div id="previewCrear" class="promo-preview-imagen" style="display:none;">
                <img src="" alt="Vista previa" style="max-height:180px; border-radius:10px; object-fit:cover; width:100%;">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="crear_promo" class="btn btn-primary">
            <i class="bi bi-send me-1"></i> Enviar solicitud
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Mis Promociones (CEO) -->
<div class="modal fade" id="modalMisPromos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--azul-oscuro); color:#fff;">
        <h5 class="modal-title"><i class="bi bi-list-ul me-2"></i>Mis Promociones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Aerolínea</th>
                <th>Descripción</th>
                <th>Descuento</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($misPromosRes && mysqli_num_rows($misPromosRes) > 0):
                while ($mp = mysqli_fetch_assoc($misPromosRes)): ?>
                  <tr>
                    <td><?= $mp['codPromocion'] ?></td>
                    <td><?= htmlspecialchars($mp['nombreAerolinea'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($mp['descripcionPromocion']) ?></td>
                    <td><?= number_format($mp['descuentoPromocion'], 0) ?>%</td>
                    <td><?= htmlspecialchars($mp['vigenciaPromocion'] ?? '-') ?></td>
                    <td><?= estadoBadge($mp['estadoPromocion']) ?></td>
                    <td>
                      <?php if ($mp['estadoPromocion'] === 'aprobada'): ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-dismiss="modal"
                                onclick="setTimeout(()=>new bootstrap.Modal(document.getElementById('modalEditarPromo<?= $mp['codPromocion'] ?>')).show(), 300)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-dismiss="modal"
                                onclick="setTimeout(()=>new bootstrap.Modal(document.getElementById('modalBajaPromo<?= $mp['codPromocion'] ?>')).show(), 300)">
                          <i class="bi bi-arrow-down-circle"></i>
                        </button>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No tenés promociones registradas.</td></tr>
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

<!-- ═══════════════════════ MODAL: ADMIN – Pendientes ════════════════════════ -->
<?php if ($esAdmin): ?>
<div class="modal fade" id="modalPendientes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="bi bi-hourglass-split me-2"></i>Promociones Pendientes de Aprobación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Aerolínea</th>
                <th>CEO</th>
                <th>Descripción</th>
                <th>Descuento</th>
                <th>Vigencia</th>
                <th>Imagen</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              mysqli_data_seek($pendientesRes, 0);
              if (mysqli_num_rows($pendientesRes) > 0):
                while ($pend = mysqli_fetch_assoc($pendientesRes)):
                  $imgP = htmlspecialchars($pend['imagenPromocion'] ?? '');
              ?>
                <tr>
                  <td><?= $pend['codPromocion'] ?></td>
                  <td><?= htmlspecialchars($pend['nombreAerolinea'] ?? 'Sin aerolínea') ?></td>
                  <td><?= htmlspecialchars($pend['nombreCEO'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($pend['descripcionPromocion']) ?></td>
                  <td><?= number_format($pend['descuentoPromocion'], 0) ?>%</td>
                  <td><?= htmlspecialchars($pend['vigenciaPromocion'] ?? '-') ?></td>
                  <td>
                    <?php if ($imgP): ?>
                      <a href="<?= $imgP ?>" target="_blank">
                        <img src="<?= $imgP ?>" alt="img"
                             style="height:48px; width:70px; object-fit:cover; border-radius:6px;"
                             onerror="this.replaceWith('(error)')">
                      </a>
                    <?php else: ?>
                      <span class="text-muted small">Sin imagen</span>
                    <?php endif; ?>
                  </td>
                  <td class="d-flex gap-1 flex-wrap">
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="id" value="<?= $pend['codPromocion'] ?>">
                      <button type="submit" name="aprobar_promo" class="btn btn-sm btn-success"
                              onclick="return confirm('¿Aprobar esta promoción?')">
                        <i class="bi bi-check-lg me-1"></i>Aprobar
                      </button>
                    </form>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="id" value="<?= $pend['codPromocion'] ?>">
                      <button type="submit" name="rechazar_promo" class="btn btn-sm btn-danger"
                              onclick="return confirm('¿Rechazar esta promoción?')">
                        <i class="bi bi-x-lg me-1"></i>Rechazar
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

<!-- ═══════════════════════════════ FOOTER ═══════════════════════════════════ -->
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
<script>
// Scroll automático al mensaje de alerta si existe
document.addEventListener('DOMContentLoaded', function() {
  const alerta = document.querySelector('.alert');
  if (alerta) {
    alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

// Preview de imagen en modal crear
function previewImagen(input, containerId) {
  const container = document.getElementById(containerId);
  const img = container.querySelector('img');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      container.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewUrl(url, containerId) {
  const container = document.getElementById(containerId);
  const img = container.querySelector('img');
  if (url.trim() !== '') {
    img.src = url.trim();
    container.style.display = 'block';
    img.onerror = () => { container.style.display = 'none'; };
  } else {
    container.style.display = 'none';
  }
}
</script>

<?php if($link) mysqli_close($link); ?>
</body>
</html>