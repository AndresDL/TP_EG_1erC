-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-06-2026 a las 20:43:06
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
  `drescripcionAerolinea` text NOT NULL,
  `codPais` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `tipoNovedad` enum('Alerta','Importante','Informativa','') NOT NULL
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
(7, 'Descuentos especiales para jubilados', 'VuelaSeguro, en conjunto con las aerolíneas adheridas, ofrece un 15% de descuento adicional para pasajeros mayores de 60 años que presenten su credencial de jubilado al momento de la compra.', '2026-05-20', '2026-12-31', 'Informativa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `codPromocion` int(11) NOT NULL,
  `descripcionPromocion` text NOT NULL,
  `descuentoPromocion` decimal(10,0) NOT NULL,
  `codAerolinea` int(11) NOT NULL,
  `estadoPromocion` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `codReserva` int(11) NOT NULL,
  `codUsuario` int(11) NOT NULL,
  `codVuelo` int(11) NOT NULL,
  `fechaReserva` date NOT NULL,
  `estadoReserva` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `codUsuario` int(11) NOT NULL,
  `nombreUsuario` varchar(100) DEFAULT NULL,
  `claveUsuario` varchar(8) DEFAULT NULL,
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
(3, 'Lucio', 'admin123', 'admin', 'luciocasadedio.a@gmail.com', '34165678978');

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
  `precioVuelo` decimal(10,0) DEFAULT NULL,
  `asientosDisponibles` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`codNovedad`);

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
-- AUTO_INCREMENT de la tabla `novedades`
--
ALTER TABLE `novedades`
  MODIFY `codNovedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `codUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  MODIFY `codVuelo` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
