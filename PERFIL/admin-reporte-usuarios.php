<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

// Solo admin puede acceder
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] != 'admin') {
    header("Location: ../INDEX/index.php");
    exit();
}

// ─── FILTROS ──────────────────────────────────────────────────────────────────
$filtroBuscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$filtroTipo   = isset($_GET['tipo'])   ? trim($_GET['tipo'])   : '';

// ─── PAGINACIÓN ───────────────────────────────────────────────────────────────
$porPagina    = 10;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;

// ─── WHERE DINÁMICO ───────────────────────────────────────────────────────────
$where      = "WHERE 1=1";
$tipos_bind = "";
$params     = [];

if ($filtroBuscar !== '') {
    $where       .= " AND (u.nombreUsuario LIKE ? OR u.emailUsuario LIKE ?)";
    $tipos_bind  .= "ss";
    $buscarLike   = "%$filtroBuscar%";
    $params[]     = &$buscarLike;
    $params[]     = &$buscarLike;
}
if ($filtroTipo !== '') {
    $where      .= " AND u.tipoUsuario = ?";
    $tipos_bind .= "s";
    $params[]    = &$filtroTipo;
}

// ─── CONTEO TOTAL (para paginación) ───────────────────────────────────────────
$sqlConteo = "SELECT COUNT(DISTINCT u.codUsuario) AS total FROM usuarios u $where";
$stmtConteo = mysqli_prepare($link, $sqlConteo);
if ($tipos_bind !== '') {
    mysqli_stmt_bind_param($stmtConteo, $tipos_bind, ...$params);
}
mysqli_stmt_execute($stmtConteo);
$totalUsuarios = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtConteo))['total'];
$totalPaginas  = max(1, (int)ceil($totalUsuarios / $porPagina));
if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;
$offset = ($paginaActual - 1) * $porPagina;

// ─── ESTADÍSTICAS FIJAS ──────────────────────
$conteosPorTipo = [];
$resTipos = mysqli_query($link, "SELECT tipoUsuario, COUNT(*) AS total FROM usuarios GROUP BY tipoUsuario");
while ($fila = mysqli_fetch_assoc($resTipos)) {
    $conteosPorTipo[$fila['tipoUsuario']] = (int)$fila['total'];
}
$totalGeneral = array_sum($conteosPorTipo);

// ─── LISTADO PAGINADO ──────────────────────────────────────────────────────────
$sqlLista = "SELECT u.codUsuario, u.nombreUsuario, u.emailUsuario, u.telefonoUsuario,
                    u.tipoUsuario,
                    COUNT(r.codReserva) AS totalReservas,
                    SUM(CASE WHEN r.estadoReserva = 'Confirmada' THEN 1 ELSE 0 END) AS comprasConfirmadas
             FROM usuarios u
             LEFT JOIN reservas r ON r.codUsuario = u.codUsuario
             $where
             GROUP BY u.codUsuario
             ORDER BY u.codUsuario ASC
             LIMIT ? OFFSET ?";

$tipos_bind_full = $tipos_bind . "ii";
$paramsLista     = $params;
$paramsLista[]   = &$porPagina;
$paramsLista[]   = &$offset;

$stmtLista = mysqli_prepare($link, $sqlLista);
mysqli_stmt_bind_param($stmtLista, $tipos_bind_full, ...$paramsLista);
mysqli_stmt_execute($stmtLista);
$resUsuarios = mysqli_stmt_get_result($stmtLista);

// Helper: preservar filtros en links de paginación
function buildQuery($extras = []) {
    $base = [];
    if (!empty($_GET['buscar'])) $base['buscar'] = $_GET['buscar'];
    if (!empty($_GET['tipo']))   $base['tipo']   = $_GET['tipo'];
    return '?' . http_build_query(array_merge($base, $extras));
}

