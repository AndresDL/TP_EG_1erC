<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

$esAdmin = (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipoUsuario'] === 'admin');
// $esAdmin = true; // TEMPORAL PARA TESTEO
$mensaje = "";
$tipo_mensaje = "";

// ─── LÓGICA DE CRUD (SOLO PARA ADMIN) ──────────────────────────────────────────
if ($esAdmin && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['crear'])) {
        $titulo = mysqli_real_escape_string($link, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($link, $_POST['descripcion']);
        $fecha_publicacion = date('Y-m-d');
        $fecha_expiracion = $_POST['fecha_expiracion'];
        $tipo_novedad = $_POST['tipo_novedad'];

        $sql = "INSERT INTO novedades (TituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad, tipoNovedad) 
                VALUES ('$titulo', '$descripcion', '$fecha_publicacion', '$fecha_expiracion', '$tipo_novedad')";
        if(mysqli_query($link, $sql)){
            $mensaje = "Novedad creada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al crear: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    }

    if (!empty($_POST['editar'])) {
        $id = (int)$_POST['id'];
        $titulo = mysqli_real_escape_string($link, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($link, $_POST['descripcion']);
        $fecha_expiracion = $_POST['fecha_expiracion'];
        $tipo_novedad = $_POST['tipo_novedad'];

        $sql = "UPDATE novedades SET TituloNovedad='$titulo', textoNovedad='$descripcion', 
                fechaExpiracionNovedad='$fecha_expiracion', tipoNovedad='$tipo_novedad' 
                WHERE codNovedad=$id";
        if(mysqli_query($link, $sql)){
            $mensaje = "Novedad actualizada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    }

    if (!empty($_POST['eliminar'])) {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM novedades WHERE codNovedad=$id";
        if(mysqli_query($link, $sql)){
            $mensaje = "Novedad eliminada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    }
}

// ─── PAGINACIÓN ────────────────────────────────────────────────────────────────
$porPagina   = 6;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) $paginaActual = 1;
$offset = ($paginaActual - 1) * $porPagina;

// ─── FILTRO POR TIPO ───────────────────────────────────────────────────────────
$tipoFiltro = '';
$params     = [];
$types      = '';
$whereClause = $esAdmin ? "WHERE 1=1" : "WHERE fechaExpiracionNovedad >= CURDATE()"; 

if (!empty($_GET['tipo']) && in_array(ucfirst($_GET['tipo']), ['Importante', 'Alerta', 'Informativa'])) {
    $tipoFiltro   = strtolower($_GET['tipo']);
    $whereClause .= " AND tipoNovedad = ?";
    $params[]     = ucfirst($tipoFiltro);
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
        'Importante'  => '<span class="badge-nov badge-imp"><i class="bi bi-star-fill me-1"></i>Importante</span>',
        'Alerta'      => '<span class="badge-nov badge-alt"><i class="bi bi-cloud-lightning me-1"></i>Alerta</span>',
        default       => '<span class="badge-nov badge-info"><i class="bi bi-info-circle me-1"></i>Informativa</span>',
    };
}

function claseCard(string $tipo): string {
    return match($tipo) {
        'Importante' => 'novedad-card destacada',
        'Alerta'     => 'novedad-card alerta',
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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@700&display=swap" rel="stylesheet">

  <link href="../INDEX/estilos-globales.css" rel="stylesheet">
  <link href="novedades.css" rel="stylesheet">
</head>

<body>

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
            <span style="color:#fff; font-size:.9rem; font-weight:600; margin-right: 15px;">
              <?= htmlspecialchars($_SESSION['usuario']['nombreUsuario']) ?>
            </span>
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
            <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none; background:transparent; border:1px solid #fff;">Registrarse</a>
          <?php endif; ?>
        </div>
      </nav>
      
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

<main class="novedades-wrapper container mt-4 mb-5">

  <?php if (!empty($mensaje)): ?>
      <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show mt-3" role="alert">
          <?= htmlspecialchars($mensaje) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
  <?php endif; ?>

  <div class="novedades-header d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div>
      <h2 style="font-family:'Sora',sans-serif;">Novedades</h2>
      <p class="novedades-count mt-1 mb-0">
        <?= $totalRegistros ?> novedad<?= $totalRegistros !== 1 ? 'es' : '' ?> registradas
      </p>
    </div>

    <div class="d-flex gap-2 flex-wrap align-items-center">
      <a href="<?= urlFiltro('') ?>" class="btn btn-sm <?= $tipoFiltro === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todas</a>
      <a href="<?= urlFiltro('importante') ?>" class="btn btn-sm <?= $tipoFiltro === 'importante' ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="bi bi-star-fill me-1"></i>Importantes</a>
      <a href="<?= urlFiltro('alerta') ?>" class="btn btn-sm <?= $tipoFiltro === 'alerta' ? 'btn-warning' : 'btn-outline-warning' ?>"><i class="bi bi-cloud-lightning me-1"></i>Alertas</a>
      <a href="<?= urlFiltro('informativa') ?>" class="btn btn-sm <?= $tipoFiltro === 'informativa' ? 'btn-success' : 'btn-outline-success' ?>"><i class="bi bi-info-circle me-1"></i>Informativas</a>
      
      <?php if ($esAdmin): ?>
        <button class="btn btn-sm btn-success ms-md-3" data-bs-toggle="modal" data-bs-target="#modalCrear">
          <i class="bi bi-plus-circle me-1"></i>Crear Novedad
        </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($novedades->num_rows === 0): ?>
    <div class="text-center py-5" style="color:var(--gris);">
      <i class="bi bi-inbox fs-1 d-block mb-3"></i>
      <p class="fs-5">No hay novedades registradas.</p>
    </div>
  <?php else: ?>
    <div class="novedades-grid">
      <?php 
      $modales_admin_html = ""; 
      while ($nov = $novedades->fetch_assoc()): 
      ?>
        <div class="<?= claseCard($nov['tipoNovedad']) ?>">
          <div>
              <?= badgeNovedad($nov['tipoNovedad']) ?>
              <?php if ($esAdmin && $nov['fechaExpiracionNovedad'] < date('Y-m-d')): ?>
                  <span class="badge bg-secondary ms-1">Expirada</span>
              <?php endif; ?>
          </div>
          <div class="nov-titulo-card mt-2"><?= htmlspecialchars($nov['TituloNovedad']) ?></div>
          <p class="nov-texto"><?= nl2br(htmlspecialchars($nov['textoNovedad'])) ?></p>
          <div class="nov-fecha mt-3">
            <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($nov['fechaPublicacionNovedad'])) ?></span>
            <span class="nov-vence"><i class="bi bi-clock"></i> Vence: <?= date('d/m/Y', strtotime($nov['fechaExpiracionNovedad'])) ?></span>
          </div>
          
          <?php if ($esAdmin): ?>
          <div class="d-flex justify-content-between align-items-center mt-1 pt-3 border-top">
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $nov['codNovedad'] ?>">
                  <i class="bi bi-pencil"></i> Editar
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $nov['codNovedad'] ?>">
                  <i class="bi bi-trash"></i> Eliminar
              </button>
          </div>

          <?php 
          ob_start(); 
          ?>
          <div class="modal fade" id="modalEditar<?= $nov['codNovedad'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form method="POST" action="">
                <div class="modal-content text-start" style="font-weight: normal;">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Editar Novedad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $nov['codNovedad'] ?>">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Título (máx 50)</label>
                      <input type="text" class="form-control" name="titulo" value="<?= htmlspecialchars($nov['TituloNovedad']) ?>" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Tipo</label>
                      <select class="form-select" name="tipo_novedad" required>
                        <option value="Informativa" <?= $nov['tipoNovedad'] == 'Informativa' ? 'selected' : '' ?>>Informativa</option>
                        <option value="Importante" <?= $nov['tipoNovedad'] == 'Importante' ? 'selected' : '' ?>>Importante</option>
                        <option value="Alerta" <?= $nov['tipoNovedad'] == 'Alerta' ? 'selected' : '' ?>>Alerta</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Descripción</label>
                      <textarea class="form-control" name="descripcion" rows="4" required><?= htmlspecialchars($nov['textoNovedad']) ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Vencimiento</label>
                      <input type="date" class="form-control" name="fecha_expiracion" value="<?= $nov['fechaExpiracionNovedad'] ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <input type="submit" name="editar" class="btn btn-primary" value="Guardar Cambios">
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="modal fade" id="modalEliminar<?= $nov['codNovedad'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form method="POST" action="">
                <div class="modal-content text-start" style="font-weight: normal;">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Eliminar Novedad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $nov['codNovedad'] ?>">
                    <p>¿Estás seguro que deseas eliminar la novedad <strong>"<?= htmlspecialchars($nov['TituloNovedad']) ?>"</strong>?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <input type="submit" name="eliminar" class="btn btn-danger" value="Sí, Eliminar">
                  </div>
                </div>
              </form>
            </div>
          </div>
          <?php 
          $modales_admin_html .= ob_get_clean(); 
          endif; 
          ?>

        </div>
      <?php endwhile; ?>
    </div>
    
    <?= $modales_admin_html ?>
    
  <?php endif; ?>

  <?php if ($totalPaginas > 1): ?>
    <nav class="mt-5" aria-label="Paginación de novedades">
      <ul class="pagination justify-content-center">

        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlFiltro($tipoFiltro, $paginaActual - 1) ?>">
            <i class="bi bi-chevron-left"></i> Anterior
          </a>
        </li>

        <?php
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

        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlFiltro($tipoFiltro, $paginaActual + 1) ?>">
            Siguiente <i class="bi bi-chevron-right"></i>
          </a>
        </li>

      </ul>
      <p class="text-center mt-2" style="font-size:.85rem; color:var(--gris);">
        Página <?= $paginaActual ?> de <?= $totalPaginas ?>
      </p>
    </nav>
  <?php endif; ?>

</main>

<?php if ($esAdmin): ?>
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Crear Nueva Novedad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título (máx 50 carac.)</label>
                        <input type="text" class="form-control" name="titulo" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Novedad</label>
                        <select class="form-select" name="tipo_novedad" required>
                            <option value="" disabled selected>Seleccione un tipo...</option>
                            <option value="Informativa">Informativa</option>
                            <option value="Importante">Importante</option>
                            <option value="Alerta">Alerta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Expiración</label>
                        <input type="date" class="form-control" name="fecha_expiracion" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <input type="submit" name="crear" class="btn btn-success" value="Crear Novedad">
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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
    <p class="copyright">© 2026 VuelaSeguro. Todos los derechos reservados. Licenciado bajo
      <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons BY 4.0</a>.
    </p>
  </footer>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
if($link) {
    mysqli_close($link);
}
?>