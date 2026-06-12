DROP TABLE IF EXISTS `promociones`;
 
CREATE TABLE `promociones` (
  `codPromocion`         int(11)         NOT NULL AUTO_INCREMENT,
  `descripcionPromocion` text            NOT NULL,
  `descuentoPromocion`   decimal(10,2)   NOT NULL,
  `codAerolinea`         int(11)         DEFAULT NULL,   -- NULL si tabla aerolineas vacía
  `estadoPromocion`      enum('pendiente','aprobada','denegada') NOT NULL DEFAULT 'pendiente',
  `imagenPromocion`      varchar(500)    DEFAULT NULL,
  `vigenciaPromocion`    date            DEFAULT NULL,
  `codCEO`               int(11)         DEFAULT NULL,
  PRIMARY KEY (`codPromocion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;