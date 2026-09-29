-- ============================================================
--  Sistema Osman - Datos de demostracion
--  U.P.Q.L  -  Unidad de Produccion de Quimica
--
--  IMPORTANTE
--  Todos los datos de este archivo son FICTICIOS y sirven unica-
--  mente para levantar el sistema en un entorno de pruebas.
--  Nunca lo ejecutes sobre la base de datos de produccion.
--
--  Usuario de prueba:
--      usuario : admin
--      clave   : osman2024
--
--  >> CAMBIA ESA CLAVE ANTES DE CUALQUIER USO REAL <<
--
--  Uso:  mysql -u USUARIO -p osman_db < database/seed_demo.sql
-- ============================================================

USE `osman_db`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `detalle_salidas`;
TRUNCATE TABLE `detalle_entregas`;
TRUNCATE TABLE `recetas`;
TRUNCATE TABLE `inventario`;
TRUNCATE TABLE `salidas`;
TRUNCATE TABLE `entregas`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `materias_primas`;
TRUNCATE TABLE `proveedores`;
TRUNCATE TABLE `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
--  Usuarios
--  El sistema valida las contrasenas con md5() (ver login.php)
-- ------------------------------------------------------------
INSERT INTO `usuarios` (`nombre`, `usuario`, `clave`, `pregunta`, `respuesta`, `tipo`) VALUES
('Administrador',  'admin',    '6bdf0c746aef544282d3a58dcaf76c9a', 'Ciudad de nacimiento', 'Ejemplo',  'admin'),
('Operador Demo', 'operador', '6bdf0c746aef544282d3a58dcaf76c9a', 'Color favorito',       'Ejemplo',  'operador');

-- ------------------------------------------------------------
--  Proveedores
-- ------------------------------------------------------------
INSERT INTO `proveedores` (`nombre`, `rif`, `correo`, `telefono`) VALUES
('Proveedor Demo Uno',   'J-00000000-1', 'contacto@demo-uno.example',   '04120000001'),
('Proveedor Demo Dos',   'J-00000000-2', 'ventas@demo-dos.example',     '04120000002'),
('Proveedor Demo Tres',  'J-00000000-3', 'info@demo-tres.example',      '04120000003');

-- ------------------------------------------------------------
--  Materias primas
-- ------------------------------------------------------------
INSERT INTO `materias_primas` (`nombre`, `unidad_medida`, `descripcion`) VALUES
('Materia prima de prueba A', 'kg',  'Materia ficticia para demostracion'),
('Materia prima de prueba B', 'L',   'Materia ficticia para demostracion'),
('Materia prima de prueba C', 'kg',  'Materia ficticia para demostracion'),
('Materia prima de prueba D', 'g',   'Materia ficticia para demostracion'),
('Materia prima de prueba E', 'und', 'Materia ficticia para demostracion');

-- ------------------------------------------------------------
--  Productos
-- ------------------------------------------------------------
INSERT INTO `productos` (`nombre`, `descripcion`, `precio_venta`, `cantidad_stock`, `unidad_medida`, `activo`) VALUES
('Producto Demo Alfa',  'Producto ficticio para demostracion', 0.00, 0.00, 'und', 1),
('Producto Demo Beta',  'Producto ficticio para demostracion', 0.00, 0.00, 'und', 1),
('Producto Demo Gamma', 'Producto ficticio para demostracion', 0.00, 0.00, 'und', 1);

-- ------------------------------------------------------------
--  Recetas
-- ------------------------------------------------------------
INSERT INTO `recetas` (`id_producto`, `id_materia_prima`, `cantidad_necesaria`, `instrucciones`) VALUES
(1, 1, 1.00, 'Instrucciones ficticias de demostracion'),
(1, 2, 0.50, 'Instrucciones ficticias de demostracion'),
(2, 3, 2.00, 'Instrucciones ficticias de demostracion'),
(3, 1, 1.50, 'Instrucciones ficticias de demostracion'),
(3, 4, 0.25, 'Instrucciones ficticias de demostracion');

-- ------------------------------------------------------------
--  Inventario inicial
-- ------------------------------------------------------------
INSERT INTO `inventario` (`id_materia_prima`, `cantidad_actual`) VALUES
(1, 100.00),
(2, 50.00),
(3, 75.00),
(4, 10.00),
(5, 20.00);
