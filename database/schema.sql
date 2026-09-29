-- ============================================================
--  Sistema Osman - Estructura de la base de datos
--  U.P.Q.L  -  Unidad de Produccion de Quimica
--
--  Este archivo contiene SOLO la estructura (tablas, indices y
--  claves foraneas). No incluye ningun dato.
--
--  Uso:  mysql -u USUARIO -p < database/schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `osman_db`
  DEFAULT CHARACTER SET utf8mb3
  COLLATE utf8mb3_spanish_ci;

USE `osman_db`;

CREATE TABLE `detalle_entregas` (
  `id_detalle_entrega` int(11) NOT NULL AUTO_INCREMENT,
  `id_entrega` int(11) NOT NULL,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle_entrega`),
  KEY `id_entrega` (`id_entrega`),
  KEY `id_materia_prima` (`id_materia_prima`),
  CONSTRAINT `detalle_entregas_ibfk_1` FOREIGN KEY (`id_entrega`) REFERENCES `entregas` (`id_entrega`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_entregas_ibfk_2` FOREIGN KEY (`id_materia_prima`) REFERENCES `materias_primas` (`id_materia_prima`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `detalle_salidas` (
  `id_detalle_salida` int(11) NOT NULL AUTO_INCREMENT,
  `id_salida` int(11) NOT NULL,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad_utilizada` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle_salida`),
  KEY `id_salida` (`id_salida`),
  KEY `id_materia_prima` (`id_materia_prima`),
  CONSTRAINT `detalle_salidas_ibfk_1` FOREIGN KEY (`id_salida`) REFERENCES `salidas` (`id_salida`),
  CONSTRAINT `detalle_salidas_ibfk_2` FOREIGN KEY (`id_materia_prima`) REFERENCES `materias_primas` (`id_materia_prima`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `entregas` (
  `id_entrega` int(11) NOT NULL AUTO_INCREMENT,
  `id_proveedor` int(11) NOT NULL,
  `fecha_entrega` date NOT NULL,
  `numero_factura` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_entrega`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `inventario` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad_actual` decimal(10,2) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_inventario`),
  KEY `id_materia_prima` (`id_materia_prima`),
  CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`id_materia_prima`) REFERENCES `materias_primas` (`id_materia_prima`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `materias_primas` (
  `id_materia_prima` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(55) NOT NULL,
  `unidad_medida` varchar(10) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_materia_prima`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  `cantidad_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad_medida` varchar(20) DEFAULT 'unidad',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(55) NOT NULL,
  `rif` varchar(15) NOT NULL,
  `correo` varchar(35) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `recetas` (
  `id_receta` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_materia_prima` int(11) NOT NULL,
  `cantidad_necesaria` float NOT NULL,
  `instrucciones` text DEFAULT NULL,
  PRIMARY KEY (`id_receta`),
  KEY `id_producto` (`id_producto`),
  KEY `id_materia_prima` (`id_materia_prima`),
  CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`id_materia_prima`) REFERENCES `materias_primas` (`id_materia_prima`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `salidas` (
  `id_salida` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT NULL,
  `producto` varchar(100) NOT NULL,
  `fecha_salida` date NOT NULL,
  `cantidad_producto` float NOT NULL,
  `observaciones` varchar(33) NOT NULL,
  PRIMARY KEY (`id_salida`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `salidas_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(44) NOT NULL,
  `usuario` varchar(44) NOT NULL,
  `clave` varchar(44) NOT NULL,
  `pregunta` varchar(66) NOT NULL,
  `respuesta` varchar(66) NOT NULL,
  `tipo` varchar(33) NOT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

SET FOREIGN_KEY_CHECKS = 1;
