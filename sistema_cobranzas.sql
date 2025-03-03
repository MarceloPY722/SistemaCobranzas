-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 03, 2025 at 05:42 PM
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
  `contenido` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_emisor` enum('administrador','cliente') COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `reclamo_id`, `emisor_id`, `contenido`, `fecha_hora`, `tipo_emisor`) VALUES
(1, 4, 9, 'Hola', '2025-03-03 15:41:50', 'administrador'),
(2, 4, 9, 'por aqui hablaremos mejor', '2025-03-03 15:42:59', 'administrador');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identificacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `usuario_id`, `nombre`, `identificacion`, `direccion`, `telefono`, `email`, `created_at`, `imagen`) VALUES
(2, 7, 'Ariel', '9426996', 'San Martin Casi Bolivar', '0971631959', 'ariel@gmail.com', '2025-02-17 01:00:40', 'default.png'),
(3, 8, 'Marcelo7', '4589552', 'San Marti', '0995648334', 'marceloariel862@gmail.com', '2025-02-20 01:50:11', 'default.png'),
(4, 7, 'Carlos Martínez', '12345678', 'Av. San Martín 123', '0981122334', 'carlos.martinez@example.com', '2025-03-03 11:58:41', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `deudas`
--

CREATE TABLE `deudas` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `politica_interes_id` int NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_pendiente` decimal(12,2) NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('pendiente','pagado','vencido','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deudas`
--

INSERT INTO `deudas` (`id`, `cliente_id`, `politica_interes_id`, `monto`, `saldo_pendiente`, `descripcion`, `fecha_vencimiento`, `estado`, `created_at`) VALUES
(1, 4, 1, '1500000.00', '1500000.00', 'Deuda por préstamo no pagado', '2024-12-31', 'pendiente', '2025-03-03 12:12:46');

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
(1, 'Interés Simple Mensual', 'simple', '5.00', '2024-01-01', NULL, 1, '2025-03-03 12:03:28'),
(2, 'Interés Compuesto Anual', 'compuesto', '12.00', '2024-01-01', NULL, 1, '2025-03-03 12:03:28'),
(3, 'Interés Promocional', 'simple', '3.50', '2024-01-01', '2024-12-31', 1, '2025-03-03 12:03:28');

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

--
-- Dumping data for table `reclamos`
--

INSERT INTO `reclamos` (`id`, `cliente_id`, `deuda_id`, `descripcion`, `estado`, `created_at`, `respuesta`, `respondido_por`) VALUES
(4, 4, 1, 'El cliente afirma que realizó el pago el 15/11/2023 mediante transferencia bancaria, pero el sistema no lo refleja.', 'cerrado', '2025-03-03 12:21:15', 'Estaremos revisando su reclamo!!', 9);

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
(1, 'Administrador', '{\n    \"dashboard\": true,\n    \"gestion_usuarios\": true,\n    \"gestion_clientes\": true,\n    \"gestion_deudas\": true,\n    \"gestion_pagos\": true,\n    \"gestion_reclamos\": true,\n    \"configuracion\": true,\n    \"reportes\": true\n}', '2025-02-16 23:47:11'),
(2, 'Gestor de Cobranzas', '{\r\n    \"dashboard\": true,\r\n    \"gestion_clientes\": true,\r\n    \"gestion_deudas\": true,\r\n    \"gestion_pagos\": true,\r\n    \"gestion_reclamos\": true,\r\n    \"reportes\": true\r\n}', '2025-02-16 23:47:11'),
(3, 'Cliente', '{\r\n    \"ver_deudas\": true,\r\n    \"realizar_pagos\": true,\r\n    \"crear_reclamos\": true,\r\n    \"ver_documentos\": true\r\n}', '2025-02-16 23:47:11');

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
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password`, `activo`, `created_at`, `imagen`) VALUES
(1, 1, 'Juan Pérez', 'admin@dominio.com', 'df8d881c50a6916c7ee00baef250c9850a3172fdd003fa5f54db0678aa84185a', 1, '2025-02-16 23:50:08', 'default.png'),
(2, 2, 'María García', 'gestor@dominio.com', '9c6d1957fcc63331ca2df0980f19bcf1401b727cb6c459d6ddeff209c2347767', 1, '2025-02-16 23:50:08', 'default.png'),
(3, 3, 'Carlos López', 'cliente@dominio.com', 'e4498123470460ded4fb56e7b83146290f0260c88bdd1fe09b335b351b5b5a95', 1, '2025-02-16 23:50:08', 'default.png'),
(5, 1, 'Admin Principal', 'admin@gmail.com', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 1, '2025-02-17 00:31:35', 'default.png'),
(6, 1, 'Marcelo', 'marcelo@gmail.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-02-17 00:37:57', 'default.png'),
(7, 3, 'Ariel', 'ariel@gmail.com', '$2y$10$gCH7UkreWl9sJzzNPMvIWODmqdgJsJRmRb58fV0IgXVgWK8Vj9fc2', 1, '2025-02-17 01:00:40', 'default.png'),
(8, 3, 'Marcelo7', 'marceloariel862@gmail.com', '$2y$10$TFH2/34NmfzSb721KAkAHOytCYGnSKvUVLHvcZnOe41gDcG1JSGQu', 1, '2025-02-20 01:50:11', '2625eedc727819d507f49415fe8c3a4e.png'),
(9, 2, 'gestor.php', 'gestor.php@dominio.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-03-01 02:13:21', 'default.png'),
(10, 3, 'Carlos Martínez', 'carlos.martinez@example.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-03-03 12:08:32', 'default.png');

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
  ADD UNIQUE KEY `identificacion` (`identificacion`),
  ADD KEY `usuario_id` (`usuario_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_deudas`
--
ALTER TABLE `historial_deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

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

--
-- Constraints for table `reclamos`
--
ALTER TABLE `reclamos`
  ADD CONSTRAINT `reclamos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reclamos_ibfk_2` FOREIGN KEY (`deuda_id`) REFERENCES `deudas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
