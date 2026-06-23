-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-06-2026 a las 21:47:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `vuelaseguro`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aerolineas`
--

CREATE TABLE `aerolineas` (
  `codAerolinea` int(11) NOT NULL,
  `nombreAerolinea` varchar(100) NOT NULL,
  `codigoIATA` varchar(3) NOT NULL,
  `descripcionAerolinea` varchar(300) NOT NULL,
  `codigoPais` varchar(3) NOT NULL,
  `claveAerolinea` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aerolineas`
--

INSERT INTO `aerolineas` (`codAerolinea`, `nombreAerolinea`, `codigoIATA`, `descripcionAerolinea`, `codigoPais`, `claveAerolinea`) VALUES
(1, 'AndesMensuales', 'AT', 'Viajes anuales para cruzar los Andes (como San Martin)', 'AR', '$2y$10$r3H.xu7kxuXlRKGIT24HjeB3WTb1FhZZneC7tb5VNSqDp96BogIc6'),
(2, 'Caribe Travel', 'CAT', 'Viajes al Caribe todo el año!', 'DM', '$2y$10$4PowHQETityXhU/Ic0IvSOarynf5VUHvordcyLfYK2mH6Vy8ManCe'),
(5, 'Cabos Sueltos', 'CBD', 'Viajes a Cabo Verde, le empatamos a ESPAÑA en la copa del mundo!', 'CV', '$2y$10$SFGoaNNijcmHev015SItaugfKO.pN8dPxhw1JaQXxDqKOUvjloUj2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

CREATE TABLE `novedades` (
  `codNovedad` int(11) NOT NULL,
  `TituloNovedad` varchar(50) NOT NULL,
  `textoNovedad` text NOT NULL,
  `fechaPublicacionNovedad` date NOT NULL,
  `fechaExpiracionNovedad` date NOT NULL,
  `tipoNovedad` enum('Alerta','Importante','Informativa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `novedades`
--

INSERT INTO `novedades` (`codNovedad`, `TituloNovedad`, `textoNovedad`, `fechaPublicacionNovedad`, `fechaExpiracionNovedad`, `tipoNovedad`) VALUES
(1, 'Mantenimiento programado – Aeropuerto Internaciona', 'Debido a tareas de mantenimiento en la pista principal, el aeropuerto permanecerá parcialmente operativo los días 20 y 21 de mayo. Los vuelos afectados serán reprogramados con 48 hs de anticipación.', '2026-05-14', '2026-08-25', 'Importante'),
(2, 'Alerta climática – Rutas patagónicas', 'Condiciones meteorológicas adversas podrían generar demoras o cancelaciones en vuelos con destino a Bariloche, Ushuaia y El Calafate durante la semana del 19 al 23 de mayo.', '2026-05-12', '2026-08-23', 'Alerta'),
(3, 'Check-in online disponible hasta 48 hs antes del v', 'Desde esta semana los pasajeros pueden hacer el check-in online hasta 48 horas antes del vuelo, para todas las aerolíneas registradas en la plataforma.', '2026-05-10', '2026-08-10', 'Informativa'),
(4, 'Nueva terminal en Ezeiza', 'A partir del mes de julio se habilitará la nueva terminal internacional del Aeropuerto de Ezeiza, lo que ampliará la capacidad de embarque y mejorará la experiencia de los pasajeros.', '2026-06-01', '2026-09-01', 'Importante'),
(5, 'Protocolo de seguridad actualizado', 'Se actualizaron los protocolos de seguridad para el ingreso a las terminales. Se solicita a los pasajeros presentar DNI o pasaporte vigente y llegar con 2 horas de anticipación.', '2026-06-03', '2026-12-31', 'Informativa'),
(7, 'Descuentos especiales para jubilados', 'VuelaSeguro, en conjunto con las aerolíneas adheridas, ofrece un 15% de descuento adicional para pasajeros mayores de 60 años que presenten su credencial de jubilado al momento de la compra.', '2026-05-20', '2026-12-31', 'Informativa'),
(11, 'Los knicks ganan las finales de la nba ', 'SI VIAJAS A NUEVA YOL TENE MUCHO CUIDADO', '2026-06-17', '2026-06-18', 'Alerta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `codPromocion` int(11) NOT NULL,
  `descripcionPromocion` text NOT NULL,
  `descuentoPromocion` decimal(10,0) NOT NULL,
  `codAerolinea` int(11) DEFAULT NULL,
  `estadoPromocion` varchar(20) NOT NULL DEFAULT 'pendiente',
  `imagenPromocion` varchar(500) DEFAULT NULL,
  `vigenciaPromocion` date DEFAULT NULL,
  `codCEO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`codPromocion`, `descripcionPromocion`, `descuentoPromocion`, `codAerolinea`, `estadoPromocion`, `imagenPromocion`, `vigenciaPromocion`, `codCEO`) VALUES
(1, '25% OFF en Vuelos a el Cusco', 25, 2, 'denegada', 'https://www.boletomachupicchu.com/gutblt/wp-content/uploads/2025/11/viaje-cusco-full.jpg', '2026-07-25', 2),
(2, '25% OFF en Vuelos de Caribe Travel', 25, 2, 'aprobada', 'https://www.civitatis.com/blog/wp-content/uploads/2024/01/shutterstock_607235345-scaled.jpg', '2026-07-25', 2),
(3, '10% OFF en Vuelos de AndesMensuales', 10, 1, 'aprobada', 'https://upload.wikimedia.org/wikipedia/commons/f/f3/Panoramic_view_Andes-Chile.jpg', '2026-09-11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `codReserva` int(11) NOT NULL,
  `codUsuario` int(11) NOT NULL,
  `codVuelo` int(11) NOT NULL,
  `fechaReserva` date NOT NULL,
  `estadoReserva` enum('Pendiente de pago','Confirmada') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`codReserva`, `codUsuario`, `codVuelo`, `fechaReserva`, `estadoReserva`) VALUES
(1, 7, 6, '2026-06-21', 'Confirmada'),
(2, 7, 7, '2026-06-21', 'Confirmada'),
(3, 7, 8, '2026-06-21', 'Confirmada'),
(4, 7, 13, '2026-06-21', 'Confirmada'),
(6, 7, 8, '2026-06-21', 'Confirmada'),
(9, 8, 11, '2026-06-21', 'Confirmada'),
(10, 8, 8, '2026-06-21', 'Confirmada'),
(11, 8, 15, '2026-06-21', 'Confirmada'),
(12, 8, 10, '2026-06-21', 'Confirmada'),
(15, 8, 8, '2026-06-21', 'Confirmada'),
(16, 7, 14, '2026-06-21', 'Confirmada'),
(17, 8, 16, '2026-06-21', 'Confirmada'),
(20, 5, 8, '2026-06-22', 'Pendiente de pago'),
(21, 5, 11, '2026-06-22', 'Pendiente de pago');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_promo`
--

CREATE TABLE `solicitudes_promo` (
  `codSolicitud` int(11) NOT NULL,
  `codUsuario` int(11) NOT NULL,
  `codPromocion` int(11) NOT NULL,
  `fechaSolicitud` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_promo`
--

INSERT INTO `solicitudes_promo` (`codSolicitud`, `codUsuario`, `codPromocion`, `fechaSolicitud`) VALUES
(1, 5, 3, '2026-06-22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `codUsuario` int(11) NOT NULL,
  `nombreUsuario` varchar(100) DEFAULT NULL,
  `claveUsuario` varchar(255) NOT NULL,
  `tipoUsuario` varchar(20) DEFAULT NULL,
  `emailUsuario` varchar(100) DEFAULT NULL,
  `telefonoUsuario` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`codUsuario`, `nombreUsuario`, `claveUsuario`, `tipoUsuario`, `emailUsuario`, `telefonoUsuario`) VALUES
(1, 'Andres De Luca', '123', 'usuario', 'anddrers@gmail.com', '1234'),
(2, 'Mateo', '123', 'CEO', 'mateo@gmail.com', '12345'),
(3, 'Lucio', 'admin123', 'admin', 'luciocasadedio.a@gmail.com', '34165678978'),
(4, 'Jacob Lash', '$2y$10$/', 'usuario', 'jacob@gmail.com', '31231231113213'),
(5, 'Profesor Dynamo', '$2y$10$5uXzsFB3iuf5H4UvnnGkbuPZbkF0vOwaBu6lTl7g8LST7283O4aV2', 'usuario', 'dynamo@gmail.com', '32131231231313'),
(6, 'admin admin', '$2y$10$USzA2JI.UcRN2PMMiKK6sOjKHr.fZIp77oM0z9QKeOtNQvh2iKAPi', 'admin', 'admin@gmail.com', '12345'),
(7, 'Mateo Sampaulesi', '$2y$10$szEfo0F358gqDs2q.wFXeOk7DGlnHIfczcN5CGeCIkl04XRRbPFve', 'usuario', 'mateosampaulesicve@gmail.com', '03413117530'),
(8, 'Alan Heis', '$2y$10$6cB8YMf.vJBPFJHkt64JuuNpDcAd6W67Xq/SH/EORiOq4Sa/YP18y', 'usuario', 'alanjheis@gmail.com', '3415836423');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vuelos`
--

CREATE TABLE `vuelos` (
  `codVuelo` int(11) NOT NULL,
  `codAerolinea` int(11) DEFAULT NULL,
  `origenVuelo` varchar(50) DEFAULT NULL,
  `destinoVuelo` varchar(50) DEFAULT NULL,
  `fechaSalidaVuelo` date DEFAULT NULL,
  `horaSalidaVuelo` time(5) DEFAULT NULL,
  `fechaVuelta` date DEFAULT NULL,
  `horaVuelta` time(5) DEFAULT NULL,
  `precioVuelo` decimal(10,0) DEFAULT NULL,
  `asientosDisponibles` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vuelos`
--

INSERT INTO `vuelos` (`codVuelo`, `codAerolinea`, `origenVuelo`, `destinoVuelo`, `fechaSalidaVuelo`, `horaSalidaVuelo`, `fechaVuelta`, `horaVuelta`, `precioVuelo`, `asientosDisponibles`) VALUES
(8, 2, 'España', 'Arabia Saudita', '2026-06-28', '12:00:00.00000', '0000-00-00', '00:00:00.00000', 1000000, 295),
(11, 1, 'Uruguay', 'Cabo Verde', '2026-06-21', '19:00:00.00000', '0000-00-00', '00:00:00.00000', 300000, 198),
(12, 1, 'Nueva Zelanda', 'Egipto', '2026-06-21', '22:00:00.00000', '2026-07-05', '08:00:00.00000', 400000, 250),
(13, 1, 'Nueva Zelanda', 'Egipto', '2026-06-21', '19:00:00.00000', '2026-07-05', '10:00:00.00000', 350000, 130),
(14, 1, 'Argentina', 'Austria', '2026-06-22', '14:00:00.00000', '0000-00-00', '00:00:00.00000', 10000000, 99),
(15, 2, 'Francia', 'Irak', '2026-06-22', '18:00:00.00000', '2026-06-29', '00:00:00.00000', 6000000, 30),
(16, 1, 'Noruega', 'Senegal', '2026-06-22', '09:00:00.00000', '2026-06-29', '14:00:00.00000', 300000, 199);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aerolineas`
--
ALTER TABLE `aerolineas`
  ADD PRIMARY KEY (`codAerolinea`);

--
-- Indices de la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`codNovedad`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`codPromocion`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`codReserva`);

--
-- Indices de la tabla `solicitudes_promo`
--
ALTER TABLE `solicitudes_promo`
  ADD PRIMARY KEY (`codSolicitud`),
  ADD UNIQUE KEY `uq_usuario_promo` (`codUsuario`,`codPromocion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`codUsuario`);

--
-- Indices de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  ADD PRIMARY KEY (`codVuelo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aerolineas`
--
ALTER TABLE `aerolineas`
  MODIFY `codAerolinea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `novedades`
--
ALTER TABLE `novedades`
  MODIFY `codNovedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `codPromocion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `codReserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `solicitudes_promo`
--
ALTER TABLE `solicitudes_promo`
  MODIFY `codSolicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `codUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  MODIFY `codVuelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