$nombreAdmin = $_SESSION['usuario']['nombreUsuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VuelaSeguro – Reporte de usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../INDEX/estilos-globales.css">
    <link rel="stylesheet" href="../CONTACTO/contacto.css">
    <link rel="stylesheet" href="../PERFIL/perfiles.css">
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
          <a href="../NOVEDADES/novedades.php">Novedades</a>
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
              <span class="text-white me-2">
                <a href="../PERFIL/perfiles.php" style="text-decoration:none;color:white;">
                  Hola, <strong><?php echo htmlspecialchars($nombreAdmin); ?></strong>
                </a>
              </span>
              <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesión</a>
          <?php else: ?>
              <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration:none;color:white;">Iniciar sesión</a>
          <?php endif; ?>
        </div>
      </nav>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item"><a href="../PERFIL/perfiles.php">Panel Admin</a></li>
          <li class="breadcrumb-item active" aria-current="page">Reporte de usuarios</li>
        </ol>
      </nav>
    </div>
  </section>
</header>

<!-- ══ CONTENIDO ═══════════════════════════════════════════════════════════ -->
<div class="contacto-wrapper">

    <div class="contacto-header">
        <h2><i class="bi bi-people-fill"></i> Reporte de usuarios</h2>
    </div>

    <!-- ── Tarjetas de resumen ── -->
    <div class="row" style="margin: 0 1.5rem 1.5rem;">
        <div class="col-md-3" style="margin-bottom:1rem;">
            <div class="contacto-form-card" style="margin:0;padding:20px;">
                <h4 style="border:none;padding:0;margin-bottom:4px;">Total registrados</h4>
                <h2 style="color:var(--azul);margin:0;"><?php echo $totalGeneral; ?></h2>
            </div>
        </div>
        <div class="col-md-3" style="margin-bottom:1rem;">
            <div class="contacto-form-card" style="margin:0;padding:20px;">
                <h4 style="border:none;padding:0;margin-bottom:4px;">Clientes</h4>
                <h2 style="color:var(--verde);margin:0;"><?php echo $conteosPorTipo['usuario'] ?? 0; ?></h2>
            </div>
        </div>
        <div class="col-md-3" style="margin-bottom:1rem;">
            <div class="contacto-form-card" style="margin:0;padding:20px;">
                <h4 style="border:none;padding:0;margin-bottom:4px;">CEOs</h4>
                <h2 style="color:var(--azul);margin:0;"><?php echo $conteosPorTipo['CEO'] ?? 0; ?></h2>
            </div>
        </div>
        <div class="col-md-3" style="margin-bottom:1rem;">
            <div class="contacto-form-card" style="margin:0;padding:20px;">
                <h4 style="border:none;padding:0;margin-bottom:4px;">Admins</h4>
                <h2 style="color:var(--rojo);margin:0;"><?php echo $conteosPorTipo['admin'] ?? 0; ?></h2>
            </div>
        </div>
    </div>

    <!-- ── Filtros ── -->
    <form method="GET" class="filtro-bar">
        <span class="filtro-titulo"><i class="bi bi-funnel"></i> Filtrar usuarios</span>

        <input type="text"
               name="buscar"
               class="filtro-input"
               placeholder="Nombre o email..."
               value="<?php echo htmlspecialchars($filtroBuscar); ?>">

        <select name="tipo" class="filtro-input" style="cursor:pointer;">
            <option value="">Todos los tipos</option>
            <option value="usuario" <?php echo $filtroTipo === 'usuario' ? 'selected' : ''; ?>>Cliente</option>
            <option value="CEO"     <?php echo $filtroTipo === 'CEO'     ? 'selected' : ''; ?>>CEO</option>
            <option value="admin"   <?php echo $filtroTipo === 'admin'   ? 'selected' : ''; ?>>Admin</option>
        </select>

        <button type="submit" class="btn-buscar">
            <i class="bi bi-search"></i> Buscar
        </button>
        <a href="admin-reporte-usuarios.php" class="btn-buscar"
           style="background:var(--gris);text-decoration:none;display:flex;align-items:center;">
            <i class="bi bi-x-circle"></i>
        </a>
    </form>

    <!-- ── Tabla ── -->
    <div class="table-responsive" style="margin:0 1.5rem;">
        <table class="table table-hover" style="background:#ffffff;border-radius:12px;overflow:hidden;">
            <thead style="background:var(--azul-oscuro);color:#ffffff;">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Reservas</th>
                    <th class="text-center">Confirmadas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resUsuarios) === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding:28px;color:var(--gris);">
                            <i class="bi bi-search d-block fs-3 mb-2"></i>
                            No se encontraron usuarios con esos filtros.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($u = mysqli_fetch_assoc($resUsuarios)):
                        // Badge de tipo reutilizando colores de las variables globales
                        $badgeColor = match($u['tipoUsuario']) {
                            'admin'   => 'var(--rojo)',
                            'CEO'     => 'var(--azul)',
                            default   => 'var(--verde)',
                        };
                        $badgeLabel = match($u['tipoUsuario']) {
                            'admin'   => 'Admin',
                            'CEO'     => 'CEO',
                            default   => 'Cliente',
                        };
                    ?>
                    <tr>
                        <td style="color:var(--gris);"><?php echo $u['codUsuario']; ?></td>
                        <td><?php echo htmlspecialchars($u['nombreUsuario'] ?? '—'); ?></td>
                        <td style="color:var(--gris);"><?php echo htmlspecialchars($u['emailUsuario'] ?? '—'); ?></td>
                        <td style="color:var(--gris);"><?php echo htmlspecialchars($u['telefonoUsuario'] ?? '—'); ?></td>
                        <td class="text-center">
                            <span style="background:<?php echo $badgeColor; ?>;color:#fff;
                                         border-radius:6px;padding:2px 10px;
                                         font-size:.78rem;font-weight:600;">
                                <?php echo $badgeLabel; ?>
                            </span>
                        </td>
                        <td class="text-center" style="font-weight:600;">
                            <?php echo (int)$u['totalReservas']; ?>
                        </td>
                        <td class="text-center">
                            <?php if ((int)$u['comprasConfirmadas'] > 0): ?>
                                <span style="background:var(--verde);color:#fff;
                                             border-radius:6px;padding:2px 10px;
                                             font-size:.78rem;font-weight:600;">
                                    <?php echo (int)$u['comprasConfirmadas']; ?>
                                </span>
                            <?php else: ?>
                                <span style="color:var(--gris);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Paginación ── -->
    <?php if ($totalPaginas > 1): ?>
    <nav aria-label="Paginación usuarios" style="margin:24px 1.5rem 0;">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $paginaActual <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo buildQuery(['pagina' => $paginaActual - 1]); ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <li class="page-item <?php echo $p === $paginaActual ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo buildQuery(['pagina' => $p]); ?>"><?php echo $p; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $paginaActual >= $totalPaginas ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo buildQuery(['pagina' => $paginaActual + 1]); ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<!-- FOOTER -->
<section class="footer-section">
    <footer>
        <div class="row">
            <div class="col">
                <h3><strong>Contactanos</strong><div class="subrayado"></div></h3>
                <ul>
                    <li><i class="bi bi-envelope-at"></i><a href="mailto:vuela@seguro.com.ar">vuela@seguro.com.ar</a></li>
                    <li><i class="bi bi-whatsapp"></i><a href="#">+54 9 341 234 5678</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if ($link) mysqli_close($link);
?>