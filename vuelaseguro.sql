-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2026 a las 03:13:11
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
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `codUsuario` int(11) NOT NULL,
  `nombreUsuario` varchar(100) DEFAULT NULL,
  `claveUsuario` varchar(255) NOT NULL,
  `tipoUsuario` varchar(20) DEFAULT NULL,
  `emailUsuario` varchar(100) DEFAULT NULL,
  `telefonoUsuario` varchar(20) DEFAULT NULL,
  `emailVerificado` tinyint(1) NOT NULL DEFAULT 0,
  `tokenVerificacion` varchar(64) DEFAULT NULL,
  `tokenReset` varchar(64) DEFAULT NULL,
  `tokenResetExpira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`codUsuario`, `nombreUsuario`, `claveUsuario`, `tipoUsuario`, `emailUsuario`, `telefonoUsuario`, `emailVerificado`, `tokenVerificacion`, `tokenReset`, `tokenResetExpira`) VALUES
(1, 'Andres De Luca', '123', 'usuario', 'anddrers@gmail.com', '1234', 0, NULL, NULL, NULL),
(2, 'Mateo', '123', 'CEO', 'mateo@gmail.com', '12345', 0, NULL, NULL, NULL),
(3, 'Lucio', 'admin123', 'admin', 'luciocasadedio.a@gmail.com', '34165678978', 0, NULL, NULL, NULL),
(4, 'Jacob Lash', '$2y$10$/', 'usuario', 'jacob@gmail.com', '31231231113213', 0, NULL, NULL, NULL),
(5, 'Profesor Dynamo', '$2y$10$5uXzsFB3iuf5H4UvnnGkbuPZbkF0vOwaBu6lTl7g8LST7283O4aV2', 'usuario', 'dynamo@gmail.com', '32131231231313', 0, NULL, NULL, NULL),
(6, 'admin admin', '$2y$10$USzA2JI.UcRN2PMMiKK6sOjKHr.fZIp77oM0z9QKeOtNQvh2iKAPi', 'admin', 'admin@gmail.com', '12345', 0, NULL, NULL, NULL),
(7, 'Mateo Sampaulesi', '$2y$10$szEfo0F358gqDs2q.wFXeOk7DGlnHIfczcN5CGeCIkl04XRRbPFve', 'usuario', 'mateosampaulesicve@gmail.com', '03413117530', 0, NULL, NULL, NULL),
(8, 'Alan Heis', '$2y$10$6cB8YMf.vJBPFJHkt64JuuNpDcAd6W67Xq/SH/EORiOq4Sa/YP18y', 'usuario', 'alanjheis@gmail.com', '3415836423', 0, NULL, NULL, NULL),
(9, 'lucio casadedio', '$2y$10$iNMkNybV5.e0y52DOliOL.yyHwo0B7.3HOSHQg4ERO4o6l./7CzsK', 'usuario', 'cuentaparatodo0100@gmail.com', '1312312312', 1, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`codUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `codUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
