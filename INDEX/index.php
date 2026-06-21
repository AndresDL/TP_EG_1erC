<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}
// Últimas 3 novedades vigentes
$resNovedades = mysqli_query($link,
    "SELECT * FROM novedades
     WHERE fechaExpiracionNovedad >= CURDATE()
     ORDER BY fechaPublicacionNovedad DESC
     LIMIT 3");

// Ultimas 3 promociones aprobadas para el carrusel
$resPromos = mysqli_query($link,
    "SELECT p.*, a.nombreAerolinea FROM promociones p
     LEFT JOIN aerolineas a ON p.codAerolinea = a.codAerolinea
     WHERE p.estadoPromocion = 'aprobada'
     ORDER BY p.codPromocion DESC
     LIMIT 3");

$usuario     = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
$tipoUsuario = $usuario ? $usuario['tipoUsuario'] : 'no_registrado';

function badgeNovedad($tipo) {
    return match($tipo) {
        'Importante'  => '<span class="badge-nov badge-imp"><i class="bi bi-star-fill me-1"></i>Importante</span>',
        'Alerta'      => '<span class="badge-nov badge-alt"><i class="bi bi-cloud-lightning me-1"></i>Alerta</span>',
        'Informativa' => '<span class="badge-nov badge-info">Informativa</span>',
        default       => '<span class="badge-nov badge-info">' . htmlspecialchars($tipo) . '</span>',
    };
}

function claseNovedad($tipo) {
    return match($tipo) {
        'Importante' => 'destacada',
        'Alerta'     => 'alerta',
        default      => '',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>VuelaSeguro – Inicio</title>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@700&display=swap" rel="stylesheet"/>
  <link href="estilos-globales.css" rel="stylesheet">
  <link href="estilos-novedades.css" rel="stylesheet">
  <link href="estilos-inicio.css" rel="stylesheet">
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
          <a href="../INDEX/index.php" class="active">Inicio</a>
          <a href="../VUELOS/vuelos.php">Vuelos</a>
          <a href="../NOVEDADES/novedades.php" >Novedades</a>
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
              <span class="text-white me-2"><a href="../PERFIL/perfiles.php" style="text-decoration: none; color: white">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?><a></strong></span>
              <a href="../LOGIN/logout.php" class="btn-registro" style="text-decoration:none;background:#dc3545;">Cerrar sesion</a>
          <?php else: ?>
              <a href="../LOGIN/login.php" class="btn-registro" style="text-decoration: none; color: white;">Iniciar sesión</a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </section>
</header>

<!-- HERO + FILTRO -->
<section class="hero">
  <h1>Tu próximo vuelo, <span style="color:#7ab4ff;">seguro y simple</span></h1>
  <p>Buscá vuelos, consultá novedades y aprovechá las mejores promociones.</p>

  <div class="filtro-hero">
    <input class="filtro-input" type="text" placeholder="✈ Origen" id="origen"/>
    <input class="filtro-input" type="text" placeholder="✈ Destino" id="destino"/>
    <div class="fecha-wrap">
      <div class="fecha-label" id="labelIda">
        <span id="textoIda">Ida</span>
        <i class="bi bi-calendar3" style="color:var(--gris);"></i>
      </div>
      <input type="date" onchange="setFecha(this,'textoIda','Ida')"/>
    </div>
    <div class="fecha-wrap">
      <div class="fecha-label" id="labelVuelta" title="Fecha de vuelta (opcional)">
        <span id="textoVuelta" style="color: var(--gris); font-size: .78rem">Vuelta (opcional)</span>
        <i class="bi bi-calendar3" style="color:var(--gris);"></i>
      </div>
      <input type="date" onchange="setFecha(this,'textoVuelta','Vuelta (opcional)')"/>
    </div>
    <input class="filtro-input" type="number" placeholder="👤 Pasajeros" min="1" style="max-width:130px;"/>
    <button class="btn-buscar-hero" onclick="buscarVuelos()">
      <i class="bi bi-search me-1"></i> Buscar
    </button>
  </div>
</section>

<!-- CONTENIDO PRINCIPAL -->
<main class="container py-5" style="max-width:1060px;">

  <?php
  // Stats dinámicos desde la BD
  $totalVuelos  = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) AS n FROM vuelos"))['n'] ?? 0;
  $totalPromos  = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) AS n FROM promociones WHERE estadoPromocion='aprobada'"))['n'] ?? 0;
  $totalAero    = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) AS n FROM aerolineas"))['n'] ?? 0;
  ?>
  <div class="stats-strip">
    <div class="stat-item">
      <div class="stat-num"><?= $totalVuelos ?>+</div>
      <div class="stat-label">Vuelos disponibles</div>
    </div>
    <div class="stat-item">
      <div class="stat-num"><?= $totalAero ?>+</div>
      <div class="stat-label">Aerolíneas</div>
    </div>
    <div class="stat-item">
      <div class="stat-num"><?= $totalPromos ?>+</div>
      <div class="stat-label">Promociones activas</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">24/7</div>
      <div class="stat-label">Soporte disponible</div>
    </div>
  </div>

  <!-- NOVEDADES -->
  <section class="seccion-bloque">
    <div class="seccion-titulo">
      <h2><i class="bi bi-newspaper me-2" style="color:var(--azul);"></i>Novedades</h2>
      <a href="../NOVEDADES/novedades.php">Ver todas <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (!$resNovedades || mysqli_num_rows($resNovedades) === 0): ?>
      <p style="color:var(--gris);">No hay novedades vigentes por el momento.</p>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php while ($nov = mysqli_fetch_assoc($resNovedades)): ?>
          <div class="col">
            <div class="novedad-card <?= claseNovedad($nov['tipoNovedad']) ?> h-100">
              <?= badgeNovedad($nov['tipoNovedad']) ?>
              <div class="nov-titulo-card"><?= htmlspecialchars($nov['TituloNovedad']) ?></div>
              <p class="nov-texto"><?= htmlspecialchars($nov['textoNovedad']) ?></p>
              <div class="nov-fecha">
                <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($nov['fechaPublicacionNovedad'])) ?></span>
                <span class="nov-vence"><i class="bi bi-clock"></i> Vence: <?= date('d/m', strtotime($nov['fechaExpiracionNovedad'])) ?></span>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- PROMOCIONES DESTACADAS -->
  <section class="seccion-bloque">
    <div class="seccion-titulo">
      <h2><i class="bi bi-tags me-2" style="color:var(--azul);"></i>Promociones Destacadas</h2>
      <a href="../PROMOCIONES/promociones.php">Ver todas <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php
    $promos = [];
    if ($resPromos) {
        while ($p = mysqli_fetch_assoc($resPromos)) $promos[] = $p;
    }
    ?>

    <?php if (empty($promos)): ?>
      <div style="background:var(--blanco);border:1px solid var(--borde);border-radius:16px;padding:48px;text-align:center;color:var(--gris);">
        <i class="bi bi-tags fs-1 d-block mb-3" style="opacity:.3;"></i>
        <p>No hay promociones activas por el momento.</p>
        <a href="../PROMOCIONES/promociones.php" style="color:var(--azul);font-weight:600;">Ver sección de promociones</a>
      </div>
    <?php else: ?>
      <div class="promo-carousel-wrap">
        <div id="carouselInicio" class="carousel slide" data-bs-ride="carousel">

          <!-- Indicadores -->
          <?php if (count($promos) > 1): ?>
          <div class="carousel-indicators">
            <?php foreach ($promos as $i => $p): ?>
              <button type="button" data-bs-target="#carouselInicio"
                      data-bs-slide-to="<?= $i ?>"
                      <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                      aria-label="Promoción <?= $i+1 ?>"></button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="carousel-inner">
            <?php foreach ($promos as $i => $promo):
              $imgSrc = '';
              if (!empty($promo['imagenPromocion'])) {
                  $imgSrc = htmlspecialchars($promo['imagenPromocion']);
              }
            ?>
              <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <?php if ($imgSrc): ?>
                  <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($promo['descripcionPromocion']) ?>"
                       onerror="this.parentElement.querySelector('.promo-placeholder').style.display='flex'; this.style.display='none';">
                <?php endif; ?>
                <?php if (!$imgSrc): ?>
                  <div class="promo-placeholder">
                    <i class="bi bi-image fs-1"></i>
                    <span style="font-size:.9rem;">Sin imagen</span>
                  </div>
                <?php endif; ?>
                <div class="carousel-caption">
                  <span class="promo-tag"><?= number_format($promo['descuentoPromocion'], 0) ?>% OFF</span>
                  <h3><?= htmlspecialchars($promo['nombreAerolinea'] ?? 'Promoción especial') ?></h3>
                  <p><?= htmlspecialchars($promo['descripcionPromocion']) ?></p>
                  <?php if (!empty($promo['vigenciaPromocion'])): ?>
                    <div class="vigencia">
                      <i class="bi bi-clock me-1"></i>
                      Vigencia hasta <?= date('d/m/Y', strtotime($promo['vigenciaPromocion'])) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (count($promos) > 1): ?>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselInicio" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselInicio" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
          </button>
          <?php endif; ?>

        </div>
      </div>
    <?php endif; ?>
  </section>

</main>

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
          <li><a href="index.php">Inicio</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var fechaIdaVal = '';
var fechaVueltaVal = '';

function setFecha(input, labelId, fallback) {

   const el = document.getElementById(labelId);
    if (input.value) {
        const [y, m, d] = input.value.split('-');
        el.textContent = d + '/' + m + '/' + y;
        el.style.color = 'var(--azul-oscuro)';
    } else {
        el.textContent = fallback;
        el.style.color = 'var(--gris)';
    }
}

function buscarVuelos() {
    var origen = document.getElementById('origen').value.trim();
    var destino = document.getElementById('destino').value.trim();
    var pasajeros = document.querySelector('.filtro-input[placeholder*="Pasajeros"]').value.trim();
    var params = new URLSearchParams();
    if (origen) params.append('origen', origen);
    if (destino) params.append('destino', destino);
    if (fechaIdaVal) params.append('fechaIda', fechaIdaVal);
    if (fechaVueltaVal) params.append('fechaVuelta', fechaVueltaVal);
    if (pasajeros) params.append('pasajeros', pasajeros);
    window.location.href = '../VUELOS/vuelos.php?' + params.toString();
}
</script>

<?php mysqli_close($link); ?>
</body>
</html>