<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'vuelaseguro';

$link = mysqli_connect($host, $username, $password, $database);

if (!$link) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error());
}

$sql = "SELECT * FROM vuelos"; 
$result = mysqli_query($link, $sql);
$totalVuelos = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>VuelaSeguro – Resultados de búsqueda</title>

  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>

  <!--Links de estilos-->
  <link href="../INDEX/estilos-globales.css" rel="stylesheet">
  <link href="vuelos.css" rel="stylesheet">

</head>

<body>

  <!-- NAVBAR ORIGINAL -->

  <section class="navbar-section">

    <div class="header-wrapper">

      <nav class="navbar-custom">

        <div class="logo-wrap">
          <img src="../INDEX/logo-vuelaseguro.png" class="logo-vuela" alt="Logo VuelaSeguro">
        </div>

        <div class="nav-links">
          <a href="../INDEX/index.php">Inicio</a>
          <a href="../VUELOS/vuelos.php" class="active">Vuelos</a>
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

          <button class="btn-registro">Registrarse</button>

        </div>


      </nav>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="../INDEX/index.php">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Vuelos</li>
        </ol>
      </nav>

    </div>

  </section>

  <!-- CONTENIDO -->

  <div class="resultados-wrapper">

    <!-- SIDEBAR -->

    <aside class="sidebar">

      <h3>MODIFICAR BÚSQUEDA</h3>

      <div class="sidebar-grupo">
        <label>Origen</label>
        <input class="sidebar-input" type="text">
      </div>

      <div class="sidebar-grupo">
        <label>Destino</label>
        <input class="sidebar-input" type="text">
      </div>

      <div class="sidebar-grupo">
        <label>Ida fecha</label>
        <input class="sidebar-input-date" type="date" onclick="this.showPicker()">
      </div>

      <div class="sidebar-grupo">
        <label>Vuelta fecha</label>
        <input class="sidebar-input-date" type="date" onclick="this.showPicker()">
      </div>

      <div class="sidebar-grupo">
        <label>Cantidad pasajeros</label>
        <input class="sidebar-input" type="number">
      </div>

      <button class="btn-aplicar">Buscar</button>

    </aside>

    <!-- LISTA VUELOS -->

    <div class="vuelos-lista">

      <div class="vuelos-header">
        <h2>Vuelos disponibles</h2>
        <span class="vuelos-count"><?php echo $totalVuelos; ?></span>
      </div>

      <!-- VUELO 1 -->

      <div class="vuelo-card">

        <div class="vuelo-info">
<?php if ($totalVuelos > 0) { ?>
          <div class="vuelo-aerolinea-row">
            <span class="vuelo-aerolinea">Aerolíneas Argentinas</span>
            <span class="badge-barato">MÁS BARATO</span>
          </div>

          <div class="vuelo-ruta">

            <div>
              <span class="ciudad-nombre">Buenos Aires</span>
              <span class="ciudad-horario">Salida: 06:45 hs</span>
            </div>

            <div>
              <span class="ciudad-nombre">Mendoza</span>
              <span class="ciudad-horario">Llegada: 08:40 hs</span>
            </div>

          </div>

          <div class="vuelo-detalles-row">
            <div>Pasajeros: <strong>1</strong></div>
            <div>Duración: <strong>1h 55m</strong></div>
            <div>Equipaje incluido: <span class="equipaje-si">✓</span></div>
          </div>

        </div>

        <div class="vuelo-precio-col">
          <span class="precio-label">PRECIO</span>
          <span class="precio-valor">$89.990</span>
          <button class="btn-comprar">COMPRAR</button>
        </div>
       </div>
    </div>
      <?php } else { 
   echo "<p style='text-align: center; margin-top: 40px; color: --gris;'>No hay vuelos disponibles en este momento.</p>"; 
} ?>
  </div> 
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

</body>
</html>