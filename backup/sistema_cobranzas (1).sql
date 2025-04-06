-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-04-2025 a las 12:57:58
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_cobranzas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--

CREATE TABLE `chats` (
  `id` int NOT NULL,
  `reclamo_id` int NOT NULL,
  `emisor_id` int NOT NULL,
  `contenido` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_emisor` enum('administrador','cliente') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identificacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ubicacion_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `identificacion`, `direccion`, `ubicacion_link`, `telefono`, `email`, `created_at`, `imagen`, `password`) VALUES
(13, 'Carlos Rodríguez', '1255665', 'Av. Mcal. López 1234', NULL, '0961345678', 'carlos.rodríguez@gmail.com', '2025-03-17 14:15:41', 'default.png', NULL),
(14, 'María González', '1456789', 'Av. España 567', NULL, '0971456789', 'maría.gonzález@gmail.com', '2025-03-17 03:00:00', 'default.png', NULL),
(15, 'Juan Martínez', '1678901', 'Calle Palma 890', NULL, '0981567890', 'juan.martínez@gmail.com', '2025-03-17 23:47:32', 'default.png', NULL),
(16, 'Ana Sánchez', '1890123', 'Av. Artigas 123', NULL, '0991678901', 'ana.sánchez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(17, 'Pedro Gómez', '2345678', 'Calle Estrella 456', NULL, '0962789012', 'pedro.gómez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(18, 'Laura Fernández', '2567890', 'Av. Mariscal Estigarribia 789', NULL, '0972890123', 'laura.fernández@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(19, 'Miguel López', '2789012', 'Calle Independencia 012', NULL, '0982901234', 'miguel.lópez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(20, 'Sofía Díaz', '3012345', 'Av. Sacramento 345', NULL, '0992012345', 'sofía.díaz@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(21, 'José Pérez', '3234567', 'Calle Colón 678', NULL, '0963123456', 'josé.pérez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(22, 'Lucía Torres', '3456789', 'Av. Aviadores del Chaco 901', NULL, '0973234567', 'lucía.torres@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(23, 'Roberto Ramírez', '3678901', 'Calle Yegros 234', NULL, '0983345678', 'roberto.ramírez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(24, 'Valentina Flores', '4012345', 'Av. Santísima Trinidad 567', NULL, '0993456789', 'valentina.flores@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(25, 'Daniel Acosta', '4234567', 'Calle Iturbe 890', NULL, '0964567890', 'daniel.acosta@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(26, 'Camila Benítez', '4567890', 'Av. Félix Bogado 123', NULL, '0974678901', 'camila.benítez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(27, 'Alejandro Romero', '5678901', 'Calle 14 de Mayo 456', NULL, '0984789012', 'alejandro.romero@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(28, 'Valeria Medina', '6123456', 'Av. Bernardino Caballero 789', NULL, '0994890123', 'valeria.medina@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(29, 'Gabriel Rojas', '6789012', 'Calle Antequera 012', NULL, '0961901234', 'gabriel.rojas@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(30, 'Natalia Ortiz', '7123456', 'Av. General Santos 345', NULL, '0971012345', 'natalia.ortiz@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(31, 'Sebastián Giménez', '7890123', 'Calle Azara 678', NULL, '0981123456', 'sebastián.giménez@gmail.com', '2025-03-15 14:15:41', 'default.png', NULL),
(32, 'Isabella Núñez', '8456789', 'Av. Eusebio Ayala 901', NULL, '0991234567', 'isabella@gmail.com', '2025-03-15 14:15:41', '67d5b441a2250.png', '$2y$10$brQH3OaqebPGApU8D9HjxuX8X1rVn7paW8BAA.mSmNNIApefNwwu.'),
(33, 'Marcelo Ariel Benitez', '8426996', 'San Martin Casi Bolivar\r\n', 'https://maps.app.goo.gl/wXF5gWGD2DnNGXR8A', '0971631959', 'marceloariel722@gmail.com', '2025-03-15 14:41:59', '67d591b74dc66.png', '$2y$10$9f/axWlK2x6jnJT.0QezjuViITYt84Fz3JUOixAgzZmyinI8QbjXS'),
(34, 'Juan Pérez', '12345678', 'Calle 1, Asunción', 'https://maps.google.com/?q=-25.2637,-57.5759', '094086970', 'juan.pérez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(35, 'María López', '87654321', 'Avenida 2, Ciudad del Este', 'https://maps.google.com/?q=-25.5085,-54.6111', '099245273', 'maría.lópez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(36, 'Carlos Gómez', '45678912', 'Calle 3, Encarnación', 'https://maps.google.com/?q=-27.3306,-55.8667', '093965455', 'carlos.gómez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(37, 'Ana Martínez', '78912345', 'Avenida 4, Luque', 'https://maps.google.com/?q=-25.2753,-57.4845', '092091456', 'ana.martínez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(38, 'Pedro Rodríguez', '32165498', 'Calle 5, San Lorenzo', 'https://maps.google.com/?q=-25.3395,-57.5078', '098560918', 'pedro.rodríguez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(39, 'Laura Fernández', '98765432', 'Avenida 6, Fernando de la Mora', 'https://maps.google.com/?q=-25.3468,-57.5384', '096530223', 'laura.fernández@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(40, 'Luis González', '65432178', 'Calle 7, Lambaré', 'https://maps.google.com/?q=-25.3462,-57.6065', '096968362', 'luis.gonzález@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(41, 'Gabriela Díaz', '15975328', 'Avenida 8, Villa Elisa', 'https://maps.google.com/?q=-25.3645,-57.5669', '095251143', 'gabriela.díaz@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(42, 'Jorge Ramírez', '75395146', 'Calle 9, Mariano Roque Alonso', 'https://maps.google.com/?q=-25.1892,-57.5463', '095350629', 'jorge.ramírez@gmail.com', '2025-03-16 23:41:52', 'default.png', NULL),
(43, 'Natalia Castro', '85274169', 'Avenida 10, Ñemby', 'https://maps.google.com/?q=-25.3949,-57.5352', '09999716', 'natalia.castro@gmail.com', '2025-03-16 23:41:52', '67d765326ee9d.png', '$2y$10$TLodIibguAyGI/5iIK/uNuXRlFgOmy71vTX/xtcevfJUv3Av58acW');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas_deuda`
--

CREATE TABLE `cuotas_deuda` (
  `id` int NOT NULL,
  `deuda_id` int NOT NULL,
  `numero_cuota` int NOT NULL,
  `monto_cuota` decimal(12,2) NOT NULL,
  `interes_acumulado` decimal(12,2) DEFAULT '0.00',
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('pendiente','pagado','vencido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuotas_deuda`
--

INSERT INTO `cuotas_deuda` (`id`, `deuda_id`, `numero_cuota`, `monto_cuota`, `interes_acumulado`, `fecha_vencimiento`, `estado`, `created_at`) VALUES
(21, 7, 1, 2083333.33, 0.00, '2025-04-30', 'pagado', '2025-03-31 10:49:26'),
(22, 7, 2, 2083333.33, 0.00, '2025-05-31', 'pendiente', '2025-03-31 10:49:26'),
(23, 7, 3, 2083333.33, 0.00, '2025-06-30', 'pendiente', '2025-03-31 10:49:26'),
(24, 7, 4, 2083333.33, 0.00, '2025-07-31', 'pendiente', '2025-03-31 10:49:26'),
(25, 7, 5, 2083333.33, 0.00, '2025-08-31', 'pendiente', '2025-03-31 10:49:26'),
(26, 7, 6, 2083333.33, 0.00, '2025-09-30', 'pendiente', '2025-03-31 10:49:26'),
(27, 7, 7, 2083333.33, 0.00, '2025-10-31', 'pendiente', '2025-03-31 10:49:26'),
(28, 7, 8, 2083333.33, 0.00, '2025-11-30', 'pendiente', '2025-03-31 10:49:26'),
(29, 7, 9, 2083333.33, 0.00, '2025-12-31', 'pendiente', '2025-03-31 10:49:26'),
(30, 7, 10, 2083333.33, 0.00, '2026-01-31', 'pendiente', '2025-03-31 10:49:26'),
(31, 7, 11, 2083333.33, 0.00, '2026-02-28', 'pendiente', '2025-03-31 10:49:26'),
(32, 7, 12, 2083333.33, 0.00, '2026-03-31', 'pendiente', '2025-03-31 10:49:26'),
(33, 7, 13, 2083333.33, 0.00, '2026-04-30', 'pendiente', '2025-03-31 10:49:26'),
(34, 7, 14, 2083333.33, 0.00, '2026-05-31', 'pendiente', '2025-03-31 10:49:26'),
(35, 7, 15, 2083333.33, 0.00, '2026-06-30', 'pendiente', '2025-03-31 10:49:26'),
(36, 7, 16, 2083333.33, 0.00, '2026-07-31', 'pendiente', '2025-03-31 10:49:26'),
(37, 7, 17, 2083333.33, 0.00, '2026-08-31', 'pendiente', '2025-03-31 10:49:26'),
(38, 7, 18, 2083333.33, 0.00, '2026-09-30', 'pendiente', '2025-03-31 10:49:26'),
(39, 7, 19, 2083333.33, 0.00, '2026-10-31', 'pendiente', '2025-03-31 10:49:26'),
(40, 7, 20, 2083333.33, 0.00, '2026-11-30', 'pendiente', '2025-03-31 10:49:26'),
(41, 7, 21, 2083333.33, 0.00, '2026-12-31', 'pendiente', '2025-03-31 10:49:26'),
(42, 7, 22, 2083333.33, 0.00, '2027-01-31', 'pendiente', '2025-03-31 10:49:26'),
(43, 7, 23, 2083333.33, 0.00, '2027-02-28', 'pendiente', '2025-03-31 10:49:26'),
(44, 7, 24, 2083333.33, 0.00, '2027-03-31', 'pendiente', '2025-03-31 10:49:26'),
(45, 7, 1, 2083333.33, 0.00, '2025-05-30', 'pendiente', '2025-03-31 10:49:26'),
(46, 7, 2, 2083333.33, 0.00, '2025-05-30', 'pendiente', '2025-03-31 10:49:26'),
(47, 7, 3, 2083333.33, 0.00, '2025-07-30', 'pendiente', '2025-03-31 10:49:26'),
(48, 7, 4, 2083333.33, 0.00, '2025-07-30', 'pendiente', '2025-03-31 10:49:26'),
(49, 7, 5, 2083333.33, 0.00, '2025-08-30', 'pendiente', '2025-03-31 10:49:26'),
(50, 7, 6, 2083333.33, 0.00, '2025-10-30', 'pendiente', '2025-03-31 10:49:26'),
(51, 7, 7, 2083333.33, 0.00, '2025-10-30', 'pendiente', '2025-03-31 10:49:26'),
(52, 7, 8, 2083333.33, 0.00, '2025-12-30', 'pendiente', '2025-03-31 10:49:26'),
(53, 7, 9, 2083333.33, 0.00, '2025-12-30', 'pendiente', '2025-03-31 10:49:26'),
(54, 7, 10, 2083333.33, 0.00, '2026-01-30', 'pendiente', '2025-03-31 10:49:26'),
(55, 7, 11, 2083333.33, 0.00, '2026-03-30', 'pendiente', '2025-03-31 10:49:26'),
(56, 7, 12, 2083333.33, 0.00, '2026-03-30', 'pendiente', '2025-03-31 10:49:26'),
(57, 7, 13, 2083333.33, 0.00, '2026-05-30', 'pendiente', '2025-03-31 10:49:26'),
(58, 7, 14, 2083333.33, 0.00, '2026-05-30', 'pendiente', '2025-03-31 10:49:26'),
(59, 7, 15, 2083333.33, 0.00, '2026-07-30', 'pendiente', '2025-03-31 10:49:26'),
(60, 7, 16, 2083333.33, 0.00, '2026-07-30', 'pendiente', '2025-03-31 10:49:26'),
(61, 7, 17, 2083333.33, 0.00, '2026-08-30', 'pendiente', '2025-03-31 10:49:26'),
(62, 7, 18, 2083333.33, 0.00, '2026-10-30', 'pendiente', '2025-03-31 10:49:26'),
(63, 7, 19, 2083333.33, 0.00, '2026-10-30', 'pendiente', '2025-03-31 10:49:26'),
(64, 7, 20, 2083333.33, 0.00, '2026-12-30', 'pendiente', '2025-03-31 10:49:26'),
(65, 7, 21, 2083333.33, 0.00, '2026-12-30', 'pendiente', '2025-03-31 10:49:26'),
(66, 7, 22, 2083333.33, 0.00, '2027-01-30', 'pendiente', '2025-03-31 10:49:26'),
(67, 7, 23, 2083333.33, 0.00, '2027-03-30', 'pendiente', '2025-03-31 10:49:26'),
(68, 7, 24, 2083333.33, 0.00, '2027-03-30', 'pendiente', '2025-03-31 10:49:26'),
(69, 8, 1, 5000000.00, 0.00, '2025-05-01', 'pendiente', '2025-04-01 17:31:44'),
(70, 8, 2, 5000000.00, 0.00, '2025-06-01', 'pendiente', '2025-04-01 17:31:44'),
(71, 8, 3, 5000000.00, 0.00, '2025-07-01', 'pendiente', '2025-04-01 17:31:44'),
(72, 8, 4, 5000000.00, 0.00, '2025-08-01', 'pendiente', '2025-04-01 17:31:44'),
(73, 8, 5, 5000000.00, 0.00, '2025-09-01', 'pendiente', '2025-04-01 17:31:44'),
(74, 8, 6, 5000000.00, 0.00, '2025-10-01', 'pendiente', '2025-04-01 17:31:44'),
(75, 8, 7, 5000000.00, 0.00, '2025-11-01', 'pendiente', '2025-04-01 17:31:44'),
(76, 8, 8, 5000000.00, 0.00, '2025-12-01', 'pendiente', '2025-04-01 17:31:44'),
(77, 8, 9, 5000000.00, 0.00, '2026-01-01', 'pendiente', '2025-04-01 17:31:44'),
(78, 8, 10, 5000000.00, 0.00, '2026-02-01', 'pendiente', '2025-04-01 17:31:44'),
(79, 8, 1, 5000000.00, 0.00, '2025-05-30', 'pendiente', '2025-04-01 17:31:44'),
(80, 8, 2, 5000000.00, 0.00, '2025-06-30', 'pendiente', '2025-04-01 17:31:44'),
(81, 8, 3, 5000000.00, 0.00, '2025-07-30', 'pendiente', '2025-04-01 17:31:44'),
(82, 8, 4, 5000000.00, 0.00, '2025-08-30', 'pendiente', '2025-04-01 17:31:44'),
(83, 8, 5, 5000000.00, 0.00, '2025-09-30', 'pendiente', '2025-04-01 17:31:44'),
(84, 8, 6, 5000000.00, 0.00, '2025-10-30', 'pendiente', '2025-04-01 17:31:44'),
(85, 8, 7, 5000000.00, 0.00, '2025-11-30', 'pendiente', '2025-04-01 17:31:44'),
(86, 8, 8, 5000000.00, 0.00, '2025-12-30', 'pendiente', '2025-04-01 17:31:44'),
(87, 8, 9, 5000000.00, 0.00, '2026-01-30', 'pendiente', '2025-04-01 17:31:44'),
(88, 8, 10, 5000000.00, 0.00, '2026-03-31', 'pendiente', '2025-04-01 17:31:44'),
(89, 9, 1, 2291666.67, 0.00, '2025-05-02', 'pagado', '2025-04-02 13:25:38'),
(90, 9, 2, 2291666.67, 0.00, '2025-06-02', 'pendiente', '2025-04-02 13:25:38'),
(91, 9, 3, 2291666.67, 0.00, '2025-07-02', 'pendiente', '2025-04-02 13:25:38'),
(92, 9, 4, 2291666.67, 0.00, '2025-08-02', 'pendiente', '2025-04-02 13:25:38'),
(93, 9, 5, 2291666.67, 0.00, '2025-09-02', 'pendiente', '2025-04-02 13:25:38'),
(94, 9, 6, 2291666.67, 0.00, '2025-10-02', 'pendiente', '2025-04-02 13:25:38'),
(95, 9, 7, 2291666.67, 0.00, '2025-11-02', 'pendiente', '2025-04-02 13:25:38'),
(96, 9, 8, 2291666.67, 0.00, '2025-12-02', 'pendiente', '2025-04-02 13:25:38'),
(97, 9, 9, 2291666.67, 0.00, '2026-01-02', 'pendiente', '2025-04-02 13:25:38'),
(98, 9, 10, 2291666.67, 0.00, '2026-02-02', 'pendiente', '2025-04-02 13:25:38'),
(99, 9, 11, 2291666.67, 0.00, '2026-03-02', 'pendiente', '2025-04-02 13:25:38'),
(100, 9, 12, 2291666.67, 0.00, '2026-04-02', 'pendiente', '2025-04-02 13:25:38'),
(101, 9, 13, 2291666.67, 0.00, '2026-05-02', 'pendiente', '2025-04-02 13:25:38'),
(102, 9, 14, 2291666.67, 0.00, '2026-06-02', 'pendiente', '2025-04-02 13:25:38'),
(103, 9, 15, 2291666.67, 0.00, '2026-07-02', 'pendiente', '2025-04-02 13:25:38'),
(104, 9, 16, 2291666.67, 0.00, '2026-08-02', 'pendiente', '2025-04-02 13:25:38'),
(105, 9, 17, 2291666.67, 0.00, '2026-09-02', 'pendiente', '2025-04-02 13:25:38'),
(106, 9, 18, 2291666.67, 0.00, '2026-10-02', 'pendiente', '2025-04-02 13:25:38'),
(107, 9, 19, 2291666.67, 0.00, '2026-11-02', 'pendiente', '2025-04-02 13:25:38'),
(108, 9, 20, 2291666.67, 0.00, '2026-12-02', 'pendiente', '2025-04-02 13:25:38'),
(109, 9, 21, 2291666.67, 0.00, '2027-01-02', 'pendiente', '2025-04-02 13:25:38'),
(110, 9, 22, 2291666.67, 0.00, '2027-02-02', 'pendiente', '2025-04-02 13:25:38'),
(111, 9, 23, 2291666.67, 0.00, '2027-03-02', 'pendiente', '2025-04-02 13:25:38'),
(112, 9, 24, 2291666.67, 0.00, '2027-04-02', 'pendiente', '2025-04-02 13:25:38'),
(113, 9, 1, 2291666.67, 0.00, '2025-05-30', 'pagado', '2025-04-02 13:25:38'),
(114, 9, 2, 2291666.67, 0.00, '2025-06-30', 'pendiente', '2025-04-02 13:25:38'),
(115, 9, 3, 2291666.67, 0.00, '2025-07-30', 'pendiente', '2025-04-02 13:25:38'),
(116, 9, 4, 2291666.67, 0.00, '2025-08-30', 'pendiente', '2025-04-02 13:25:38'),
(117, 9, 5, 2291666.67, 0.00, '2025-09-30', 'pendiente', '2025-04-02 13:25:38'),
(118, 9, 6, 2291666.67, 0.00, '2025-10-30', 'pendiente', '2025-04-02 13:25:38'),
(119, 9, 7, 2291666.67, 0.00, '2025-11-30', 'pendiente', '2025-04-02 13:25:38'),
(120, 9, 8, 2291666.67, 0.00, '2025-12-30', 'pendiente', '2025-04-02 13:25:38'),
(121, 9, 9, 2291666.67, 0.00, '2026-01-30', 'pendiente', '2025-04-02 13:25:38'),
(122, 9, 10, 2291666.67, 0.00, '2026-03-31', 'pendiente', '2025-04-02 13:25:38'),
(123, 9, 11, 2291666.67, 0.00, '2026-03-30', 'pendiente', '2025-04-02 13:25:38'),
(124, 9, 12, 2291666.67, 0.00, '2026-04-30', 'pendiente', '2025-04-02 13:25:38'),
(125, 9, 13, 2291666.67, 0.00, '2026-05-30', 'pendiente', '2025-04-02 13:25:38'),
(126, 9, 14, 2291666.67, 0.00, '2026-06-30', 'pendiente', '2025-04-02 13:25:38'),
(127, 9, 15, 2291666.67, 0.00, '2026-07-30', 'pendiente', '2025-04-02 13:25:38'),
(128, 9, 16, 2291666.67, 0.00, '2026-08-30', 'pendiente', '2025-04-02 13:25:38'),
(129, 9, 17, 2291666.67, 0.00, '2026-09-30', 'pendiente', '2025-04-02 13:25:38'),
(130, 9, 18, 2291666.67, 0.00, '2026-10-30', 'pendiente', '2025-04-02 13:25:38'),
(131, 9, 19, 2291666.67, 0.00, '2026-11-30', 'pendiente', '2025-04-02 13:25:38'),
(132, 9, 20, 2291666.67, 0.00, '2026-12-30', 'pendiente', '2025-04-02 13:25:38'),
(133, 9, 21, 2291666.67, 0.00, '2027-01-30', 'pendiente', '2025-04-02 13:25:38'),
(134, 9, 22, 2291666.67, 0.00, '2027-03-31', 'pendiente', '2025-04-02 13:25:38'),
(135, 9, 23, 2291666.67, 0.00, '2027-03-30', 'pendiente', '2025-04-02 13:25:38'),
(136, 9, 24, 2291666.67, 0.00, '2027-04-30', 'pendiente', '2025-04-02 13:25:38'),
(137, 10, 1, 1000000.00, 0.00, '2025-05-04', 'pendiente', '2025-04-04 00:49:01'),
(138, 10, 2, 1000000.00, 0.00, '2025-06-04', 'pendiente', '2025-04-04 00:49:01'),
(139, 10, 3, 1000000.00, 0.00, '2025-07-04', 'pendiente', '2025-04-04 00:49:01'),
(140, 10, 4, 1000000.00, 0.00, '2025-08-04', 'pendiente', '2025-04-04 00:49:01'),
(141, 10, 5, 1000000.00, 0.00, '2025-09-04', 'pendiente', '2025-04-04 00:49:01'),
(142, 10, 6, 1000000.00, 0.00, '2025-10-04', 'pendiente', '2025-04-04 00:49:01'),
(143, 10, 7, 1000000.00, 0.00, '2025-11-04', 'pendiente', '2025-04-04 00:49:01'),
(144, 10, 8, 1000000.00, 0.00, '2025-12-04', 'pendiente', '2025-04-04 00:49:01'),
(145, 10, 9, 1000000.00, 0.00, '2026-01-04', 'pendiente', '2025-04-04 00:49:01'),
(146, 10, 10, 1000000.00, 0.00, '2026-02-04', 'pendiente', '2025-04-04 00:49:01'),
(147, 10, 1, 1000000.00, 0.00, '2025-05-30', 'pendiente', '2025-04-04 00:49:01'),
(148, 10, 2, 1000000.00, 0.00, '2025-06-30', 'pendiente', '2025-04-04 00:49:01'),
(149, 10, 3, 1000000.00, 0.00, '2025-07-30', 'pendiente', '2025-04-04 00:49:01'),
(150, 10, 4, 1000000.00, 0.00, '2025-08-30', 'pendiente', '2025-04-04 00:49:01'),
(151, 10, 5, 1000000.00, 0.00, '2025-09-30', 'pendiente', '2025-04-04 00:49:01'),
(152, 10, 6, 1000000.00, 0.00, '2025-10-30', 'pendiente', '2025-04-04 00:49:01'),
(153, 10, 7, 1000000.00, 0.00, '2025-11-30', 'pendiente', '2025-04-04 00:49:01'),
(154, 10, 8, 1000000.00, 0.00, '2025-12-30', 'pendiente', '2025-04-04 00:49:01'),
(155, 10, 9, 1000000.00, 0.00, '2026-01-30', 'pendiente', '2025-04-04 00:49:01'),
(156, 10, 10, 1000000.00, 0.00, '2026-03-31', 'pendiente', '2025-04-04 00:49:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pago`
--

CREATE TABLE `detalles_pago` (
  `id` int NOT NULL,
  `pago_id` int NOT NULL,
  `tipo_detalle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_detalle` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

CREATE TABLE `deudas` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `politica_interes_id` int NOT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `monto` decimal(12,2) NOT NULL,
  `cuotas` int NOT NULL DEFAULT '1',
  `fecha_emision` date NOT NULL,
  `saldo_pendiente` decimal(12,2) NOT NULL,
  `interes_acumulado` decimal(12,2) DEFAULT '0.00',
  `ultima_actualizacion_interes` date DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('pendiente','pagado','vencido','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `monto_cuota` decimal(12,2) GENERATED ALWAYS AS ((`monto` / `cuotas`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id`, `cliente_id`, `politica_interes_id`, `notas`, `monto`, `cuotas`, `fecha_emision`, `saldo_pendiente`, `interes_acumulado`, `ultima_actualizacion_interes`, `descripcion`, `fecha_vencimiento`, `estado`, `created_at`) VALUES
(5, 32, 3, '', 41000000.00, 1, '2025-03-16', 41000000.00, 0.00, NULL, 'CrediAgil San Lorenzo', '2026-04-15', 'pendiente', '2025-03-16 23:13:03'),
(7, 41, 1, '', 50000000.00, 24, '2025-03-31', 47900000.00, 0.00, NULL, 'CrediAmigo', '2027-03-31', 'pendiente', '2025-03-31 10:49:26'),
(8, 42, 3, '', 50000000.00, 10, '2025-04-01', 50000000.00, 0.00, NULL, 'CrediAmigo', '2026-02-01', 'pendiente', '2025-04-01 17:31:44'),
(9, 43, 2, '', 55000000.00, 24, '2025-04-02', 50000000.00, 0.00, NULL, 'CrediAmigo', '2027-04-02', 'pendiente', '2025-04-02 13:25:38'),
(10, 33, 1, '', 10000000.00, 10, '2025-04-04', 10000000.00, 0.00, NULL, 'CrediAmigo', '2026-02-04', 'pendiente', '2025-04-04 00:49:01');

--
-- Disparadores `deudas`
--
DELIMITER $$
CREATE TRIGGER `after_deuda_insert` AFTER INSERT ON `deudas` FOR EACH ROW BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE fecha_venc DATE;
    
    WHILE i <= NEW.cuotas DO
        -- Calculate the due date for each installment (monthly)
        SET fecha_venc = DATE_ADD(NEW.fecha_emision, INTERVAL i MONTH);
        
        -- Insert the installment record
        INSERT INTO `cuotas_deuda` (
            `deuda_id`, 
            `numero_cuota`, 
            `monto_cuota`, 
            `fecha_vencimiento`
        ) VALUES (
            NEW.id,
            i,
            NEW.monto / NEW.cuotas,
            fecha_venc
        );
        
        SET i = i + 1;
    END WHILE;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `deuda_id` int DEFAULT NULL,
  `tipo_documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruta_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_deudas`
--

CREATE TABLE `historial_deudas` (
  `id` int NOT NULL,
  `deuda_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `accion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_deudas`
--

INSERT INTO `historial_deudas` (`id`, `deuda_id`, `usuario_id`, `accion`, `detalle`, `created_at`) VALUES
(5, 5, 6, 'creación', 'Creación de nueva deuda por monto 41.000.000,00 Gs.', '2025-03-16 23:13:03'),
(15, 7, 6, 'creación', 'Creación de nueva deuda por monto 50.000.000,00 Gs.', '2025-03-31 10:49:26'),
(16, 7, 17, 'pago', 'Pago de 2.100.000 Gs. mediante Transferencia', '2025-03-31 10:50:45'),
(17, 8, 17, 'creación', 'Creación de nueva deuda por monto 50.000.000,00 Gs.', '2025-04-01 17:31:44'),
(18, 9, 6, 'creación', 'Creación de nueva deuda por monto 55.000.000,00 Gs.', '2025-04-02 13:25:38'),
(19, 9, 17, 'pago', 'Pago de 5.000.000 Gs. mediante Tarjeta', '2025-04-03 23:19:50'),
(20, 10, 6, 'creación', 'Creación de nueva deuda por monto 10.000.000,00 Gs.', '2025-04-04 00:49:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `tipo` enum('email','sms','app') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','enviado','fallido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_envio` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `deuda_id` int NOT NULL,
  `monto_pagado` decimal(12,2) NOT NULL,
  `metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_anulado` tinyint(1) DEFAULT '0',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `cuota_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `deuda_id`, `monto_pagado`, `metodo_pago`, `fecha_pago`, `comprobante`, `created_at`, `is_anulado`, `estado`, `cuota_id`) VALUES
(8, 7, 2100000.00, 'Transferencia', '2025-03-31', NULL, '2025-03-31 10:50:45', 0, 'pendiente', NULL),
(9, 9, 5000000.00, 'Tarjeta', '2025-04-03', NULL, '2025-04-03 23:19:50', 0, 'pendiente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `politicas_interes`
--

CREATE TABLE `politicas_interes` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('simple','compuesto','escalonado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `periodo` enum('diario','mensual','anual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensual',
  `tasa` decimal(5,2) NOT NULL,
  `tasa_escalonada_json` json DEFAULT NULL,
  `penalizacion_fija` decimal(12,2) DEFAULT NULL,
  `dias_penalizacion` int DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `politicas_interes`
--

INSERT INTO `politicas_interes` (`id`, `nombre`, `tipo`, `periodo`, `tasa`, `tasa_escalonada_json`, `penalizacion_fija`, `dias_penalizacion`, `fecha_inicio`, `fecha_fin`, `activa`, `created_at`) VALUES
(1, 'Interés Simple Diario', 'simple', 'diario', 0.10, NULL, NULL, NULL, '2025-03-22', NULL, 1, '2025-03-22 17:22:33'),
(2, 'Interés Compuesto Diario', 'compuesto', 'diario', 0.05, NULL, NULL, NULL, '2025-03-22', NULL, 1, '2025-03-22 17:22:33'),
(3, 'Interés Moratorio Escalonado', 'escalonado', 'diario', 0.08, '[{\"tasa\": 0.08, \"dias_desde\": 1, \"dias_hasta\": 5}, {\"tasa\": 0.15, \"dias_desde\": 6, \"dias_hasta\": 10}, {\"tasa\": 0.2, \"dias_desde\": 11, \"dias_hasta\": null}]', 10000.00, 5, '2025-03-22', NULL, 1, '2025-03-22 17:22:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reclamos`
--

CREATE TABLE `reclamos` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `deuda_id` int DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('abierto','en_proceso','resuelto','cerrado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'abierto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `respuesta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `respondido_por` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `permisos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `permisos`, `created_at`) VALUES
(1, 'Administrador', '{\n    \"dashboard\": true,\n    \"gestion_usuarios\": true,\n    \"gestion_clientes\": true,\n    \"gestion_deudas\": true,\n    \"gestion_pagos\": true,\n    \"gestion_reclamos\": true,\n    \"configuracion\": true,\n    \"reportes\": true\n}', '2025-02-17 02:47:11'),
(2, 'Gestor de Cobranzas', '{\r\n    \"dashboard\": true,\r\n    \"gestion_clientes\": true,\r\n    \"gestion_deudas\": true,\r\n    \"gestion_pagos\": true,\r\n    \"gestion_reclamos\": true,\r\n    \"reportes\": true\r\n}', '2025-02-17 02:47:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `rol_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png',
  `last_activity` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password`, `activo`, `created_at`, `imagen`, `last_activity`) VALUES
(6, 1, 'Marcelo', 'marcelo@gmail.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-02-17 03:37:57', 'usuario_6_1741560443.jpg', '2025-03-31 11:08:20'),
(9, 2, 'Gestor', 'gestor.php@dominio.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-03-01 05:13:21', 'usuario_9_1741560308.png', '2025-03-15 22:12:53'),
(17, 1, 'Administrador', 'Admin@gmail.com', '$2y$10$5C4ffiSK9KiYJXoSVK9F0edURthp0iWYNJ9tVUCXKD7tpRsO9sb3G', 1, '2025-03-10 01:12:00', 'cd9f1fc8fcbf5d15eb8b4809567c6294.png', '2025-03-15 22:12:53'),
(20, 1, 'Daniel Ascurra', 'daniel@gmail.com', '$2y$10$a6kFkUMzJTgRPHHmW5Qe5.XxfC3l7cDAOxeV5QxQwn.PEG8YTnhza', 1, '2025-04-01 16:25:31', 'ed07cdc4705ab35c856e7e911809fe4b.jpg', '2025-04-01 13:25:31');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_deudas_con_interes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_deudas_con_interes` (
`id` int
,`cliente_id` int
,`cliente_nombre` varchar(200)
,`monto` decimal(12,2)
,`saldo_pendiente` decimal(12,2)
,`interes_acumulado` decimal(12,2)
,`total_a_pagar` decimal(13,2)
,`fecha_emision` date
,`fecha_vencimiento` date
,`estado` enum('pendiente','pagado','vencido','cancelado')
,`politica_nombre` varchar(100)
,`tipo_interes` enum('simple','compuesto','escalonado')
,`tasa` decimal(5,2)
,`ultima_actualizacion_interes` date
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reclamo_id` (`reclamo_id`),
  ADD KEY `emisor_id` (`emisor_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`);

--
-- Indices de la tabla `cuotas_deuda`
--
ALTER TABLE `cuotas_deuda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cuotas_deuda` (`deuda_id`);

--
-- Indices de la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pago_id` (`pago_id`);

--
-- Indices de la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deudas_cliente` (`cliente_id`),
  ADD KEY `idx_deudas_politica` (`politica_interes_id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `deuda_id` (`deuda_id`);

--
-- Indices de la tabla `historial_deudas`
--
ALTER TABLE `historial_deudas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deuda_id` (`deuda_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagos_deuda` (`deuda_id`),
  ADD KEY `fk_pagos_cuota` (`cuota_id`);

--
-- Indices de la tabla `politicas_interes`
--
ALTER TABLE `politicas_interes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reclamos`
--
ALTER TABLE `reclamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deuda_id` (`deuda_id`),
  ADD KEY `idx_reclamos_cliente` (`cliente_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `cuotas_deuda`
--
ALTER TABLE `cuotas_deuda`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT de la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_deudas`
--
ALTER TABLE `historial_deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `politicas_interes`
--
ALTER TABLE `politicas_interes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reclamos`
--
ALTER TABLE `reclamos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_deudas_con_interes`
--
DROP TABLE IF EXISTS `v_deudas_con_interes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_deudas_con_interes`  AS SELECT `d`.`id` AS `id`, `d`.`cliente_id` AS `cliente_id`, `c`.`nombre` AS `cliente_nombre`, `d`.`monto` AS `monto`, `d`.`saldo_pendiente` AS `saldo_pendiente`, `d`.`interes_acumulado` AS `interes_acumulado`, (`d`.`saldo_pendiente` + `d`.`interes_acumulado`) AS `total_a_pagar`, `d`.`fecha_emision` AS `fecha_emision`, `d`.`fecha_vencimiento` AS `fecha_vencimiento`, `d`.`estado` AS `estado`, `p`.`nombre` AS `politica_nombre`, `p`.`tipo` AS `tipo_interes`, `p`.`tasa` AS `tasa`, `d`.`ultima_actualizacion_interes` AS `ultima_actualizacion_interes` FROM ((`deudas` `d` join `clientes` `c` on((`d`.`cliente_id` = `c`.`id`))) join `politicas_interes` `p` on((`d`.`politica_interes_id` = `p`.`id`))) ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`reclamo_id`) REFERENCES `reclamos` (`id`),
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `cuotas_deuda`
--
ALTER TABLE `cuotas_deuda`
  ADD CONSTRAINT `fk_cuotas_deuda` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  ADD CONSTRAINT `detalles_pago_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD CONSTRAINT `deudas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deudas_politicas` FOREIGN KEY (`politica_interes_id`) REFERENCES `politicas_interes` (`id`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `historial_deudas`
--
ALTER TABLE `historial_deudas`
  ADD CONSTRAINT `historial_deudas_ibfk_1` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_deudas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_cuota` FOREIGN KEY (`cuota_id`) REFERENCES `cuotas_deuda` (`id`),
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `calcular_interes_diario` ON SCHEDULE EVERY 1 DAY STARTS '2025-03-22 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Variables for interest calculation
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_deuda_id, v_politica_id, v_cuota_id INT;
    DECLARE v_monto, v_saldo, v_tasa, v_interes DECIMAL(12,2);
    DECLARE v_fecha_venc, v_hoy DATE;
    DECLARE v_tipo_politica VARCHAR(20);
    DECLARE v_periodo VARCHAR(20);
    DECLARE v_dias_atraso INT;
    DECLARE v_tasa_escalonada JSON;
    DECLARE v_tasa_aplicar DECIMAL(5,2);
    
    -- Cursor for overdue debts
    DECLARE cur_deudas CURSOR FOR 
        SELECT d.id, d.politica_interes_id, d.monto, d.saldo_pendiente, 
               p.tipo, p.periodo, p.tasa, p.tasa_escalonada_json
        FROM deudas d
        JOIN politicas_interes p ON d.politica_interes_id = p.id
        WHERE d.estado = 'pendiente' 
        AND d.fecha_vencimiento < CURDATE()
        AND d.saldo_pendiente > 0;
    
    -- Cursor for overdue installments
    DECLARE cur_cuotas CURSOR FOR 
        SELECT c.id, c.deuda_id, c.monto_cuota, d.politica_interes_id,
               c.fecha_vencimiento, p.tipo, p.periodo, p.tasa, p.tasa_escalonada_json
        FROM cuotas_deuda c
        JOIN deudas d ON c.deuda_id = d.id
        JOIN politicas_interes p ON d.politica_interes_id = p.id
        WHERE c.estado = 'pendiente' 
        AND c.fecha_vencimiento < CURDATE();
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET v_hoy = CURDATE();
    
    -- Process overdue debts
    OPEN cur_deudas;
    
    read_loop: LOOP
        FETCH cur_deudas INTO v_deuda_id, v_politica_id, v_monto, v_saldo, 
                             v_tipo_politica, v_periodo, v_tasa, v_tasa_escalonada;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Update debt status to 'vencido' if not already
        UPDATE deudas SET estado = 'vencido' 
        WHERE id = v_deuda_id AND estado = 'pendiente';
        
        -- Calculate interest based on policy type
        IF v_tipo_politica = 'simple' THEN
            -- Simple interest calculation
            IF v_periodo = 'diario' THEN
                SET v_interes = v_saldo * (v_tasa / 100);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_saldo * (v_tasa / 100 / 30);
            ELSE -- annual
                SET v_interes = v_saldo * (v_tasa / 100 / 365);
            END IF;
            
        ELSEIF v_tipo_politica = 'compuesto' THEN
            -- Compound interest calculation
            IF v_periodo = 'diario' THEN
                SET v_interes = v_saldo * (POW(1 + (v_tasa / 100), 1) - 1);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_saldo * (POW(1 + (v_tasa / 100), 1/30) - 1);
            ELSE -- annual
                SET v_interes = v_saldo * (POW(1 + (v_tasa / 100), 1/365) - 1);
            END IF;
            
        ELSEIF v_tipo_politica = 'escalonado' THEN
            -- Escalated interest calculation
            -- Calculate days overdue
            SELECT DATEDIFF(v_hoy, fecha_vencimiento) INTO v_dias_atraso
            FROM deudas WHERE id = v_deuda_id;
            
            -- Find applicable rate from escalated rates
            SET v_tasa_aplicar = v_tasa; -- Default rate
            
            -- Parse JSON and find applicable rate
            IF v_tasa_escalonada IS NOT NULL THEN
                -- Logic to parse JSON and find applicable rate based on days overdue
                -- This is a simplified version
                SELECT tasa INTO v_tasa_aplicar
                FROM JSON_TABLE(
                    v_tasa_escalonada,
                    '$[*]' COLUMNS(
                        dias_desde INT PATH '$.dias_desde',
                        dias_hasta INT PATH '$.dias_hasta',
                        tasa DECIMAL(5,2) PATH '$.tasa'
                    )
                ) AS jt
                WHERE v_dias_atraso >= dias_desde
                AND (dias_hasta IS NULL OR v_dias_atraso <= dias_hasta)
                ORDER BY dias_desde DESC
                LIMIT 1;
            END IF;
            
            -- Calculate interest with the applicable rate
            IF v_periodo = 'diario' THEN
                SET v_interes = v_saldo * (v_tasa_aplicar / 100);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_saldo * (v_tasa_aplicar / 100 / 30);
            ELSE -- annual
                SET v_interes = v_saldo * (v_tasa_aplicar / 100 / 365);
            END IF;
        END IF;
        
        -- Update accumulated interest
        UPDATE deudas 
        SET interes_acumulado = interes_acumulado + v_interes,
            ultima_actualizacion_interes = v_hoy
        WHERE id = v_deuda_id;
        
        -- Log interest calculation in history
        INSERT INTO historial_deudas (deuda_id, usuario_id, accion, detalle)
        VALUES (v_deuda_id, 1, 'calculo_interes', 
                CONCAT('Interés calculado: ', ROUND(v_interes, 2), ' Gs. Tipo: ', v_tipo_politica));
    END LOOP;
    
    CLOSE cur_deudas;
    
    -- Reset done flag
    SET done = FALSE;
    
    -- Process overdue installments
    OPEN cur_cuotas;
    
    cuotas_loop: LOOP
        FETCH cur_cuotas INTO v_cuota_id, v_deuda_id, v_monto, v_politica_id,
                              v_fecha_venc, v_tipo_politica, v_periodo, v_tasa, v_tasa_escalonada;
        
        IF done THEN
            LEAVE cuotas_loop;
        END IF;
        
        -- Update installment status to 'vencido' if not already
        UPDATE cuotas_deuda SET estado = 'vencido' 
        WHERE id = v_cuota_id AND estado = 'pendiente';
        
        -- Calculate days overdue
        SET v_dias_atraso = DATEDIFF(v_hoy, v_fecha_venc);
        
        -- Calculate interest based on policy type (similar logic as above)
        IF v_tipo_politica = 'simple' THEN
            -- Simple interest calculation for installment
            IF v_periodo = 'diario' THEN
                SET v_interes = v_monto * (v_tasa / 100);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_monto * (v_tasa / 100 / 30);
            ELSE -- annual
                SET v_interes = v_monto * (v_tasa / 100 / 365);
            END IF;
            
        ELSEIF v_tipo_politica = 'compuesto' THEN
            -- Compound interest calculation for installment
            IF v_periodo = 'diario' THEN
                SET v_interes = v_monto * (POW(1 + (v_tasa / 100), 1) - 1);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_monto * (POW(1 + (v_tasa / 100), 1/30) - 1);
            ELSE -- annual
                SET v_interes = v_monto * (POW(1 + (v_tasa / 100), 1/365) - 1);
            END IF;
            
        ELSEIF v_tipo_politica = 'escalonado' THEN
            -- Find applicable rate from escalated rates
            SET v_tasa_aplicar = v_tasa; -- Default rate
            
            -- Parse JSON and find applicable rate
            IF v_tasa_escalonada IS NOT NULL THEN
                SELECT tasa INTO v_tasa_aplicar
                FROM JSON_TABLE(
                    v_tasa_escalonada,
                    '$[*]' COLUMNS(
                        dias_desde INT PATH '$.dias_desde',
                        dias_hasta INT PATH '$.dias_hasta',
                        tasa DECIMAL(5,2) PATH '$.tasa'
                    )
                ) AS jt
                WHERE v_dias_atraso >= dias_desde
                AND (dias_hasta IS NULL OR v_dias_atraso <= dias_hasta)
                ORDER BY dias_desde DESC
                LIMIT 1;
            END IF;
            
            -- Calculate interest with the applicable rate
            IF v_periodo = 'diario' THEN
                SET v_interes = v_monto * (v_tasa_aplicar / 100);
            ELSEIF v_periodo = 'mensual' THEN
                SET v_interes = v_monto * (v_tasa_aplicar / 100 / 30);
            ELSE -- annual
                SET v_interes = v_monto * (v_tasa_aplicar / 100 / 365);
            END IF;
        END IF;
        
        -- Update accumulated interest for the installment
        UPDATE cuotas_deuda 
        SET interes_acumulado = interes_acumulado + v_interes
        WHERE id = v_cuota_id;
        
        -- Also update the total debt's accumulated interest
        UPDATE deudas 
        SET interes_acumulado = interes_acumulado + v_interes,
            ultima_actualizacion_interes = v_hoy
        WHERE id = v_deuda_id;
    END LOOP;
    
    CLOSE cur_cuotas;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
