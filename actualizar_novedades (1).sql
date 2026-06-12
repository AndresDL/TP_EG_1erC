-- ============================================================
-- INSERCIÓN DE NOVEDADES DE EJEMPLO
-- Ejecutar este script en phpMyAdmin o MySQL CLI
-- Las columnas tituloNovedad y tipoNovedad ya fueron agregadas
-- ============================================================

USE `vuelaseguro`;

INSERT INTO `novedades` (`tituloNovedad`, `tipoNovedad`, `textoNovedad`, `fechaPublicacionNovedad`, `fechaExpiracionNovedad`) VALUES
('Mantenimiento programado – Aeropuerto Internacional Rosario', 'importante',
 'Debido a tareas de mantenimiento en la pista principal, el aeropuerto permanecerá parcialmente operativo los días 20 y 21 de mayo. Los vuelos afectados serán reprogramados con 48 hs de anticipación.',
 '2026-05-14', '2026-08-25'),

('Alerta climática – Rutas patagónicas', 'alerta',
 'Condiciones meteorológicas adversas podrían generar demoras o cancelaciones en vuelos con destino a Bariloche, Ushuaia y El Calafate durante la semana del 19 al 23 de mayo.',
 '2026-05-12', '2026-08-23'),

('Check-in online disponible hasta 48 hs antes del vuelo', 'informativa',
 'Desde esta semana los pasajeros pueden hacer el check-in online hasta 48 horas antes del vuelo, para todas las aerolíneas registradas en la plataforma.',
 '2026-05-10', '2026-08-10'),

('Nueva terminal en Ezeiza', 'importante',
 'A partir del mes de julio se habilitará la nueva terminal internacional del Aeropuerto de Ezeiza, lo que ampliará la capacidad de embarque y mejorará la experiencia de los pasajeros.',
 '2026-06-01', '2026-09-01'),

('Protocolo de seguridad actualizado', 'informativa',
 'Se actualizaron los protocolos de seguridad para el ingreso a las terminales. Se solicita a los pasajeros presentar DNI o pasaporte vigente y llegar con 2 horas de anticipación.',
 '2026-06-03', '2026-12-31'),

('Alerta por vientos en el NOA', 'alerta',
 'Fuertes vientos en la región del Noroeste Argentino podrían afectar operaciones en los aeropuertos de Salta, Jujuy y Tucumán. Consulte el estado de su vuelo antes de dirigirse al aeropuerto.',
 '2026-06-05', '2026-06-30'),

('Descuentos especiales para jubilados', 'informativa',
 'VuelaSeguro, en conjunto con las aerolíneas adheridas, ofrece un 15% de descuento adicional para pasajeros mayores de 60 años que presenten su credencial de jubilado al momento de la compra.',
 '2026-05-20', '2026-12-31');
