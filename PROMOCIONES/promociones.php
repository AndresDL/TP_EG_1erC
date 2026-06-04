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
          <div class="foto-perfil" title="Foto de perfil">
            <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <circle cx="21" cy="10" r="9" fill="#ffffff"/>
              <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
            </svg>
          </div>
          <button class="btn-registro">Registrarse</button>
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

  <div class="promociones-wrapper">

    <div class="promociones-header">
      <h2>Promociones disponibles</h2>
      <span style="color:#666666;">4 resultados</span>
    </div>

    <div class="promociones-filtro">
      <label>Filtrar Aerolínea</label>
      <input type="text" class="filtro-input-promo" placeholder="Ingrese aerolínea">
    </div>

    <div class="promociones-grid">

      <div class="promo-card">
        <img src="img/promo1.jpg" alt="Promo 1">
        <div class="card-body">
          <h5 class="card-title">Aerolíneas Argentinas</h5>
          <p class="card-text">30% OFF en vuelos nacionales</p>
          <p class="promo-vigencia"><i class="bi bi-clock me-1"></i>Vigencia hasta 30/11/2026</p>
          <button class="btn-solicitar">SOLICITAR</button>
        </div>
      </div>

      <div class="promo-card">
        <img src="img/promo2.jpg" alt="Promo 2">
        <div class="card-body">
          <h5 class="card-title">FlyBondi</h5>
          <p class="card-text">15% OFF primera compra</p>
          <p class="promo-vigencia"><i class="bi bi-clock me-1"></i>Vigencia hasta 15/12/2026</p>
          <button class="btn-solicitar">SOLICITAR</button>
        </div>
      </div>

      <div class="promo-card">
        <img src="img/promo3.jpg" alt="Promo 3">
        <div class="card-body">
          <h5 class="card-title">LATAM</h5>
          <p class="card-text">20% OFF vuelos internacionales</p>
          <p class="promo-vigencia"><i class="bi bi-clock me-1"></i>Vigencia hasta 20/12/2026</p>
          <button class="btn-solicitar">SOLICITAR</button>
        </div>
      </div>

      <div class="promo-card">
        <img src="img/promo4.jpg" alt="Promo 4">
        <div class="card-body">
          <h5 class="card-title">Jetsmart</h5>
          <p class="card-text">10% OFF ida y vuelta</p>
          <p class="promo-vigencia"><i class="bi bi-clock me-1"></i>Vigencia hasta 10/01/2027</p>
          <button class="btn-solicitar">SOLICITAR</button>
        </div>
      </div>

    </div>

    <!-- PAGINACIÓN -->
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item"><a class="page-link" href="#">Anterior</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Siguiente</a></li>
      </ul>
    </nav>

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
            <li><i class="bi bi-pen"></i><a href="../CONTACTO/contacto.html">Formulario de Contacto</a></li>
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