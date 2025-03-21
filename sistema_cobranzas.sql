-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 17, 2025 at 12:11 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_cobranzas`
--

-- --------------------------------------------------------

--
-- Table structure for table `chats`
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
-- Table structure for table `clientes`
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
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
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
-- Table structure for table `deudas`
--

CREATE TABLE `deudas` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `politica_interes_id` int NOT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `monto` decimal(12,2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `saldo_pendiente` decimal(12,2) NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('pendiente','pagado','vencido','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deudas`
--

INSERT INTO `deudas` (`id`, `cliente_id`, `politica_interes_id`, `notas`, `monto`, `fecha_emision`, `saldo_pendiente`, `descripcion`, `fecha_vencimiento`, `estado`, `created_at`) VALUES
(1, 33, 2, '', '1000000.00', '2025-03-15', '1000000.00', 'CrediAgil', '2025-04-14', 'pendiente', '2025-03-15 16:50:15'),
(5, 32, 3, '', '41000000.00', '2025-03-16', '41000000.00', 'CrediAgil San Lorenzo', '2026-04-15', 'pendiente', '2025-03-16 23:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `documentos`
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
-- Table structure for table `historial_deudas`
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
-- Dumping data for table `historial_deudas`
--

INSERT INTO `historial_deudas` (`id`, `deuda_id`, `usuario_id`, `accion`, `detalle`, `created_at`) VALUES
(1, 1, 6, 'creación', 'Creación de nueva deuda por monto $1,000,000.00', '2025-03-15 16:50:15'),
(5, 5, 6, 'creación', 'Creación de nueva deuda por monto 41.000.000,00 Gs.', '2025-03-16 23:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
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
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `deuda_id` int NOT NULL,
  `monto_pagado` decimal(12,2) NOT NULL,
  `metodo_pago` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `politicas_interes`
--

CREATE TABLE `politicas_interes` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('simple','compuesto') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasa` decimal(5,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `politicas_interes`
--

INSERT INTO `politicas_interes` (`id`, `nombre`, `tipo`, `tasa`, `fecha_inicio`, `fecha_fin`, `activa`, `created_at`) VALUES
(1, 'Interés Simple Mensual', 'simple', '5.00', '2024-01-01', NULL, 1, '2025-03-03 15:03:28'),
(2, 'Interés Compuesto Anual', 'compuesto', '12.00', '2024-01-01', NULL, 1, '2025-03-03 15:03:28'),
(3, 'Interés Promocional', 'simple', '3.50', '2024-01-01', '2024-12-31', 1, '2025-03-03 15:03:28');

-- --------------------------------------------------------

--
-- Table structure for table `reclamos`
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
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `permisos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `permisos`, `created_at`) VALUES
(1, 'Administrador', '{\n    \"dashboard\": true,\n    \"gestion_usuarios\": true,\n    \"gestion_clientes\": true,\n    \"gestion_deudas\": true,\n    \"gestion_pagos\": true,\n    \"gestion_reclamos\": true,\n    \"configuracion\": true,\n    \"reportes\": true\n}', '2025-02-17 02:47:11'),
(2, 'Gestor de Cobranzas', '{\r\n    \"dashboard\": true,\r\n    \"gestion_clientes\": true,\r\n    \"gestion_deudas\": true,\r\n    \"gestion_pagos\": true,\r\n    \"gestion_reclamos\": true,\r\n    \"reportes\": true\r\n}', '2025-02-17 02:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
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
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password`, `activo`, `created_at`, `imagen`, `last_activity`) VALUES
(6, 1, 'Marcelo', 'marcelo@gmail.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-02-17 03:37:57', 'usuario_6_1741560443.jpg', '2025-03-16 21:09:44'),
(9, 2, 'gestor.php', 'gestor.php@dominio.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-03-01 05:13:21', 'usuario_9_1741560308.png', '2025-03-15 22:12:53'),
(17, 1, 'Administrador', 'Admin@gmail.com', '$2y$10$5C4ffiSK9KiYJXoSVK9F0edURthp0iWYNJ9tVUCXKD7tpRsO9sb3G', 1, '2025-03-10 01:12:00', 'cd9f1fc8fcbf5d15eb8b4809567c6294.png', '2025-03-15 22:12:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reclamo_id` (`reclamo_id`),
  ADD KEY `emisor_id` (`emisor_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`);

--
-- Indexes for table `deudas`
--
ALTER TABLE `deudas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deudas_cliente` (`cliente_id`),
  ADD KEY `idx_deudas_politica` (`politica_interes_id`);

--
-- Indexes for table `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `deuda_id` (`deuda_id`);

--
-- Indexes for table `historial_deudas`
--
ALTER TABLE `historial_deudas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deuda_id` (`deuda_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagos_deuda` (`deuda_id`);

--
-- Indexes for table `politicas_interes`
--
ALTER TABLE `politicas_interes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reclamos`
--
ALTER TABLE `reclamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deuda_id` (`deuda_id`),
  ADD KEY `idx_reclamos_cliente` (`cliente_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_deudas`
--
ALTER TABLE `historial_deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `politicas_interes`
--
ALTER TABLE `politicas_interes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reclamos`
--
ALTER TABLE `reclamos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`reclamo_id`) REFERENCES `reclamos` (`id`),
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `deudas`
--
ALTER TABLE `deudas`
  ADD CONSTRAINT `deudas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deudas_politicas` FOREIGN KEY (`politica_interes_id`) REFERENCES `politicas_interes` (`id`);

--
-- Constraints for table `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `historial_deudas`
--
ALTER TABLE `historial_deudas`
  ADD CONSTRAINT `historial_deudas_ibfk_1` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_deudas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
