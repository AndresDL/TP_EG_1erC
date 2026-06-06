<!DOCTYPE html>
<html lang="es">
<head>
  <title>VuelaSeguro</title>

  <!--Metadatos-->
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>

  <link href="estilos-globales.css" rel="stylesheet">
  <link href="estilos-novedades.css" rel="stylesheet">
  <link href="estilos-promociones.css" rel="stylesheet">
   <style>
    body { background: var(--gris-claro) !important; }
  </style>

  <!-- Bootstrap CSS  -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  
</head>

<body>
  <section class="navbar-section">
    <div class="header-wrapper">
      <nav class="navbar-custom">
        <div class="logo-wrap">
          <img src="./logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
        </div>
        <div class="nav-links">
          <a href="../INDEX/index.php" class="active">Inicio</a>
          <a href="../VUELOS/vuelos.php">Vuelos</a>
          <a href="../NOVEDADES/novedades.php">Novedades</a>
          <a href="../PROMOCIONES/promociones.php">Promociones</a>
        </div>
        <div class="nav-right">
          <div class="foto-perfil" title="Foto de perfil">
            <svg width="26" height="40" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <circle cx="21" cy="10" r="9" fill="#ffffff"/>
              <path d="M -4 42 Q 21 7 46 42 Z" fill="#ffffff"/>
            </svg>
          </div>
          <a class="btn-registro" href="../LOGIN/login.php">Iniciar Sesión</a>
        </div>
      </nav>
    </div>
    <div class="filtro-bar">
      <span class="filtro-titulo">Filtrar vuelos</span>
      <input class="filtro-input" type="text" placeholder="Origen"/>
      <input class="filtro-input" type="text" placeholder="Destino"/>
      <div class="fecha-container">
        <div class="fecha-espejo" id="espejoIda">
          <span>dd/mm/aaaa</span>
          <span class="label-texto">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path></svg>
            - Ida
          </span>
        </div>
        <input class="fecha-input" type="date" id="fechaIda" onchange="actualizarFecha(this, 'espejoIda', '- Ida')" onclick="this.showPicker()"/>
      </div>
      <div class="fecha-container">
        <div class="fecha-espejo" id="espejoVuelta">
          <span>dd/mm/aaaa</span>
          <span class="label-texto">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path></svg>
            - Vuelta
          </span>
        </div>
        <input class="fecha-input" type="date" id="fechaVuelta" onchange="actualizarFecha(this, 'espejoVuelta', '- Vuelta')" onclick="this.showPicker()"/>
      </div>
      <input class="filtro-input" type="text" placeholder="Pasajeros"/>
      <button class="btn-buscar">Buscar</button>
    </div>
    <script>
      function actualizarFecha(input, espejoId, labelTexto) {
        const espejo = document.getElementById(espejoId);
        if (input.value) {
          const partes = input.value.split('-');
          const fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
          espejo.innerHTML = `<span style="color: #333;">${fechaFormateada}</span><span class="label-texto" style="color: var(--gris);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path></svg> ${labelTexto}</span>`;
        } else {
          espejo.innerHTML = `<span>dd/mm/aaaa</span><span class="label-texto"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path></svg> ${labelTexto}</span>`;
        }
      }
    </script>
  </section>
  <section class="novedades-section" id="novedades">
    <div class="container px-3 px-md-4">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="m-0" style="color: var(--azul); font-weight: bold;">Novedades</h2>
        <a href="novedades.html" style="font-size: .85rem; color: var(--azul-m); text-decoration: none; font-weight: 600;">
          <u>Ver todas <i class="bi bi-arrow-right"></i></u>
        </a>
      </div>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
        
        <!-- Novedad Destacada -->
        <div class="col">
          <div class="novedad-card destacada"> 
            <span class="badge-nov badge-imp"><i class="bi bi-star-fill me-1"></i>Importante</span>
            <div class="nov-titulo-card">Mantenimiento programado – Aeropuerto Internacional Rosario</div>
            <p class="nov-texto">
              Debido a tareas de mantenimiento en la pista principal, el aeropuerto permanecerá
              parcialmente operativo los días 20 y 21 de mayo. Los vuelos afectados serán
              reprogramados con 48 hs de anticipación.
            </p>
            <div class="nov-fecha">
              <span><i class="bi bi-calendar3"></i> 14/05/2026</span>
              <span class="nov-vence"><i class="bi bi-clock"></i>Vence: 25/05</span>
            </div>
          </div>
        </div>

        <!-- Novedad Alerta -->
        <div class="col">
          <div class="novedad-card alerta">
            <span class="badge-nov badge-alt"><i class="bi bi-cloud-lightning me-1"></i>Alerta</span>
            <div class="nov-titulo-card">Alerta climática – Rutas patagónicas</div>
            <p class="nov-texto">
              Condiciones meteorológicas adversas podrían generar demoras o cancelaciones en vuelos
              con destino a Bariloche, Ushuaia y El Calafate durante la semana del 19 al 23 de mayo.
            </p>
            <div class="nov-fecha">
              <span><i class="bi bi-calendar3"></i> 12/05/2026</span>
              <span class="nov-vence"><i class="bi bi-clock"></i>Vence: 23/05</span>
            </div>
          </div>
        </div>
        
        <!-- Novedad Informativa -->
        <div class="col">
          <div class="novedad-card">
            <span class="badge-nov badge-info">Informativa</span>
            <div class="nov-titulo-card">Check-in online disponible hasta 48 hs antes del vuelo</div>
            <p class="nov-texto">
              Desde esta semana los pasajeros pueden hacer el check-in online hasta 48 horas antes
              del vuelo, para todas las aerolíneas registradas en la plataforma.
            </p>
            <div class="nov-fecha">
              <span><i class="bi bi-calendar3"></i> 10/05/2026</span>
              <span class="nov-vence"><i class="bi bi-clock"></i>Vence: 10/08</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>    

  <!--PROMOCIONES-->
   <section class="promociones-section">

    <h2 class="text-center mb-4"> Promociones Destacadas</h2>
    <div class="contenedor-carousel">
      <div id="carouselPromos" class="carousel slide" data-bs-ride="carousel" style="position:relative;">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="https://www.radioromance.com/wp-content/uploads/2025/01/PATOS-1024x576.jpeg" class="d-block w-100" alt="Promo 1">
            <div class="carousel-caption">
              <h3>Promocion 1</h3>
              <p>30% OFF en vuelos nacionales</p>
              <span>Vigencia: 01/06/2026 - 30/06/2026</span>
            </div>
          </div>
          <div class="carousel-item">
            <img src="https://verdecora.es/blog/wp-content/uploads/2025/06/cuidados-pato-casa.jpg" class="d-block w-100" alt="Promo 2">
            <div class="carousel-caption">
              <h3>Promocion 2</h3>
              <p>20% OFF en vuelos nacionales</p>
              <span>Vigencia: 03/07/2026 - 20/08/2026</span>
            </div>
          </div>
          <div class="carousel-item">
            <img src="https://findelmundo.tur.ar/imagecache/large/Anas-georgica-de-Jorge-La-Gotteria.jpg" class="d-block w-100" alt="Promo 3">
            <div class="carousel-caption">
              <h3>Promocion 3</h3>
              <p>25% OFF en vuelos nacionales</p>
              <span>Vigencia: 04/05/2026 - 22/06/2026</span>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPromos" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselPromos" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </div>
  </section>
  
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
          </div>
        </div>
      </div>
      <hr>
      <p class="copyright">&copy; 2026 VuelaSeguro. Todos los derechos reservados. Licenciado bajo
        <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons BY 4.0</a>.
      </p>
    </footer>
  </section>
 

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>