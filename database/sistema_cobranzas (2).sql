-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2025 at 03:35 PM
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
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `identificacion`, `direccion`, `telefono`, `email`, `created_at`, `imagen`, `password`) VALUES
(13, 'Carlos Rodríguez', '1255665', 'Av. Mcal. López 1234', '0961345678', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(14, 'María González', '1456789', 'Av. España 567', '0971456789', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(15, 'Juan Martínez', '1678901', 'Calle Palma 890', '0981567890', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(16, 'Ana Sánchez', '1890123', 'Av. Artigas 123', '0991678901', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(17, 'Pedro Gómez', '2345678', 'Calle Estrella 456', '0962789012', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(18, 'Laura Fernández', '2567890', 'Av. Mariscal Estigarribia 789', '0972890123', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(19, 'Miguel López', '2789012', 'Calle Independencia 012', '0982901234', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(20, 'Sofía Díaz', '3012345', 'Av. Sacramento 345', '0992012345', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(21, 'José Pérez', '3234567', 'Calle Colón 678', '0963123456', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(22, 'Lucía Torres', '3456789', 'Av. Aviadores del Chaco 901', '0973234567', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(23, 'Roberto Ramírez', '3678901', 'Calle Yegros 234', '0983345678', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(24, 'Valentina Flores', '4012345', 'Av. Santísima Trinidad 567', '0993456789', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(25, 'Daniel Acosta', '4234567', 'Calle Iturbe 890', '0964567890', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(26, 'Camila Benítez', '4567890', 'Av. Félix Bogado 123', '0974678901', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(27, 'Alejandro Romero', '5678901', 'Calle 14 de Mayo 456', '0984789012', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(28, 'Valeria Medina', '6123456', 'Av. Bernardino Caballero 789', '0994890123', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(29, 'Gabriel Rojas', '6789012', 'Calle Antequera 012', '0961901234', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(30, 'Natalia Ortiz', '7123456', 'Av. General Santos 345', '0971012345', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(31, 'Sebastián Giménez', '7890123', 'Calle Azara 678', '0981123456', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(32, 'Isabella Núñez', '8456789', 'Av. Eusebio Ayala 901', '0991234567', NULL, '2025-03-15 14:15:41', 'default.png', NULL),
(33, 'Marcelo Ariel Benitez', '8426996', 'Sam Martin Casi Bolivar\r\n', '0971631959', 'marceloariel722@gmail.com', '2025-03-15 14:41:59', '67d591b74dc66.png', '$2y$10$9f/axWlK2x6jnJT.0QezjuViITYt84Fz3JUOixAgzZmyinI8QbjXS');

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
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password`, `activo`, `created_at`, `imagen`) VALUES
(6, 1, 'Marcelo', 'marcelo@gmail.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-02-17 03:37:57', 'usuario_6_1741560443.jpg'),
(9, 2, 'gestor.php', 'gestor.php@dominio.com', '$2y$10$sM2SMBWuM85jqx7tV7Xhj.y.pWEOhmcXbTo0XYwKzWiRN.HMzUZ5m', 1, '2025-03-01 05:13:21', 'usuario_9_1741560308.png'),
(17, 1, 'Administrador', 'Admin@gmail.com', '$2y$10$5C4ffiSK9KiYJXoSVK9F0edURthp0iWYNJ9tVUCXKD7tpRsO9sb3G', 1, '2025-03-10 01:12:00', 'cd9f1fc8fcbf5d15eb8b4809567c6294.png');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
