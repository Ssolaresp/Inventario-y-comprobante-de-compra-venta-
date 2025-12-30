-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 30, 2025 at 09:30 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aegis`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_saldo_factura` (IN `p_factura_id` INT)   BEGIN
    DECLARE v_total DECIMAL(12,2);
    DECLARE v_total_pagado DECIMAL(12,2);
    DECLARE v_saldo DECIMAL(12,2);
    
    -- Obtener total de la factura
    SELECT total INTO v_total
    FROM facturas
    WHERE id = p_factura_id;
    
    -- Calcular total pagado
    SELECT COALESCE(SUM(monto), 0) INTO v_total_pagado
    FROM facturas_pagos
    WHERE factura_id = p_factura_id;
    
    -- Calcular saldo
    SET v_saldo = v_total - v_total_pagado;
    
    -- Actualizar saldo en factura
    UPDATE facturas
    SET saldo_pendiente = v_saldo,
        actualizado_en = NOW()
    WHERE id = p_factura_id;
    
    -- Actualizar estado según saldo
    IF v_saldo <= 0 THEN
        UPDATE facturas
        SET estado_factura_id = (SELECT id FROM estados_factura WHERE codigo = 'PAG')
        WHERE id = p_factura_id;
    ELSEIF v_saldo < v_total THEN
        UPDATE facturas
        SET estado_factura_id = (SELECT id FROM estados_factura WHERE codigo = 'PAR')
        WHERE id = p_factura_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generar_numero_factura` (IN `p_serie_id` INT, OUT `p_numero_factura` VARCHAR(50))   BEGIN
    DECLARE v_serie VARCHAR(20);
    DECLARE v_numero_actual INT;
    DECLARE v_numero_siguiente INT;
    
    -- Obtener serie y número actual con bloqueo
    SELECT serie, numero_actual INTO v_serie, v_numero_actual
    FROM series_facturacion
    WHERE id = p_serie_id
    FOR UPDATE;
    
    -- Calcular siguiente número
    SET v_numero_siguiente = v_numero_actual + 1;
    
    -- Generar número de factura con formato: SERIE-00000001
    SET p_numero_factura = CONCAT(v_serie, '-', LPAD(v_numero_siguiente, 8, '0'));
    
    -- Actualizar el número actual en la serie
    UPDATE series_facturacion
    SET numero_actual = v_numero_siguiente,
        actualizado_en = NOW()
    WHERE id = p_serie_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `almacenes`
--

CREATE TABLE `almacenes` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` text,
  `descripcion` text,
  `responsable_usuario_id` int DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `almacenes`
--

INSERT INTO `almacenes` (`id`, `codigo`, `nombre`, `ubicacion`, `descripcion`, `responsable_usuario_id`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'ALM-001', 'Almacén Principal', 'Zona 10, Guatemala', 'Almacén central de la empresa', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'ALM-002', 'Almacén Sucursal Norte', 'Zona 18, Guatemala', 'Almacén sucursal norte', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'ALM-003', 'Almacén Sucursal Sur', 'Zona 11, Guatemala', 'Almacén sucursal sur', 2, 1, 1, 1, '2025-10-12 10:17:43', '2025-10-12 23:22:23'),
(4, 'ALM-004', 'Almacen Temporal', 'xd', 'Almacen Temporal', 1, 1, 1, NULL, '2025-10-12 23:23:02', '2025-10-12 23:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `bitacora_estados`
--

CREATE TABLE `bitacora_estados` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bitacora_estados`
--

INSERT INTO `bitacora_estados` (`id`, `nombre`, `descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, 'activo', 'Usuario habilitado para acceder al sistema', '2025-07-29 07:53:47', '2025-07-29 07:53:47'),
(2, 'inactivo', 'Usuario deshabilitado temporalmente', '2025-07-29 07:53:47', '2025-07-29 07:53:47'),
(3, 'suspendido', 'Cuenta suspendida por alguna razón', '2025-07-29 07:53:47', '2025-07-29 07:53:47'),
(4, 'eliminado', 'Cuenta eliminada lógicamente', '2025-07-29 07:53:47', '2025-07-29 07:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `bitacora_inventario`
--

CREATE TABLE `bitacora_inventario` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `almacen_id` int NOT NULL,
  `tipo_movimiento_id` int NOT NULL,
  `cantidad_anterior` decimal(10,2) NOT NULL,
  `cantidad_movimiento` decimal(10,2) NOT NULL,
  `cantidad_nueva` decimal(10,2) NOT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL COMMENT 'transferencia, salida, entrada, ajuste',
  `referencia_id` int DEFAULT NULL COMMENT 'ID del registro referenciado',
  `usuario_id` int NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bitacora_inventario`
--

INSERT INTO `bitacora_inventario` (`id`, `producto_id`, `almacen_id`, `tipo_movimiento_id`, `cantidad_anterior`, `cantidad_movimiento`, `cantidad_nueva`, `referencia_tipo`, `referencia_id`, `usuario_id`, `fecha_movimiento`, `observaciones`) VALUES
(1, 1, 1, 1, 0.00, 2.00, 2.00, 'entrada', 1, 1, '2025-10-20 21:05:58', 'Registro inicial de inventario'),
(2, 1, 1, 1, 2.00, 3.00, 5.00, 'ajuste', 1, 1, '2025-10-20 21:08:46', 'Ajuste manual de inventario'),
(3, 1, 1, 2, 5.00, 3.00, 2.00, 'transferencia', 1, 1, '2025-10-20 21:10:23', 'Envío transferencia TRANS-000001'),
(4, 1, 4, 1, 0.00, 3.00, 3.00, 'transferencia', 1, 1, '2025-10-20 21:10:28', 'Recepción transferencia TRANS-000001'),
(5, 1, 4, 2, 3.00, 1.00, 2.00, 'ajuste', 2, 1, '2025-10-22 19:56:19', 'Ajuste manual de inventario'),
(6, 1, 1, 2, 2.00, 1.00, 1.00, 'transferencia', 3, 1, '2025-10-22 20:00:40', 'Envío transferencia TRANS-000002'),
(7, 1, 3, 1, 0.00, 1.00, 1.00, 'transferencia', 3, 1, '2025-10-22 20:01:14', 'Recepción transferencia TRANS-000002'),
(8, 1, 1, 2, 1.00, 1.00, 0.00, 'transferencia', 4, 1, '2025-10-22 20:02:07', 'Envío transferencia TRANS-000003'),
(9, 1, 4, 1, 2.00, 1.00, 3.00, 'transferencia', 4, 1, '2025-10-22 20:02:29', 'Recepción transferencia TRANS-000003'),
(10, 1, 3, 2, 1.00, 1.00, 0.00, 'salida', 1, 1, '2025-10-22 20:04:11', 'Salida SAL-000001 - Ajuste Inventario'),
(11, 1, 4, 1, 3.00, 1.00, 4.00, 'entrada', 1, 1, '2025-10-22 20:06:04', 'Entrada ENT-000001 - Ajuste Inventario'),
(12, 1, 1, 1, 0.00, 10.00, 10.00, 'ajuste', 1, 1, '2025-10-25 19:20:07', 'Ajuste manual de inventario'),
(13, 1, 1, 2, 10.00, 5.00, 5.00, 'transferencia', 6, 1, '2025-10-25 19:22:34', 'Envío transferencia TRANS-000005'),
(14, 1, 4, 1, 4.00, 5.00, 9.00, 'transferencia', 6, 1, '2025-10-25 19:24:04', 'Recepción transferencia TRANS-000005'),
(15, 1, 4, 2, 9.00, 8.00, 1.00, 'transferencia', 9, 1, '2025-10-25 19:31:51', 'Envío transferencia TRANS-000007'),
(16, 1, 1, 1, 5.00, 7.00, 12.00, 'transferencia', 9, 1, '2025-10-25 19:32:13', 'Recepción transferencia TRANS-000007'),
(17, 1, 3, 1, 0.00, 10.00, 10.00, 'entrada', 2, 2, '2025-10-25 19:34:34', 'Entrada ENT-000002 - Compra'),
(18, 1, 3, 2, 10.00, 7.00, 3.00, 'salida', 2, 2, '2025-10-25 19:36:13', 'Salida SAL-000002 - Devolución'),
(19, 2, 1, 1, 0.00, 10.00, 10.00, 'entrada', 4, 1, '2025-10-25 19:53:16', 'Entrada ENT-000004 - Compra'),
(20, 2, 1, 2, 10.00, 3.00, 7.00, 'transferencia', 11, 1, '2025-10-25 19:56:31', 'Envío transferencia TRANS-000009'),
(21, 2, 4, 1, 0.00, 2.00, 2.00, 'transferencia', 11, 1, '2025-10-25 19:56:40', 'Recepción transferencia TRANS-000009'),
(22, 1, 1, 2, 12.00, 11.00, 1.00, 'transferencia', 12, 1, '2025-10-25 20:27:54', 'Envío transferencia TRANS-000010'),
(23, 2, 1, 2, 7.00, 6.00, 1.00, 'transferencia', 12, 1, '2025-10-25 20:27:54', 'Envío transferencia TRANS-000010'),
(24, 1, 4, 1, 1.00, 11.00, 12.00, 'transferencia', 12, 1, '2025-10-25 20:28:00', 'Recepción transferencia TRANS-000010'),
(25, 2, 4, 1, 2.00, 6.00, 8.00, 'transferencia', 12, 1, '2025-10-25 20:28:00', 'Recepción transferencia TRANS-000010'),
(26, 2, 1, 1, 1.00, 1.00, 2.00, 'entrada', 5, 1, '2025-10-25 21:37:29', 'Entrada ENT-000005 - Ajuste Inventario'),
(27, 1, 1, 2, 1.00, 1.00, 0.00, 'transferencia', 13, 1, '2025-10-25 22:04:04', 'Envío transferencia TRANS-000011'),
(28, 2, 1, 2, 2.00, 1.00, 1.00, 'transferencia', 13, 1, '2025-10-25 22:04:04', 'Envío transferencia TRANS-000011'),
(29, 1, 4, 1, 12.00, 1.00, 13.00, 'transferencia', 13, 2, '2025-10-25 22:05:06', 'Recepción transferencia TRANS-000011'),
(30, 2, 4, 1, 8.00, 1.00, 9.00, 'transferencia', 13, 2, '2025-10-25 22:05:06', 'Recepción transferencia TRANS-000011'),
(31, 2, 1, 2, 1.00, 1.00, 0.00, 'transferencia', 14, 1, '2025-10-25 22:12:59', 'Envío transferencia TRANS-000012'),
(32, 2, 4, 1, 9.00, 1.00, 10.00, 'transferencia', 14, 2, '2025-10-25 22:13:21', 'Recepción transferencia TRANS-000012'),
(33, 2, 1, 1, 0.00, 10.00, 10.00, 'ajuste', 4, 1, '2025-10-25 22:14:05', 'Ajuste manual de inventario'),
(34, 1, 3, 1, 3.00, 7.00, 10.00, 'ajuste', 3, 1, '2025-10-25 22:14:12', 'Ajuste manual de inventario'),
(35, 1, 4, 2, 13.00, 3.00, 10.00, 'ajuste', 2, 1, '2025-10-25 22:14:19', 'Ajuste manual de inventario'),
(36, 1, 1, 1, 0.00, 10.00, 10.00, 'ajuste', 1, 1, '2025-10-25 22:14:28', 'Ajuste manual de inventario'),
(37, 1, 1, 2, 10.00, 5.00, 5.00, 'transferencia', 15, 1, '2025-10-25 22:15:33', 'Envío transferencia TRANS-000013'),
(38, 2, 1, 2, 10.00, 5.00, 5.00, 'transferencia', 15, 1, '2025-10-25 22:15:33', 'Envío transferencia TRANS-000013'),
(39, 1, 4, 1, 10.00, 5.00, 15.00, 'transferencia', 15, 2, '2025-10-25 22:16:48', 'Recepción transferencia TRANS-000013'),
(40, 2, 4, 1, 10.00, 5.00, 15.00, 'transferencia', 15, 2, '2025-10-25 22:16:48', 'Recepción transferencia TRANS-000013'),
(41, 2, 1, 1, 5.00, 5.00, 10.00, 'ajuste', 4, 1, '2025-10-25 22:29:34', 'Ajuste manual de inventario'),
(42, 1, 4, 2, 15.00, 5.00, 10.00, 'ajuste', 2, 1, '2025-10-25 22:29:48', 'Ajuste manual de inventario'),
(43, 2, 4, 2, 15.00, 5.00, 10.00, 'ajuste', 5, 1, '2025-10-25 22:29:54', 'Ajuste manual de inventario'),
(44, 1, 1, 1, 5.00, 5.00, 10.00, 'ajuste', 1, 1, '2025-10-25 22:32:26', 'Ajuste manual de inventario'),
(45, 1, 1, 2, 10.00, 10.00, 0.00, 'transferencia', 2, 1, '2025-10-25 22:32:51', 'Envío transferencia TRANS-000001'),
(46, 2, 1, 2, 10.00, 10.00, 0.00, 'transferencia', 2, 1, '2025-10-25 22:32:51', 'Envío transferencia TRANS-000001'),
(47, 1, 4, 1, 10.00, 10.00, 20.00, 'transferencia', 2, 2, '2025-10-25 22:33:35', 'Recepción transferencia TRANS-000001'),
(48, 2, 4, 1, 10.00, 10.00, 20.00, 'transferencia', 2, 2, '2025-10-25 22:33:35', 'Recepción transferencia TRANS-000001'),
(49, 1, 3, 2, 10.00, 10.00, 0.00, 'transferencia', 3, 2, '2025-10-25 22:36:49', 'Envío transferencia TRANS-000002'),
(50, 1, 4, 1, 20.00, 9.00, 29.00, 'transferencia', 3, 1, '2025-10-25 22:37:13', 'Recepción transferencia TRANS-000002'),
(51, 2, 4, 2, 20.00, 10.00, 10.00, 'ajuste', 5, 1, '2025-10-25 22:37:41', 'Ajuste manual de inventario'),
(52, 2, 1, 1, 0.00, 10.00, 10.00, 'ajuste', 4, 1, '2025-10-25 22:37:49', 'Ajuste manual de inventario'),
(53, 1, 3, 1, 0.00, 10.00, 10.00, 'ajuste', 3, 1, '2025-10-25 22:37:54', 'Ajuste manual de inventario'),
(54, 1, 4, 2, 29.00, 19.00, 10.00, 'ajuste', 2, 1, '2025-10-25 22:37:59', 'Ajuste manual de inventario'),
(55, 1, 1, 1, 0.00, 10.00, 10.00, 'ajuste', 1, 1, '2025-10-25 22:38:05', 'Ajuste manual de inventario'),
(56, 2, 1, 1, 10.00, 1.00, 11.00, 'entrada', 6, 1, '2025-10-28 10:07:45', 'Entrada ENT-000006 - Ajuste Inventario'),
(57, 2, 4, 1, 10.00, 10.00, 20.00, 'entrada', 7, 1, '2025-10-28 10:13:31', 'Entrada ENT-000007 - Ajuste Inventario'),
(58, 2, 1, 2, 11.00, 1.00, 10.00, 'salida', 3, 1, '2025-10-28 10:45:01', 'Salida SAL-000003 - Ajuste Inventario'),
(59, 2, 1, 2, 10.00, 10.00, 0.00, 'salida', 4, 1, '2025-10-28 10:59:13', 'Salida SAL-000004 - Ajuste Inventario'),
(60, 1, 1, 2, 5.00, 5.00, 0.00, 'transferencia', 4, 1, '2025-11-09 17:26:15', 'Envío transferencia TRANS-000003'),
(61, 1, 4, 1, 10.00, 5.00, 15.00, 'transferencia', 4, 2, '2025-11-09 17:27:10', 'Recepción transferencia TRANS-000003'),
(62, 2, 1, 1, 0.00, 10.00, 10.00, 'entrada', 9, 1, '2025-11-13 21:18:42', 'Entrada ENT-000009 - Compra'),
(63, 1, 4, 2, 15.00, 1.00, 14.00, 'salida', 5, 1, '2025-11-13 21:20:28', 'Salida SAL-000005 - Ajuste Inventario'),
(64, 1, 3, 2, 10.00, 5.00, 5.00, 'transferencia', 5, 1, '2025-11-13 21:27:38', 'Envío transferencia TRANS-000004'),
(65, 1, 4, 1, 14.00, 4.00, 18.00, 'transferencia', 5, 2, '2025-11-13 21:28:29', 'Recepción transferencia TRANS-000004'),
(66, 2, 1, 2, 9.00, 8.00, 1.00, 'transferencia', 6, 1, '2025-11-15 22:42:18', 'Envío transferencia TRANS-000005'),
(67, 2, 3, 1, 0.00, 7.00, 7.00, 'transferencia', 6, 2, '2025-11-15 22:44:05', 'Recepción transferencia TRANS-000005'),
(68, 2, 1, 1, 1.00, 10.00, 11.00, 'entrada', 10, 1, '2025-11-20 20:42:55', 'Entrada ENT-000010 - Compra'),
(69, 2, 1, 2, 11.00, 10.00, 1.00, 'salida', 6, 1, '2025-11-20 20:44:40', 'Salida SAL-000006 - Ajuste Inventario'),
(70, 2, 4, 2, 20.00, 3.00, 17.00, 'transferencia', 7, 1, '2025-11-20 20:47:13', 'Envío transferencia TRANS-000006'),
(71, 2, 3, 1, 7.00, 2.00, 9.00, 'transferencia', 7, 2, '2025-11-20 20:47:54', 'Recepción transferencia TRANS-000006'),
(72, 3, 1, 1, 0.00, 10.00, 10.00, 'entrada', 10, 4, '2025-11-30 17:22:56', 'Registro inicial de inventario'),
(73, 3, 1, 2, 10.00, 5.00, 5.00, 'transferencia', 8, 4, '2025-11-30 17:24:57', 'Envío transferencia TRANS-000007'),
(74, 3, 4, 1, 0.00, 5.00, 5.00, 'transferencia', 8, 1, '2025-11-30 17:25:54', 'Recepción transferencia TRANS-000007'),
(75, 3, 1, 1, 5.00, 15.00, 20.00, 'entrada', 11, 1, '2025-11-30 17:30:42', 'Entrada ENT-000011 - Compra'),
(76, 3, 1, 2, 20.00, 15.00, 5.00, 'salida', 7, 1, '2025-11-30 17:33:06', 'Salida SAL-000007 - Ajuste Inventario');

-- --------------------------------------------------------

--
-- Table structure for table `bitacora_sesiones`
--

CREATE TABLE `bitacora_sesiones` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `navegador` text,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_salida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bitacora_sesiones`
--

INSERT INTO `bitacora_sesiones` (`id`, `usuario_id`, `ip`, `navegador`, `fecha_ingreso`, `fecha_salida`) VALUES
(1, 1, '192.168.1.1', 'Mozila firefox', '2025-07-29 08:23:15', NULL),
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-29 09:24:41', NULL),
(3, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-29 09:26:32', '2025-07-29 09:26:33'),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-29 09:29:29', '2025-10-12 10:52:51'),
(5, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-29 09:36:13', '2025-07-29 09:44:28'),
(6, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 10:52:56', '2025-10-12 11:10:34'),
(7, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 11:46:07', NULL),
(8, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 12:22:19', NULL),
(9, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-19 21:17:29', '2025-10-19 21:27:21'),
(10, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-19 21:27:51', '2025-10-19 21:50:57'),
(11, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-19 21:51:01', NULL),
(12, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 15:32:13', '2025-10-20 17:48:34'),
(13, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 17:48:57', '2025-10-20 21:00:19'),
(14, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 21:01:07', '2025-10-21 10:48:22'),
(15, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 11:36:02', NULL),
(16, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 19:48:32', '2025-10-23 07:00:37'),
(17, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 11:19:11', NULL),
(18, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:07:12', '2025-10-25 13:07:36'),
(19, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:07:43', '2025-10-25 13:15:08'),
(20, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:15:14', '2025-10-25 13:15:42'),
(21, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:15:48', '2025-10-25 13:19:08'),
(22, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:19:22', '2025-10-25 13:25:33'),
(23, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:25:38', '2025-10-25 13:35:35'),
(24, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:35:41', '2025-10-25 13:42:41'),
(25, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:42:55', '2025-10-25 18:12:36'),
(26, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:12:42', '2025-10-25 18:14:54'),
(27, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:15:00', '2025-10-25 18:43:17'),
(28, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 18:41:48', '2025-10-25 18:43:03'),
(29, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:43:23', '2025-10-25 18:50:47'),
(30, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:50:56', '2025-10-25 18:51:23'),
(31, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:51:36', '2025-10-25 18:52:16'),
(32, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:52:23', '2025-10-25 18:52:37'),
(33, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 18:52:44', '2025-10-25 18:55:04'),
(34, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:01:36', '2025-10-25 19:01:38'),
(35, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:07:57', '2025-10-25 19:09:59'),
(36, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:13:11', '2025-10-25 19:13:19'),
(37, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:15:14', '2025-10-25 19:19:31'),
(38, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:19:37', '2025-10-25 19:22:52'),
(39, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:22:58', '2025-10-25 19:23:46'),
(40, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:23:52', '2025-10-25 19:24:15'),
(41, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:24:21', '2025-10-25 19:25:17'),
(42, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:25:28', '2025-10-25 19:29:53'),
(43, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:30:05', '2025-10-25 19:32:33'),
(44, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:32:39', '2025-10-25 19:36:40'),
(45, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:36:50', '2025-10-25 19:39:21'),
(46, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:39:43', '2025-10-25 19:47:03'),
(47, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:48:48', '2025-10-25 19:54:57'),
(48, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 19:55:53', '2025-10-25 20:10:08'),
(49, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:10:36', '2025-10-25 20:18:52'),
(50, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:22:25', '2025-10-25 20:26:15'),
(51, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:26:22', '2025-10-25 20:26:58'),
(52, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:27:04', '2025-10-25 20:28:02'),
(53, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:28:07', '2025-10-25 20:30:03'),
(54, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:30:13', '2025-10-25 21:14:04'),
(55, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 21:14:09', '2025-10-25 21:16:16'),
(56, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 21:16:39', '2025-10-25 21:27:13'),
(57, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 21:27:29', '2025-10-25 21:40:04'),
(58, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 21:42:14', '2025-10-25 22:04:31'),
(59, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:04:38', '2025-10-25 22:09:24'),
(60, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:09:30', '2025-10-25 22:13:05'),
(61, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:13:12', '2025-10-25 22:13:34'),
(62, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:13:48', '2025-10-25 22:15:39'),
(63, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:15:46', '2025-10-25 22:28:02'),
(64, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:28:07', '2025-10-25 22:32:53'),
(65, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:33:15', '2025-10-25 22:36:53'),
(66, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 22:36:59', '2025-10-26 19:55:29'),
(67, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 19:57:57', '2025-10-26 19:59:02'),
(68, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 20:07:28', '2025-10-26 20:13:54'),
(69, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 20:15:32', NULL),
(70, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-26 20:46:00', NULL),
(71, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 17:15:09', NULL),
(72, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 09:58:36', '2025-10-28 11:05:57'),
(73, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 11:06:02', NULL),
(74, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 17:37:08', '2025-10-29 17:38:37'),
(75, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 18:14:38', '2025-10-31 18:15:19'),
(76, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-02 11:43:04', NULL),
(77, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 20:04:41', NULL),
(78, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 10:47:47', NULL),
(79, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 11:51:14', '2025-11-09 15:14:35'),
(80, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 15:14:41', NULL),
(81, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 15:25:43', NULL),
(82, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 15:38:28', '2025-11-09 16:06:10'),
(83, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 16:25:03', '2025-11-09 17:24:24'),
(84, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 17:24:30', '2025-11-09 17:26:43'),
(85, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 17:26:48', '2025-11-09 17:28:45'),
(86, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 17:28:52', '2025-11-09 18:22:43'),
(87, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-09 18:23:11', NULL),
(88, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 21:00:56', '2025-11-11 21:41:16'),
(89, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 21:41:33', NULL),
(90, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 22:06:46', '2025-11-12 09:30:52'),
(91, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 12:56:10', NULL),
(92, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:10:53', '2025-11-13 21:25:16'),
(93, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:25:38', '2025-11-13 21:26:49'),
(94, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:27:13', '2025-11-13 21:27:41'),
(95, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:27:52', '2025-11-13 21:32:03'),
(96, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:32:18', '2025-11-13 21:33:17'),
(97, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:33:28', '2025-11-13 21:50:09'),
(98, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 21:50:40', NULL),
(99, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-15 22:18:08', '2025-11-15 22:42:43'),
(100, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-15 22:42:53', NULL),
(101, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 20:28:10', '2025-11-20 20:47:25'),
(102, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 20:47:31', NULL),
(103, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-20 20:56:06', NULL),
(104, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:13:48', '2025-11-30 17:16:13'),
(105, 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:16:19', '2025-11-30 17:16:35'),
(106, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:16:56', '2025-11-30 17:17:24'),
(107, 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:17:30', '2025-11-30 17:25:09'),
(108, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:25:19', '2025-11-30 17:43:43'),
(109, 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:43:50', '2025-11-30 17:44:01'),
(110, 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:44:09', '2025-11-30 17:44:42'),
(111, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:44:49', '2025-11-30 17:46:04'),
(112, 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 17:45:25', NULL),
(113, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 17:46:11', '2025-11-30 17:46:22'),
(114, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 17:46:28', NULL),
(115, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 17:57:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `canales_venta`
--

CREATE TABLE `canales_venta` (
  `id_canal` int NOT NULL,
  `codigo_canal` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_canal` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `estado_id` int NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `canales_venta`
--

INSERT INTO `canales_venta` (`id_canal`, `codigo_canal`, `nombre_canal`, `descripcion`, `estado_id`, `fecha_registro`, `creado_en`, `actualizado_en`) VALUES
(1, 'CN001', 'Tienda Física', 'Ventas realizadas directamente en mostrador o punto de venta físico.', 1, '2025-10-26 19:30:59', '2025-10-26 19:30:59', NULL),
(2, 'CN002', 'Ventas en Línea', 'Pedidos realizados a través de la plataforma web o tienda virtual.', 1, '2025-10-26 19:30:59', '2025-10-26 19:30:59', NULL),
(3, 'CN003', 'Distribuidor', 'Canal dedicado a ventas por medio de distribuidores autorizados.', 1, '2025-10-26 19:30:59', '2025-10-26 19:30:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `categoria_padre_id` int DEFAULT NULL COMMENT 'Para subcategorías',
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `codigo`, `nombre`, `descripcion`, `categoria_padre_id`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'CAT-001', 'Electrónica', 'Productos electrónicos', NULL, 1, 1, NULL, '2025-10-12 10:17:44', '2025-10-12 10:17:44'),
(2, 'CAT-002', 'Alimentos', 'Productos alimenticios', NULL, 1, 1, NULL, '2025-10-12 10:17:44', '2025-10-12 10:17:44'),
(3, 'CAT-003', 'Bebidas', 'Bebidas en general', 3, 1, 1, 2, '2025-10-12 10:17:44', '2025-10-25 13:33:19'),
(4, 'CAT-004', 'Limpieza', 'Productos de limpieza', NULL, 1, 1, NULL, '2025-10-12 10:17:44', '2025-10-12 10:17:44'),
(5, 'CAT-005', 'Computadoras', 'Computadoras y accesorios', 1, 1, 1, NULL, '2025-10-12 10:17:44', '2025-10-12 10:17:44'),
(6, 'CAT-006', 'Celulares', 'Teléfonos celulares', 1, 1, 1, NULL, '2025-10-12 10:17:44', '2025-10-12 10:17:44'),
(7, 'CAT-007', 'General', 'Sin categoría, general para productos poco específicos', NULL, 1, NULL, NULL, '2025-10-12 11:44:35', '2025-10-12 11:44:35'),
(8, 'CAT-008', 'Tecnologia', '', NULL, 1, 4, NULL, '2025-11-30 17:20:55', '2025-11-30 17:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `categorias_servicios`
--

CREATE TABLE `categorias_servicios` (
  `id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categorias_servicios`
--

INSERT INTO `categorias_servicios` (`id`, `codigo`, `nombre`, `descripcion`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'CON', 'Consultoría', 'Servicios de consultoría y asesoría', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(2, 'MAN', 'Mantenimiento', 'Servicios de mantenimiento', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(3, 'INS', 'Instalación', 'Servicios de instalación', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 'CAP', 'Capacitación', 'Servicios de capacitación y formación', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(5, 'SOP', 'Soporte Técnico', 'Servicios de soporte y asistencia técnica', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(6, 'OTR', 'Otros Servicios', 'Otros servicios generales', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(7, 'CAT-007', 'Actualizacion ', 'Actualizacion de servicios', 1, '2025-11-04 02:36:20', '2025-11-04 02:39:11'),
(8, 'CAT-008', 'Reparacion', '', 1, '2025-11-04 02:38:17', '2025-11-04 02:38:58'),
(9, 'CAT-009', 'Inyeccion', 'Inyeccion de producto comprado en el establecimiento ', 1, '2025-11-04 02:39:38', '2025-11-04 02:39:38');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL,
  `codigo_cliente` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tipo_cliente` int NOT NULL,
  `primer_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `segundo_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primer_apellido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `segundo_apellido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dpi` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `id_departamento` int DEFAULT NULL,
  `id_municipio` int DEFAULT NULL,
  `id_estado` int NOT NULL,
  `id_canal` int DEFAULT NULL,
  `usuario_registra_id` int DEFAULT NULL,
  `usuario_modifica_id` int DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `codigo_cliente`, `id_tipo_cliente`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `razon_social`, `nit`, `dpi`, `telefono`, `correo`, `direccion`, `id_departamento`, `id_municipio`, `id_estado`, `id_canal`, `usuario_registra_id`, `usuario_modifica_id`, `fecha_registro`, `creado_en`, `actualizado_en`) VALUES
(1, 'CLI0001', 1, 'Selvin', '', 'Solares', '', NULL, '1234567-8', '3056123450101', '5555-1234', 'selvin.solares@example.com', 'Zona 1, Ciudad de Guatemala', 1, 1, 1, 1, 1, NULL, '2025-10-26 19:52:48', '2025-10-26 19:52:48', '2025-10-26 19:54:11'),
(2, 'CLI0002', 2, NULL, NULL, NULL, NULL, 'Soluciones Globales S.A.', '969696-0', NULL, '5555-9876', 'contacto@solucionesglobales.com', 'Km 15 Carretera a El Salvador, Guatemala', 1, 1, 1, 1, 1, NULL, '2025-10-26 19:52:48', '2025-10-26 19:52:48', NULL),
(3, 'CLI-0003', 2, NULL, NULL, NULL, NULL, 'Distirbuidora S.A.', '451316884', NULL, '59259874', 'admin@example.com', '', 1, 1, 1, 3, 1, NULL, '2025-11-30 17:35:46', '2025-11-30 17:35:46', NULL),
(4, 'CLI-0004', 2, NULL, NULL, NULL, NULL, 'Distirbuidora S.A.', '451316884', NULL, '59259874', 'admin@example.com', '', 3, 35, 1, 3, 1, NULL, '2025-11-30 17:35:55', '2025-11-30 17:35:55', NULL),
(5, 'CLI-0005', 2, NULL, NULL, NULL, NULL, 'Distirbuidora S.A.', '4823951', NULL, '59259874', 'admin@example.com', '', 1, 8, 1, 3, 1, NULL, '2025-11-30 17:36:27', '2025-11-30 17:36:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cli_estados`
--

CREATE TABLE `cli_estados` (
  `id_estado` int NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cli_estados`
--

INSERT INTO `cli_estados` (`id_estado`, `codigo`, `nombre`) VALUES
(1, 'EST001', 'Activo'),
(2, 'EST002', 'Inactivo'),
(3, 'EST003', 'Bloqueado'),
(4, 'EST004', 'Suspendido'),
(5, 'EST005', 'Eliminado');

-- --------------------------------------------------------

--
-- Table structure for table `cli_tipos_cliente`
--

CREATE TABLE `cli_tipos_cliente` (
  `id_tipo` int NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cli_tipos_cliente`
--

INSERT INTO `cli_tipos_cliente` (`id_tipo`, `codigo`, `nombre`) VALUES
(1, 'TIPO001', 'Persona Natural'),
(2, 'TIPO002', 'Persona Jurídica'),
(3, 'TIPO003', 'Distribuidor'),
(4, 'TIPO004', 'Gobierno'),
(5, 'TIPO005', 'Corporativo');

-- --------------------------------------------------------

--
-- Table structure for table `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int NOT NULL,
  `codigo_departamento` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_departamento` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `estado_id` int NOT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `codigo_departamento`, `nombre_departamento`, `descripcion`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'DEP001', 'Guatemala', 'Departamento de Guatemala', 1, '2025-10-26 19:31:44', NULL),
(2, 'DEP-002', 'Alta Verapaz', 'Departamento de la región norte de Guatemala', 1, '2025-10-27 17:36:27', NULL),
(3, 'DEP-003', 'Escuintla', 'Departamento de la costa sur de Guatemala', 1, '2025-10-27 17:36:27', NULL),
(4, 'DEP-004', 'Sacatepéquez', 'Departamento de la región central de Guatemala', 1, '2025-10-27 17:36:27', NULL),
(5, 'DEP-005', 'Quetzaltenango', 'Departamento del altiplano occidental de Guatemala', 1, '2025-10-27 17:36:27', NULL),
(6, 'DEP-006', 'Quiche', 'quc', 1, '2025-11-13 21:44:37', NULL),
(7, 'DEP-007', 'Quiche', 'quc', 1, '2025-11-13 21:44:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `entradas_almacen`
--

CREATE TABLE `entradas_almacen` (
  `id` int NOT NULL,
  `numero_entrada` varchar(50) NOT NULL,
  `almacen_id` int NOT NULL,
  `tipo_entrada_id` int NOT NULL,
  `fecha_entrada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `motivo` text,
  `documento_referencia` varchar(100) DEFAULT NULL COMMENT 'Factura, orden de compra, etc.',
  `usuario_registra_id` int NOT NULL,
  `usuario_autoriza_id` int DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `entradas_almacen`
--

INSERT INTO `entradas_almacen` (`id`, `numero_entrada`, `almacen_id`, `tipo_entrada_id`, `fecha_entrada`, `fecha_autorizacion`, `motivo`, `documento_referencia`, `usuario_registra_id`, `usuario_autoriza_id`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'ENT-000001', 4, 3, '2025-10-22 20:05:50', '2025-10-22 20:06:04', '', '', 1, 1, 5, '2025-10-22 20:05:50', '2025-10-22 20:06:04'),
(2, 'ENT-000002', 3, 1, '2025-10-25 19:33:52', '2025-10-25 19:34:34', 'Compra', '452154', 2, 2, 5, '2025-10-25 19:33:52', '2025-10-25 19:34:34'),
(3, 'ENT-000003', 1, 6, '2025-10-25 19:50:51', NULL, '', '', 1, NULL, 6, '2025-10-25 19:50:51', '2025-10-25 19:50:58'),
(4, 'ENT-000004', 1, 1, '2025-10-25 19:53:14', '2025-10-25 19:53:16', '', '', 1, 1, 5, '2025-10-25 19:53:14', '2025-10-25 19:53:16'),
(5, 'ENT-000005', 1, 3, '2025-10-25 21:37:27', '2025-10-25 21:37:29', '', '', 1, 1, 5, '2025-10-25 21:37:27', '2025-10-25 21:37:29'),
(6, 'ENT-000006', 1, 3, '2025-10-28 10:07:41', '2025-10-28 10:07:45', '', '', 1, 1, 5, '2025-10-28 10:07:41', '2025-10-28 10:07:45'),
(7, 'ENT-000007', 4, 3, '2025-10-28 10:13:23', '2025-10-28 10:13:31', '', '', 1, 1, 5, '2025-10-28 10:13:23', '2025-10-28 10:13:31'),
(8, 'ENT-000008', 1, 3, '2025-11-02 12:58:49', NULL, '', '', 1, NULL, 6, '2025-11-02 12:58:49', '2025-11-02 12:59:03'),
(9, 'ENT-000009', 1, 1, '2025-11-13 21:18:11', '2025-11-13 21:18:42', 'Compra por bajo stock', '4598', 1, 1, 5, '2025-11-13 21:18:11', '2025-11-13 21:18:42'),
(10, 'ENT-000010', 1, 1, '2025-11-20 20:41:03', '2025-11-20 20:42:55', '', '45873156', 1, 1, 5, '2025-11-20 20:41:03', '2025-11-20 20:42:55'),
(11, 'ENT-000011', 1, 1, '2025-11-30 17:30:13', '2025-11-30 17:30:42', '', '', 1, 1, 5, '2025-11-30 17:30:13', '2025-11-30 17:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `entradas_detalle`
--

CREATE TABLE `entradas_detalle` (
  `id` int NOT NULL,
  `entrada_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `entradas_detalle`
--

INSERT INTO `entradas_detalle` (`id`, `entrada_id`, `producto_id`, `cantidad`, `precio_unitario`, `observaciones`) VALUES
(1, 1, 1, 1.00, 4500.00, ''),
(2, 2, 1, 10.00, 4500.00, ''),
(3, 3, 2, 10.00, 0.00, ''),
(4, 4, 2, 10.00, 0.00, ''),
(5, 5, 2, 1.00, 7000.00, ''),
(6, 6, 2, 1.00, 7000.00, ''),
(7, 7, 2, 10.00, 7000.00, ''),
(8, 8, 1, 1.00, 4500.00, ''),
(9, 9, 2, 10.00, 7000.00, ''),
(10, 10, 2, 10.00, 7000.00, ''),
(11, 11, 3, 15.00, 100.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `estados`
--

CREATE TABLE `estados` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `aplica_a` varchar(100) DEFAULT NULL COMMENT 'A qué entidades aplica este estado',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `estados`
--

INSERT INTO `estados` (`id`, `nombre`, `descripcion`, `aplica_a`, `creado_en`, `actualizado_en`) VALUES
(1, 'activo', 'Registro activo y disponible', 'universal', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'inactivo', 'Registro inactivo temporalmente', 'universal', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'eliminado', 'Registro eliminado lógicamente', 'universal', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Registrada', 'Entrada registrada pendiente de autorización', NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53'),
(5, 'Autorizada', 'Entrada autorizada y procesada', NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53'),
(6, 'Cancelada', 'Entrada cancelada', NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `estados_factura`
--

CREATE TABLE `estados_factura` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `color` varchar(7) DEFAULT NULL,
  `orden` int DEFAULT '0',
  `es_final` tinyint(1) DEFAULT '0',
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `estados_factura`
--

INSERT INTO `estados_factura` (`id`, `codigo`, `nombre`, `descripcion`, `color`, `orden`, `es_final`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'BOR', 'Borrador', 'Factura en proceso de creación', '#6c757d', 1, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(2, 'EMI', 'Emitida', 'Factura emitida al cliente', '#007bff', 2, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(3, 'PEN', 'Pendiente de Pago', 'Factura con saldo pendiente', '#ffc107', 3, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 'PAR', 'Parcialmente Pagada', 'Factura con pagos parciales', '#17a2b8', 4, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(5, 'PAG', 'Pagada', 'Factura pagada completamente', '#28a745', 5, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(6, 'VEN', 'Vencida', 'Factura con pago vencido', '#dc3545', 6, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(7, 'ANU', 'Anulada', 'Factura anulada', '#343a40', 7, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `facturas`
--

CREATE TABLE `facturas` (
  `id` int NOT NULL,
  `tipo_factura_id` int NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `serie_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_impuestos` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `forma_pago_id` int NOT NULL,
  `dias_credito` int DEFAULT '0',
  `saldo_pendiente` decimal(12,2) DEFAULT '0.00',
  `almacen_id` int DEFAULT NULL,
  `vendedor_usuario_id` int DEFAULT NULL,
  `orden_compra` varchar(100) DEFAULT NULL,
  `referencia_interna` varchar(100) DEFAULT NULL,
  `estado_factura_id` int NOT NULL,
  `observaciones` text,
  `motivo_anulacion` text,
  `fecha_anulacion` datetime DEFAULT NULL,
  `usuario_anula_id` int DEFAULT NULL,
  `usuario_crea_id` int NOT NULL,
  `usuario_modifica_id` int DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lista_precios_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `facturas`
--

INSERT INTO `facturas` (`id`, `tipo_factura_id`, `numero_factura`, `serie_id`, `cliente_id`, `fecha_emision`, `fecha_vencimiento`, `subtotal`, `total_descuento`, `total_impuestos`, `total`, `forma_pago_id`, `dias_credito`, `saldo_pendiente`, `almacen_id`, `vendedor_usuario_id`, `orden_compra`, `referencia_interna`, `estado_factura_id`, `observaciones`, `motivo_anulacion`, `fecha_anulacion`, `usuario_anula_id`, `usuario_crea_id`, `usuario_modifica_id`, `fecha_registro`, `creado_en`, `actualizado_en`, `lista_precios_id`) VALUES
(1, 1, 'A001-00000002', 1, 1, '2025-11-09', NULL, 26500.00, 0.00, 0.00, 26500.00, 1, 0, 26500.00, 1, 1, '', '', 2, '', NULL, NULL, NULL, 1, NULL, '2025-11-09 11:21:49', '2025-11-09 17:21:49', '2025-11-09 17:21:49', NULL),
(2, 1, 'A001-00000003', 1, 2, '2025-11-09', NULL, 1100.00, 0.00, 0.00, 1100.00, 1, 0, 1100.00, 1, 1, '', '', 2, '', NULL, NULL, NULL, 1, NULL, '2025-11-09 16:26:06', '2025-11-09 22:26:06', '2025-11-09 22:26:06', NULL),
(3, 1, 'A001-00000004', 1, 2, '2025-11-14', NULL, 6400.00, 0.00, 0.00, 6400.00, 3, 0, 6400.00, 3, 2, '465132', '', 2, 'cliente gei', NULL, NULL, NULL, 2, NULL, '2025-11-13 21:41:40', '2025-11-14 03:41:40', '2025-11-14 03:41:40', NULL),
(4, 1, 'A001-00000005', 1, 2, '2025-11-16', NULL, 1100.00, 0.00, 0.00, 1100.00, 1, 0, 1100.00, 1, 1, '', '', 2, '', NULL, NULL, NULL, 1, NULL, '2025-11-15 22:31:42', '2025-11-16 04:31:42', '2025-11-16 04:31:42', NULL),
(5, 2, 'B001-00000002', 2, 1, '2025-11-21', NULL, 2200.00, 0.00, 0.00, 2200.00, 3, 0, 2200.00, 1, 2, '', '', 2, '', NULL, NULL, NULL, 2, NULL, '2025-11-20 20:52:21', '2025-11-21 02:52:21', '2025-11-21 02:52:21', NULL),
(6, 1, 'A001-00000006', 1, 3, '2025-11-30', NULL, 300.00, 0.00, 0.00, 300.00, 1, 0, 300.00, 1, 1, '', '', 2, '', NULL, NULL, NULL, 1, NULL, '2025-11-30 17:42:04', '2025-11-30 23:42:04', '2025-11-30 23:42:04', NULL);

--
-- Triggers `facturas`
--
DELIMITER $$
CREATE TRIGGER `trg_facturas_cambio_estado` AFTER UPDATE ON `facturas` FOR EACH ROW BEGIN
    IF OLD.estado_factura_id != NEW.estado_factura_id THEN
        INSERT INTO facturas_historial_estados 
        (factura_id, estado_anterior_id, estado_nuevo_id, usuario_id)
        VALUES 
        (NEW.id, OLD.estado_factura_id, NEW.estado_factura_id, NEW.usuario_modifica_id);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `facturas_detalle`
--

CREATE TABLE `facturas_detalle` (
  `id` int NOT NULL,
  `factura_id` int NOT NULL,
  `numero_linea` int NOT NULL,
  `tipo_item` enum('producto','servicio') NOT NULL,
  `item_id` int NOT NULL,
  `codigo_item` varchar(100) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT '0.00',
  `descuento_monto` decimal(10,2) DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL,
  `impuesto_id` int DEFAULT NULL,
  `impuesto_porcentaje` decimal(5,2) DEFAULT '0.00',
  `impuesto_monto` decimal(10,2) DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL,
  `inventario_almacen_id` int DEFAULT NULL,
  `lote` varchar(100) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `facturas_detalle`
--

INSERT INTO `facturas_detalle` (`id`, `factura_id`, `numero_linea`, `tipo_item`, `item_id`, `codigo_item`, `descripcion`, `cantidad`, `unidad_medida`, `precio_unitario`, `descuento_porcentaje`, `descuento_monto`, `subtotal`, `impuesto_id`, `impuesto_porcentaje`, `impuesto_monto`, `total`, `inventario_almacen_id`, `lote`, `fecha_vencimiento`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, 'producto', 1, 'PROD-001', 'HP Gravite mist ', 5.00, 'UND', 5300.00, 0.00, 0.00, 26500.00, NULL, 0.00, 0.00, 26500.00, NULL, NULL, NULL, '2025-11-09 17:21:49', '2025-11-09 17:21:49'),
(2, 2, 1, 'servicio', 2, 'SRV-0002', 'Mantenimiento a Laptop ( mantenimiento', 1.00, 'UND', 1100.00, 0.00, 0.00, 1100.00, NULL, 0.00, 0.00, 1100.00, NULL, NULL, NULL, '2025-11-09 22:26:06', '2025-11-09 22:26:06'),
(3, 3, 1, 'producto', 1, 'PROD-001', 'HP Gravite mist ', 1.00, 'UND', 5300.00, 0.00, 0.00, 5300.00, NULL, 0.00, 0.00, 5300.00, NULL, NULL, NULL, '2025-11-14 03:41:40', '2025-11-14 03:41:40'),
(4, 3, 2, 'servicio', 2, 'SRV-0002', 'Mantenimiento a Laptop ( mantenimiento', 1.00, 'UND', 1100.00, 0.00, 0.00, 1100.00, NULL, 0.00, 0.00, 1100.00, NULL, NULL, NULL, '2025-11-14 03:41:40', '2025-11-14 03:41:40'),
(5, 4, 1, 'producto', 2, 'PROD-002', 'Dell Latitude E-Series', 1.00, 'UND', 1100.00, 0.00, 0.00, 1100.00, NULL, 0.00, 0.00, 1100.00, NULL, NULL, NULL, '2025-11-16 04:31:42', '2025-11-16 04:31:42'),
(6, 4, 2, 'servicio', 4, 'SRV-0004', 'Mantenimiento Correctivo', 1.00, 'UND', 0.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 0.00, NULL, NULL, NULL, '2025-11-16 04:31:42', '2025-11-16 04:31:42'),
(7, 5, 1, 'producto', 2, 'PROD-002', 'Dell Latitude E-Series', 1.00, 'UND', 1100.00, 0.00, 0.00, 1100.00, NULL, 0.00, 0.00, 1100.00, NULL, NULL, NULL, '2025-11-21 02:52:21', '2025-11-21 02:52:21'),
(8, 5, 2, 'servicio', 2, 'SRV-0002', 'Mantenimiento a Laptop ( mantenimiento', 1.00, 'UND', 1100.00, 0.00, 0.00, 1100.00, NULL, 0.00, 0.00, 1100.00, NULL, NULL, NULL, '2025-11-21 02:52:21', '2025-11-21 02:52:21'),
(9, 6, 1, 'producto', 3, 'PROD-0003', 'Impresora L200', 1.00, 'UND', 300.00, 0.00, 0.00, 300.00, NULL, 0.00, 0.00, 300.00, NULL, NULL, NULL, '2025-11-30 23:42:04', '2025-11-30 23:42:04'),
(10, 6, 2, 'servicio', 5, 'SRV-0005', 'seguro', 1.00, 'UND', 0.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 0.00, NULL, NULL, NULL, '2025-11-30 23:42:04', '2025-11-30 23:42:04');

-- --------------------------------------------------------

--
-- Table structure for table `facturas_historial_estados`
--

CREATE TABLE `facturas_historial_estados` (
  `id` int NOT NULL,
  `factura_id` int NOT NULL,
  `estado_anterior_id` int DEFAULT NULL,
  `estado_nuevo_id` int NOT NULL,
  `motivo` text,
  `usuario_id` int NOT NULL,
  `fecha_cambio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facturas_pagos`
--

CREATE TABLE `facturas_pagos` (
  `id` int NOT NULL,
  `factura_id` int NOT NULL,
  `numero_pago` int NOT NULL,
  `fecha_pago` date NOT NULL,
  `forma_pago_id` int NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` text,
  `usuario_registra_id` int NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formas_pago`
--

CREATE TABLE `formas_pago` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `requiere_referencia` tinyint(1) DEFAULT '0',
  `dias_credito` int DEFAULT '0',
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `formas_pago`
--

INSERT INTO `formas_pago` (`id`, `codigo`, `nombre`, `descripcion`, `requiere_referencia`, `dias_credito`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'EFE', 'Efectivo', 'Pago en efectivo', 0, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(2, 'TAR', 'Tarjeta de Crédito/Débito', 'Pago con tarjeta', 1, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(3, 'TRA', 'Transferencia Bancaria', 'Transferencia o depósito', 1, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 'CHE', 'Cheque', 'Pago con cheque', 1, 0, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(5, 'CRE30', 'Crédito 30 días', 'Pago a crédito 30 días', 0, 30, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(6, 'CRE60', 'Crédito 60 días', 'Pago a crédito 60 días', 0, 60, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(7, 'CRE90', 'Crédito 90 días', 'Pago a crédito 90 días', 0, 90, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `impuestos`
--

CREATE TABLE `impuestos` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descripcion` text,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `impuestos`
--

INSERT INTO `impuestos` (`id`, `codigo`, `nombre`, `porcentaje`, `tipo`, `descripcion`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'IVA12', 'IVA 12%', 12.00, 'impuesto', 'Impuesto al Valor Agregado 12%', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(2, 'IVA0', 'IVA 0%', 0.00, 'impuesto', 'Producto exento de IVA', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(3, 'RET', 'Retención IVA', 1.00, 'retencion', 'Retención del 1% sobre IVA', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 'ISR', 'ISR', 5.00, 'retencion', 'Retención Impuesto Sobre la Renta 5%', 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `inventario_almacen`
--

CREATE TABLE `inventario_almacen` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `almacen_id` int NOT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `lote` varchar(50) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT CURRENT_TIMESTAMP,
  `cantidad_actual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_minima` decimal(10,2) DEFAULT '0.00',
  `cantidad_maxima` decimal(10,2) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventario_almacen`
--

INSERT INTO `inventario_almacen` (`id`, `producto_id`, `almacen_id`, `codigo_barras`, `lote`, `fecha_vencimiento`, `fecha_ingreso`, `cantidad_actual`, `cantidad_minima`, `cantidad_maxima`, `observaciones`, `usuario_modificador_id`, `actualizado_en`) VALUES
(1, 1, 1, '8901234567890', 'HV20251020', NULL, '2025-10-06 21:10:00', 0.00, 10.00, 10.00, NULL, 1, '2025-11-09 17:26:15'),
(2, 1, 4, NULL, NULL, NULL, '2025-10-20 21:10:28', 18.00, 0.00, NULL, NULL, 1, '2025-11-13 21:28:29'),
(3, 1, 3, NULL, NULL, NULL, '2025-10-22 20:01:14', 4.00, 0.00, NULL, NULL, 2, '2025-11-13 21:41:40'),
(4, 2, 1, NULL, NULL, NULL, '2025-10-25 19:53:16', 0.00, 0.00, NULL, NULL, 2, '2025-11-20 20:52:21'),
(5, 2, 4, NULL, NULL, '2025-11-15', '2025-10-25 19:56:40', 17.00, 0.00, NULL, NULL, 1, '2025-11-20 20:47:13'),
(9, 2, 3, NULL, NULL, NULL, '2025-11-15 22:44:05', 9.00, 0.00, NULL, NULL, NULL, '2025-11-20 20:47:54'),
(10, 3, 1, NULL, NULL, NULL, '2025-11-30 17:22:00', 4.00, 10.00, 20.00, NULL, 1, '2025-11-30 17:42:04'),
(11, 3, 4, NULL, NULL, NULL, '2025-11-30 17:25:54', 5.00, 0.00, NULL, NULL, NULL, '2025-11-30 17:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `listas_precios`
--

CREATE TABLE `listas_precios` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `moneda_id` int NOT NULL,
  `vigente_desde` date NOT NULL,
  `vigente_hasta` date DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listas_precios`
--

INSERT INTO `listas_precios` (`id`, `nombre`, `descripcion`, `moneda_id`, `vigente_desde`, `vigente_hasta`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Laptop', 'Lista de precio solo para laptop', 1, '2025-09-28', NULL, 1, 1, 1, '2025-10-20 21:13:10', '2025-10-20 21:13:10'),
(2, 'impresoras', NULL, 1, '2025-11-30', '2025-11-19', 1, 1, 1, '2025-11-30 17:27:51', '2025-11-30 17:27:51');

-- --------------------------------------------------------

--
-- Table structure for table `listas_precios_detalle`
--

CREATE TABLE `listas_precios_detalle` (
  `id` int NOT NULL,
  `lista_precio_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `tipo_precio_id` int NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listas_precios_detalle`
--

INSERT INTO `listas_precios_detalle` (`id`, `lista_precio_id`, `producto_id`, `tipo_precio_id`, `precio`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, 1, 4500.00, 1, 1, '2025-10-20 21:13:38', '2025-10-20 21:13:38'),
(3, 1, 1, 2, 5300.00, 1, 1, '2025-10-20 21:13:50', '2025-10-20 21:13:50'),
(4, 1, 2, 1, 7000.00, 1, 1, '2025-10-25 20:30:52', '2025-10-25 20:30:52'),
(5, 1, 2, 2, 1100.00, 1, 1, '2025-10-25 20:31:09', '2025-10-25 20:31:09'),
(6, 2, 3, 1, 100.00, 1, 1, '2025-11-30 17:28:02', '2025-11-30 17:28:02'),
(7, 2, 3, 2, 300.00, 1, 1, '2025-11-30 17:28:16', '2025-11-30 17:28:16'),
(8, 1, 3, 1, 100.00, 1, 1, '2025-11-30 17:29:31', '2025-11-30 17:29:31'),
(9, 1, 3, 2, 300.00, 1, 1, '2025-11-30 17:29:39', '2025-11-30 17:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `monedas`
--

CREATE TABLE `monedas` (
  `id` int NOT NULL,
  `codigo` varchar(3) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `simbolo` varchar(5) NOT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `monedas`
--

INSERT INTO `monedas` (`id`, `codigo`, `nombre`, `simbolo`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'GTQ', 'Quetzal Guatemalteco', 'Q', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'USD', 'Dólar Estadounidense', '$', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'EUR', 'Euro', '€', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'MCL', 'Michelin', 'DS', 1, 1, 1, '2025-10-13 00:06:16', '2025-10-13 00:06:22');

-- --------------------------------------------------------

--
-- Table structure for table `municipios`
--

CREATE TABLE `municipios` (
  `id_municipio` int NOT NULL,
  `codigo_municipio` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_municipio` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `departamento_id` int NOT NULL,
  `estado_id` int NOT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `municipios`
--

INSERT INTO `municipios` (`id_municipio`, `codigo_municipio`, `nombre_municipio`, `descripcion`, `departamento_id`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'MUN001', 'Guatemala', 'Municipio de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(2, 'MUN002', 'Santa Catarina Pinula', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(3, 'MUN003', 'San José Pinula', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(4, 'MUN004', 'San José del Golfo', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(5, 'MUN005', 'Palencia', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(6, 'MUN006', 'Chinautla', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(7, 'MUN007', 'San Pedro Ayampuc', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(8, 'MUN008', 'Mixco', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(9, 'MUN009', 'San Pedro Sacatepéquez', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(10, 'MUN010', 'San Juan Sacatepéquez', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(11, 'MUN011', 'San Raymundo', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(12, 'MUN012', 'Chuarrancho', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(13, 'MUN013', 'Fraijanes', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(14, 'MUN014', 'Amatitlán', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(15, 'MUN015', 'Villa Nueva', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(16, 'MUN016', 'Villa Canales', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(17, 'MUN017', 'San Miguel Petapa', 'Municipio del departamento de Guatemala', 1, 1, '2025-10-26 19:31:44', NULL),
(18, 'MUN018', 'Cobán', 'Cabecera departamental de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(19, 'MUN019', 'Santa Cruz Verapaz', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(20, 'MUN020', 'San Cristóbal Verapaz', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(21, 'MUN021', 'Tactic', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(22, 'MUN022', 'Tamahú', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(23, 'MUN023', 'San Miguel Tucurú', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(24, 'MUN024', 'Panzós', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(25, 'MUN025', 'Senahú', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(26, 'MUN026', 'San Pedro Carchá', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(27, 'MUN027', 'San Juan Chamelco', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(28, 'MUN028', 'Lanquín', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(29, 'MUN029', 'Santa María Cahabón', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(30, 'MUN030', 'Chisec', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(31, 'MUN031', 'Chahal', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(32, 'MUN032', 'Fray Bartolomé de las Casas', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(33, 'MUN033', 'Santa Catalina La Tinta', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(34, 'MUN034', 'Raxruhá', 'Municipio de Alta Verapaz', 2, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(35, 'MUN035', 'Escuintla', 'Cabecera departamental de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(36, 'MUN036', 'Santa Lucía Cotzumalguapa', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(37, 'MUN037', 'La Democracia', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(38, 'MUN038', 'Siquinalá', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(39, 'MUN039', 'Masagua', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(40, 'MUN040', 'Tiquisate', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(41, 'MUN041', 'La Gomera', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(42, 'MUN042', 'Guanagazapa', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(43, 'MUN043', 'San José', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(44, 'MUN044', 'Iztapa', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(45, 'MUN045', 'Palín', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(46, 'MUN046', 'San Vicente Pacaya', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(47, 'MUN047', 'Nueva Concepción', 'Municipio de Escuintla', 3, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(48, 'MUN048', 'Antigua Guatemala', 'Cabecera departamental de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(49, 'MUN049', 'Jocotenango', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(50, 'MUN050', 'Pastores', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(51, 'MUN051', 'Sumpango', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(52, 'MUN052', 'Santo Domingo Xenacoj', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(53, 'MUN053', 'Santiago Sacatepéquez', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(54, 'MUN054', 'San Bartolomé Milpas Altas', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(55, 'MUN055', 'San Lucas Sacatepéquez', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(56, 'MUN056', 'Santa Lucía Milpas Altas', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(57, 'MUN057', 'Magdalena Milpas Altas', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(58, 'MUN058', 'Santa María de Jesús', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(59, 'MUN059', 'Ciudad Vieja', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(60, 'MUN060', 'San Miguel Dueñas', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(61, 'MUN061', 'Alotenango', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(62, 'MUN062', 'San Antonio Aguas Calientes', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(63, 'MUN063', 'Santa Catarina Barahona', 'Municipio de Sacatepéquez', 4, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(64, 'MUN064', 'Quetzaltenango', 'Cabecera departamental de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(65, 'MUN065', 'Salcajá', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(66, 'MUN066', 'Olintepeque', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(67, 'MUN067', 'San Carlos Sija', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(68, 'MUN068', 'Sibilia', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(69, 'MUN069', 'Cabricán', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(70, 'MUN070', 'Cajolá', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(71, 'MUN071', 'San Miguel Sigüilá', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(72, 'MUN072', 'San Juan Ostuncalco', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(73, 'MUN073', 'San Mateo', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(74, 'MUN074', 'Concepción Chiquirichapa', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(75, 'MUN075', 'San Martín Sacatepéquez', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(76, 'MUN076', 'Almolonga', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(77, 'MUN077', 'Cantel', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(78, 'MUN078', 'Huitán', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(79, 'MUN079', 'Zunil', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(80, 'MUN080', 'Colomba Costa Cuca', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(81, 'MUN081', 'San Francisco La Unión', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(82, 'MUN082', 'El Palmar', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(83, 'MUN083', 'Coatepeque', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(84, 'MUN084', 'Génova', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(85, 'MUN085', 'Flores Costa Cuca', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(86, 'MUN086', 'La Esperanza', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35'),
(87, 'MUN087', 'Palestina de Los Altos', 'Municipio de Quetzaltenango', 5, 1, '2025-10-27 17:36:28', '2025-10-27 17:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text,
  `categoria_id` int NOT NULL,
  `unidad_medida_id` int NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `peso` decimal(10,2) DEFAULT NULL COMMENT 'Peso en kilogramos',
  `imagen_url` varchar(255) DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `categoria_id`, `unidad_medida_id`, `proveedor_id`, `peso`, `imagen_url`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'PROD-001', 'HP Gravite mist ', 'Laptop ligera y potente, ideal para trabajo y entretenimiento portátil.', 5, 1, 3, 10.00, 'assets/img/productos/68f6f8579a9e2_1.png', 1, 1, 1, '2025-10-20 21:04:55', '2025-11-02 12:23:08'),
(2, 'PROD-002', 'Dell Latitude E-Series', 'Portátil Dell Latitude de la serie E, diseñada para uso profesional, con alto rendimiento, seguridad avanzada y batería de larga duración. Ideal para tareas de oficina, videoconferencias y movilidad diaria.', 5, 1, 2, 10.00, 'assets/img/productos/6907a1a72c50e_Captura de pantalla 2025-10-30 090736.png', 1, 1, 1, '2025-10-25 19:50:02', '2025-11-02 12:23:35'),
(3, 'PROD-0003', 'Impresora L200', '', 8, 1, 1, NULL, 'assets/img/productos/692cd1891c551_Captura de pantalla 2025-07-18 110806.png', 1, 4, NULL, '2025-11-30 17:21:45', '2025-11-30 17:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text,
  `nit` varchar(20) DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`id`, `codigo`, `nombre`, `telefono`, `email`, `direccion`, `nit`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'PROV001', 'DELL', '50212345678', 'premier@dell.com', 'Zona 4, Ciudad de Guatemala', '7894561-2', 1, NULL, NULL, '2025-11-02 18:19:07', '2025-11-02 18:55:00'),
(2, 'PROV002', 'HP', '50222334455', 'support@hp.com', 'Km 18.5 Carretera al Atlántico', '4567891-3', 1, NULL, NULL, '2025-11-02 18:19:07', '2025-11-02 18:55:58'),
(3, 'PROV003', 'IMB', '50233445566', 'ibmidsupport@ibm.com', 'Centro Comercial Los Próceres, Local 12', '1234567-8', 1, NULL, NULL, '2025-11-02 18:19:07', '2025-11-02 18:56:28'),
(4, 'PROV-0004', 'Socios S.A.', '12345678', 'socios@socios.com', 'Socio landia ', '1289', 1, 1, NULL, '2025-11-09 22:06:07', '2025-11-09 22:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, 'Administrador', 'Acceso total a todo el sistema, incluyendo gestión de usuarios y configuraciones críticas', '2025-07-29 07:54:52', '2025-10-20 20:56:05'),
(2, 'Operador', 'Acceso administrativo general: puede crear, editar y eliminar registros importantes', '2025-07-29 07:54:52', '2025-10-20 20:56:05'),
(3, 'Moderador', 'Supervisa contenidos y usuarios, pero con acceso limitado a configuraciones', '2025-07-29 07:54:52', '2025-10-20 20:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `rol_usuario`
--

CREATE TABLE `rol_usuario` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `rol_id` int NOT NULL,
  `asignado_en` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rol_usuario`
--

INSERT INTO `rol_usuario` (`id`, `usuario_id`, `rol_id`, `asignado_en`) VALUES
(6, 1, 1, '2025-10-25 11:33:46'),
(7, 2, 1, '2025-10-25 11:33:52'),
(8, 3, 3, '2025-10-25 11:33:58'),
(9, 4, 1, '2025-11-30 17:17:17');

-- --------------------------------------------------------

--
-- Table structure for table `salidas_almacen`
--

CREATE TABLE `salidas_almacen` (
  `id` int NOT NULL,
  `numero_salida` varchar(50) NOT NULL,
  `almacen_id` int NOT NULL,
  `tipo_salida_id` int NOT NULL,
  `fecha_salida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `motivo` text,
  `documento_referencia` varchar(100) DEFAULT NULL COMMENT 'Factura, orden, etc.',
  `usuario_registra_id` int NOT NULL,
  `usuario_autoriza_id` int DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `salidas_almacen`
--

INSERT INTO `salidas_almacen` (`id`, `numero_salida`, `almacen_id`, `tipo_salida_id`, `fecha_salida`, `fecha_autorizacion`, `motivo`, `documento_referencia`, `usuario_registra_id`, `usuario_autoriza_id`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'SAL-000001', 3, 4, '2025-10-22 20:04:03', '2025-10-22 20:04:11', '', '', 1, 1, 5, '2025-10-22 20:04:03', '2025-10-22 20:04:11'),
(2, 'SAL-000002', 3, 5, '2025-10-25 19:35:50', '2025-10-25 19:36:13', 'Mal estado', '452154', 2, 2, 5, '2025-10-25 19:35:50', '2025-10-25 19:36:13'),
(3, 'SAL-000003', 1, 4, '2025-10-28 10:31:59', '2025-10-28 10:45:01', '', '', 1, 1, 5, '2025-10-28 10:31:59', '2025-10-28 10:45:01'),
(4, 'SAL-000004', 1, 4, '2025-10-28 10:59:07', '2025-10-28 10:59:13', 'Hola', '452154', 1, 1, 5, '2025-10-28 10:59:07', '2025-10-28 10:59:13'),
(5, 'SAL-000005', 4, 4, '2025-11-13 21:20:02', '2025-11-13 21:20:28', '', '', 1, 1, 5, '2025-11-13 21:20:02', '2025-11-13 21:20:28'),
(6, 'SAL-000006', 1, 4, '2025-11-20 20:44:16', '2025-11-20 20:44:40', '', '', 1, 1, 5, '2025-11-20 20:44:16', '2025-11-20 20:44:40'),
(7, 'SAL-000007', 1, 4, '2025-11-30 17:32:45', '2025-11-30 17:33:06', '', '', 1, 1, 5, '2025-11-30 17:32:45', '2025-11-30 17:33:06');

-- --------------------------------------------------------

--
-- Table structure for table `salidas_detalle`
--

CREATE TABLE `salidas_detalle` (
  `id` int NOT NULL,
  `salida_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `salidas_detalle`
--

INSERT INTO `salidas_detalle` (`id`, `salida_id`, `producto_id`, `cantidad`, `precio_unitario`, `observaciones`) VALUES
(1, 1, 1, 1.00, 5300.00, ''),
(2, 2, 1, 7.00, 5300.00, ''),
(3, 3, 2, 1.00, 1100.00, ''),
(4, 4, 2, 10.00, 1100.00, ''),
(5, 5, 1, 1.00, 5300.00, ''),
(6, 6, 2, 10.00, 1100.00, ''),
(7, 7, 3, 15.00, 300.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `series_facturacion`
--

CREATE TABLE `series_facturacion` (
  `id` int NOT NULL,
  `tipo_factura_id` int NOT NULL,
  `serie` varchar(20) NOT NULL,
  `numero_actual` int DEFAULT '1',
  `numero_inicio` int DEFAULT '1',
  `numero_fin` int DEFAULT '99999999',
  `almacen_id` int DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `series_facturacion`
--

INSERT INTO `series_facturacion` (`id`, `tipo_factura_id`, `serie`, `numero_actual`, `numero_inicio`, `numero_fin`, `almacen_id`, `fecha_inicio`, `fecha_fin`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'A001', 6, 1, 99999999, NULL, '2025-11-03', NULL, 1, '2025-11-04 02:18:55', '2025-11-30 23:42:04'),
(2, 2, 'B001', 2, 1, 99999999, NULL, '2025-11-03', NULL, 1, '2025-11-04 02:18:55', '2025-11-21 02:52:21'),
(3, 3, 'NC001', 1, 1, 99999999, NULL, '2025-11-03', NULL, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 4, 'ND001', 1, 1, 99999999, NULL, '2025-11-03', NULL, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_base` decimal(10,2) NOT NULL,
  `aplica_iva` tinyint(1) DEFAULT '1',
  `porcentaje_iva` decimal(5,2) DEFAULT '12.00',
  `categoria_servicio_id` int DEFAULT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `servicios`
--

INSERT INTO `servicios` (`id`, `codigo`, `nombre`, `descripcion`, `precio_base`, `aplica_iva`, `porcentaje_iva`, `categoria_servicio_id`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(2, 'SRV-0002', 'Mantenimiento a Laptop ( mantenimiento', 'El servicio de mantenimiento a laptop incluye una revisión completa del equipo para optimizar su rendimiento y prolongar su vida útil. Se realiza limpieza interna y externa, eliminación de polvo en ventiladores y disipadores, reemplazo de pasta térmica, verificación de componentes físicos (memoria RAM, disco duro, batería, teclado y pantalla), así como diagnóstico y optimización del sistema operativo.\r\n\r\nEste servicio ayuda a prevenir sobrecalentamientos, lentitud, fallos de hardware y otros problemas que afectan el funcionamiento general del equipo. Ideal para mantener la laptop en condiciones óptimas de desempeño y estabilidad.', 450.00, 1, 12.00, 5, 1, 1, 1, '2025-11-04 02:33:25', '2025-11-04 02:34:25'),
(3, 'SRV-0003', 'Reparación de Laptop', 'El servicio de reparación de laptop consiste en diagnosticar y solucionar fallas tanto de hardware como de software que impiden el correcto funcionamiento del equipo. Se atienden problemas como daños en pantalla, teclado, batería, cargador, placa base, disco duro, sistema operativo, virus o errores de arranque.\r\n\r\nNuestro objetivo es restaurar la funcionalidad total del equipo, garantizando un trabajo preciso y con repuestos de calidad, asegurando así el mejor rendimiento y durabilidad de la laptop.', 600.00, 1, 12.00, 5, 1, 1, NULL, '2025-11-04 02:34:11', '2025-11-04 02:34:11'),
(4, 'SRV-0004', 'Mantenimiento Correctivo', 'Su objetivo es evitar fallas futuras.\r\nIncluye:\r\n\r\nLimpieza interna (ventiladores, disipadores, puertos, teclado).\r\n\r\nLimpieza externa (pantalla, carcasa).\r\n\r\nCambio de pasta térmica del procesador.\r\n\r\nVerificación de temperaturas y rendimiento.\r\n\r\nActualización de controladores y sistema operativo.\r\n\r\nComprobación de batería y cargador.', 350.00, 1, 12.00, 5, 1, 1, NULL, '2025-11-04 02:35:49', '2025-11-04 02:35:49'),
(5, 'SRV-0005', 'seguro', '15 meses de seguro y reparacion por fallas', 90.00, 1, 12.00, 2, 1, 1, NULL, '2025-11-30 23:41:46', '2025-11-30 23:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_entrada`
--

CREATE TABLE `tipos_entrada` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `requiere_autorizacion` tinyint(1) DEFAULT '1',
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipos_entrada`
--

INSERT INTO `tipos_entrada` (`id`, `nombre`, `descripcion`, `requiere_autorizacion`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Compra', 'Entrada por compra a proveedor', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Devolución Cliente', 'Devolución de cliente', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'Ajuste Inventario', 'Ajuste de inventario por conteo físico', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Producción', 'Entrada por producción interna', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Otro', 'Otro tipo de entrada', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(6, 'Compra a Proveedor', 'Entrada por compra a proveedor', 1, 1, NULL, NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53'),
(7, 'Producción Interna', 'Entrada por producción interna', 0, 1, NULL, NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53'),
(8, 'Transferencia Entrada', 'Entrada por transferencia entre almacenes', 0, 1, NULL, NULL, '2025-10-20 16:06:53', '2025-10-20 16:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_factura`
--

CREATE TABLE `tipos_factura` (
  `id` int NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `prefijo` varchar(10) NOT NULL,
  `serie_actual` int DEFAULT '1',
  `requiere_nit` tinyint(1) DEFAULT '0',
  `afecta_inventario` tinyint(1) DEFAULT '1',
  `afecta_cuentas` tinyint(1) DEFAULT '1',
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipos_factura`
--

INSERT INTO `tipos_factura` (`id`, `codigo`, `nombre`, `descripcion`, `prefijo`, `serie_actual`, `requiere_nit`, `afecta_inventario`, `afecta_cuentas`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'FAC', 'Factura', 'Factura de venta estándar', 'A', 1, 0, 1, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(2, 'FCF', 'Crédito Fiscal', 'Factura con crédito fiscal (requiere NIT)', 'B', 1, 1, 1, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(3, 'NCR', 'Nota de Crédito', 'Nota de crédito (devolución o descuento)', 'NC', 1, 0, 1, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(4, 'NDB', 'Nota de Débito', 'Nota de débito (cargo adicional)', 'ND', 1, 0, 0, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55'),
(5, 'PRO', 'Proforma', 'Cotización o presupuesto', 'PRO', 1, 0, 0, 1, 1, '2025-11-04 02:18:55', '2025-11-04 02:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_movimiento`
--

CREATE TABLE `tipos_movimiento` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `afecta_inventario` tinyint(1) DEFAULT '1',
  `tipo_afectacion` varchar(20) DEFAULT NULL COMMENT 'suma, resta, ninguna',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipos_movimiento`
--

INSERT INTO `tipos_movimiento` (`id`, `nombre`, `descripcion`, `afecta_inventario`, `tipo_afectacion`, `creado_en`, `actualizado_en`) VALUES
(1, 'Entrada', 'Entrada de productos al almacén', 1, 'suma', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Salida', 'Salida de productos del almacén', 1, 'resta', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'Transferencia Salida', 'Salida por transferencia a otro almacén', 1, 'resta', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Transferencia Entrada', 'Entrada por transferencia desde otro almacén', 1, 'suma', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Ajuste Positivo', 'Ajuste que incrementa inventario', 1, 'suma', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(6, 'Ajuste Negativo', 'Ajuste que disminuye inventario', 1, 'resta', '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(7, 'Consulta', 'Consulta de inventario sin afectación', 0, 'ninguna', '2025-10-12 10:17:43', '2025-10-12 10:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_precio`
--

CREATE TABLE `tipos_precio` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipos_precio`
--

INSERT INTO `tipos_precio` (`id`, `nombre`, `descripcion`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Precio Compra', 'Precio de adquisición del producto', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Precio Venta', 'Precio de venta al público', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'Precio Mayoreo', 'Precio para venta al mayoreo', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Precio Distribuidor', 'Precio para distribuidores', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Precio Especial', 'Precio especial o promocional.', 1, 1, 1, '2025-10-12 10:17:43', '2025-10-13 00:10:13');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_salida`
--

CREATE TABLE `tipos_salida` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `requiere_autorizacion` tinyint(1) DEFAULT '1',
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipos_salida`
--

INSERT INTO `tipos_salida` (`id`, `nombre`, `descripcion`, `requiere_autorizacion`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Venta', 'Salida por venta de productos', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Consumo Interno', 'Consumo interno de la empresa', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'Merma', 'Pérdida de producto por deterioro', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Ajuste Inventario', 'Ajuste de inventario por conteo físico', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Devolución', 'Devolución a proveedor', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(6, 'Otro', 'Otro tipo de salida', 1, 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(7, 'Devolución Proveedor', 'Devolución de mercadería a proveedor', 1, 1, NULL, NULL, '2025-10-20 16:51:50', '2025-10-20 16:51:50'),
(8, 'Transferencia Salida', 'Salida por transferencia entre almacenes', 0, 1, NULL, NULL, '2025-10-20 16:51:50', '2025-10-20 16:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `transferencias`
--

CREATE TABLE `transferencias` (
  `id` int NOT NULL,
  `numero_transferencia` varchar(50) NOT NULL,
  `almacen_origen_id` int NOT NULL,
  `almacen_destino_id` int NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `transferencia_estado_id` int NOT NULL DEFAULT '1',
  `observaciones` text,
  `usuario_solicita_id` int NOT NULL,
  `usuario_autoriza_id` int DEFAULT NULL,
  `usuario_envia_id` int DEFAULT NULL,
  `usuario_recibe_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transferencias`
--

INSERT INTO `transferencias` (`id`, `numero_transferencia`, `almacen_origen_id`, `almacen_destino_id`, `fecha_solicitud`, `fecha_autorizacion`, `fecha_envio`, `fecha_recepcion`, `transferencia_estado_id`, `observaciones`, `usuario_solicita_id`, `usuario_autoriza_id`, `usuario_envia_id`, `usuario_recibe_id`, `creado_en`, `actualizado_en`) VALUES
(2, 'TRANS-000001', 1, 4, '2025-10-25 22:32:45', '2025-10-25 22:32:49', '2025-10-25 22:32:51', '2025-10-25 22:33:35', 4, '', 1, 1, 1, 2, '2025-10-25 22:32:45', '2025-10-25 22:33:35'),
(3, 'TRANS-000002', 3, 4, '2025-10-25 22:36:16', '2025-10-25 22:36:22', '2025-10-25 22:36:49', '2025-10-25 22:37:13', 4, '', 2, 2, 2, 1, '2025-10-25 22:36:16', '2025-10-25 22:37:13'),
(4, 'TRANS-000003', 1, 4, '2025-11-09 17:26:06', '2025-11-09 17:26:10', '2025-11-09 17:26:15', '2025-11-09 17:27:10', 4, '', 1, 1, 1, 2, '2025-11-09 17:26:06', '2025-11-09 17:27:10'),
(5, 'TRANS-000004', 3, 4, '2025-11-13 21:24:19', '2025-11-13 21:24:34', '2025-11-13 21:27:38', '2025-11-13 21:28:29', 4, '', 1, 1, 1, 2, '2025-11-13 21:24:19', '2025-11-13 21:28:29'),
(6, 'TRANS-000005', 1, 3, '2025-11-15 22:40:10', '2025-11-15 22:40:32', '2025-11-15 22:42:18', '2025-11-15 22:44:05', 4, '', 1, 1, 1, 2, '2025-11-15 22:40:10', '2025-11-15 22:44:05'),
(7, 'TRANS-000006', 4, 3, '2025-11-20 20:47:01', '2025-11-20 20:47:10', '2025-11-20 20:47:13', '2025-11-20 20:47:54', 4, '', 1, 1, 1, 2, '2025-11-20 20:47:01', '2025-11-20 20:47:54'),
(8, 'TRANS-000007', 1, 4, '2025-11-30 17:24:24', '2025-11-30 17:24:50', '2025-11-30 17:24:57', '2025-11-30 17:25:54', 4, '', 4, 4, 4, 1, '2025-11-30 17:24:24', '2025-11-30 17:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `transferencias_detalle`
--

CREATE TABLE `transferencias_detalle` (
  `id` int NOT NULL,
  `transferencia_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `cantidad_recibida` decimal(10,2) DEFAULT NULL COMMENT 'Cantidad realmente recibida',
  `observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transferencias_detalle`
--

INSERT INTO `transferencias_detalle` (`id`, `transferencia_id`, `producto_id`, `cantidad`, `cantidad_recibida`, `observaciones`) VALUES
(2, 2, 2, 10.00, 10.00, ''),
(3, 2, 1, 10.00, 10.00, ''),
(4, 3, 1, 10.00, 9.00, ''),
(5, 4, 1, 5.00, 5.00, ''),
(6, 5, 1, 5.00, 4.00, ''),
(7, 6, 2, 8.00, 7.00, ''),
(8, 7, 2, 3.00, 2.00, ''),
(9, 8, 3, 5.00, 5.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `transferencias_estados`
--

CREATE TABLE `transferencias_estados` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `orden` int NOT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transferencias_estados`
--

INSERT INTO `transferencias_estados` (`id`, `nombre`, `descripcion`, `orden`, `creado_en`, `actualizado_en`) VALUES
(1, 'Pendiente', 'Transferencia creada, pendiente de autorización', 1, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Autorizada', 'Transferencia autorizada, lista para envío', 2, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'En Tránsito', 'Productos en camino al almacén destino', 3, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Recibida', 'Transferencia completada y recibida', 4, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Cancelada', 'Transferencia cancelada', 5, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(6, 'Solicitada', 'Transferencia creada y pendiente de autorización', 1, '2025-10-19 21:25:07', '2025-10-19 21:25:07'),
(7, 'Enviada', 'Productos enviados del almacén origen', 3, '2025-10-19 21:25:07', '2025-10-19 21:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `unidades_medida`
--

CREATE TABLE `unidades_medida` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `abreviatura` varchar(10) NOT NULL,
  `descripcion` text,
  `estado_id` int NOT NULL DEFAULT '1',
  `usuario_creador_id` int DEFAULT NULL,
  `usuario_modificador_id` int DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `unidades_medida`
--

INSERT INTO `unidades_medida` (`id`, `nombre`, `abreviatura`, `descripcion`, `estado_id`, `usuario_creador_id`, `usuario_modificador_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Unidad', 'UND', 'Unidad individual', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(2, 'Caja', 'CJA', 'Caja contenedora', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(3, 'Kilogramo', 'KG', 'Kilogramo de peso', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(4, 'Gramo', 'GR', 'Gramo de peso', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(5, 'Litro', 'LT', 'Litro de volumen', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(6, 'Metro', 'MT', 'Metro de longitud', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(7, 'Paquete', 'PQT', 'Paquete', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43'),
(8, 'Docena', 'DOC', 'Docena (12 unidades)', 1, 1, NULL, '2025-10-12 10:17:43', '2025-10-12 10:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `estado_id` int NOT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 'Administrador', 'admin@example.com', '$2y$10$cIK3hcnSJj0Qdz/zAdhjXu2SR0JRWqdttG3jsZVS4clhtYUXHd5YK', 1, '2025-07-29 07:54:36', '2025-12-03 17:58:21'),
(2, 'Operador', 'operador@example.com', '$2y$10$1C4XtUu5iGskOknEtrQ43.7XbaC85hWD0JvOA9T85ADnp23jGTneK', 1, '2025-07-29 08:21:46', '2025-11-09 17:26:36'),
(3, 'Supervisor', 'supervisor@example.com', '$2y$10$t7MgAWtQZVuhJldCJHkn8eNIIsYk81qnHLFH59DhhrBOxPMVsR8Rm', 1, '2025-10-25 11:33:07', '2025-11-30 17:15:34'),
(4, 'Cristian Pochon', 'cristian@empresa.com', '$2y$10$2qMOkNAY391xhCP/rIYVUOdaSbECC2gAuWCjlTyZJMMo.oL.yQ6Nu', 4, '2025-11-30 17:16:08', '2025-11-30 17:43:59');

-- --------------------------------------------------------

--
-- Table structure for table `usuario_almacen`
--

CREATE TABLE `usuario_almacen` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `almacen_id` int NOT NULL,
  `estado_id` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuario_almacen`
--

INSERT INTO `usuario_almacen` (`id`, `usuario_id`, `almacen_id`, `estado_id`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, 1, '2025-10-26 00:07:16', '2025-10-26 00:42:17'),
(2, 1, 4, 1, '2025-10-26 00:23:40', '2025-10-26 00:23:40'),
(3, 2, 4, 1, '2025-10-26 00:41:25', '2025-10-26 00:41:25'),
(4, 2, 3, 1, '2025-10-26 00:52:34', '2025-10-26 00:52:34'),
(5, 1, 3, 1, '2025-11-14 03:23:35', '2025-11-14 03:23:35'),
(6, 4, 1, 1, '2025-11-30 23:18:56', '2025-11-30 23:18:56'),
(7, 4, 4, 1, '2025-11-30 23:23:47', '2025-11-30 23:23:47');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_facturas`
-- (See below for the actual view)
--
CREATE TABLE `vista_facturas` (
`id` int
,`numero_factura` varchar(50)
,`tipo_factura` varchar(100)
,`primer_nombre` varchar(100)
,`primer_apellido` varchar(100)
,`razon_social` varchar(200)
,`nit` varchar(30)
,`fecha_emision` date
,`fecha_vencimiento` date
,`subtotal` decimal(12,2)
,`total_descuento` decimal(12,2)
,`total_impuestos` decimal(12,2)
,`total` decimal(12,2)
,`forma_pago` varchar(100)
,`estado` varchar(100)
,`estado_color` varchar(7)
,`saldo_pendiente` decimal(12,2)
,`almacen` varchar(100)
,`vendedor` varchar(100)
,`observaciones` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_facturas_detalle`
-- (See below for the actual view)
--
CREATE TABLE `vista_facturas_detalle` (
`id` int
,`factura_id` int
,`numero_factura` varchar(50)
,`numero_linea` int
,`tipo_item` enum('producto','servicio')
,`codigo_item` varchar(100)
,`descripcion` varchar(500)
,`cantidad` decimal(10,2)
,`unidad_medida` varchar(50)
,`precio_unitario` decimal(10,2)
,`descuento_porcentaje` decimal(5,2)
,`descuento_monto` decimal(10,2)
,`subtotal` decimal(12,2)
,`impuesto_porcentaje` decimal(5,2)
,`impuesto_monto` decimal(10,2)
,`total` decimal(12,2)
,`nombre_item` varchar(255)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `almacenes`
--
ALTER TABLE `almacenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `responsable_usuario_id` (`responsable_usuario_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `bitacora_estados`
--
ALTER TABLE `bitacora_estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `bitacora_inventario`
--
ALTER TABLE `bitacora_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `almacen_id` (`almacen_id`),
  ADD KEY `tipo_movimiento_id` (`tipo_movimiento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fecha_movimiento` (`fecha_movimiento`),
  ADD KEY `referencia` (`referencia_tipo`,`referencia_id`);

--
-- Indexes for table `bitacora_sesiones`
--
ALTER TABLE `bitacora_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `canales_venta`
--
ALTER TABLE `canales_venta`
  ADD PRIMARY KEY (`id_canal`),
  ADD UNIQUE KEY `codigo_canal` (`codigo_canal`),
  ADD KEY `fk_canales_estado` (`estado_id`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_padre_id` (`categoria_padre_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `categorias_servicios`
--
ALTER TABLE `categorias_servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `fk_cliente_tipo_cliente` (`id_tipo_cliente`),
  ADD KEY `fk_cliente_estado` (`id_estado`),
  ADD KEY `fk_cliente_canal` (`id_canal`),
  ADD KEY `fk_cliente_departamento` (`id_departamento`),
  ADD KEY `fk_cliente_municipio` (`id_municipio`),
  ADD KEY `fk_cliente_usuario_registra` (`usuario_registra_id`),
  ADD KEY `fk_cliente_usuario_modifica` (`usuario_modifica_id`);

--
-- Indexes for table `cli_estados`
--
ALTER TABLE `cli_estados`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `cli_tipos_cliente`
--
ALTER TABLE `cli_tipos_cliente`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`),
  ADD UNIQUE KEY `codigo_departamento` (`codigo_departamento`),
  ADD KEY `fk_departamento_estado` (`estado_id`);

--
-- Indexes for table `entradas_almacen`
--
ALTER TABLE `entradas_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_entrada` (`numero_entrada`),
  ADD KEY `almacen_id` (`almacen_id`),
  ADD KEY `tipo_entrada_id` (`tipo_entrada_id`),
  ADD KEY `usuario_registra_id` (`usuario_registra_id`),
  ADD KEY `usuario_autoriza_id` (`usuario_autoriza_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indexes for table `entradas_detalle`
--
ALTER TABLE `entradas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entrada_id` (`entrada_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `estados_factura`
--
ALTER TABLE `estados_factura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `tipo_factura_id` (`tipo_factura_id`),
  ADD KEY `serie_id` (`serie_id`),
  ADD KEY `forma_pago_id` (`forma_pago_id`),
  ADD KEY `almacen_id` (`almacen_id`),
  ADD KEY `vendedor_usuario_id` (`vendedor_usuario_id`),
  ADD KEY `usuario_crea_id` (`usuario_crea_id`),
  ADD KEY `usuario_modifica_id` (`usuario_modifica_id`),
  ADD KEY `usuario_anula_id` (`usuario_anula_id`),
  ADD KEY `idx_fecha_emision` (`fecha_emision`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado_factura_id`),
  ADD KEY `idx_numero` (`numero_factura`),
  ADD KEY `idx_facturas_fecha_cliente` (`fecha_emision`,`cliente_id`),
  ADD KEY `idx_facturas_estado_fecha` (`estado_factura_id`,`fecha_emision`),
  ADD KEY `idx_facturas_saldo` (`saldo_pendiente`),
  ADD KEY `fk_facturas_listas_precios` (`lista_precios_id`);

--
-- Indexes for table `facturas_detalle`
--
ALTER TABLE `facturas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `impuesto_id` (`impuesto_id`),
  ADD KEY `inventario_almacen_id` (`inventario_almacen_id`),
  ADD KEY `idx_factura` (`factura_id`),
  ADD KEY `idx_tipo_item` (`tipo_item`,`item_id`);

--
-- Indexes for table `facturas_historial_estados`
--
ALTER TABLE `facturas_historial_estados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estado_anterior_id` (`estado_anterior_id`),
  ADD KEY `estado_nuevo_id` (`estado_nuevo_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_factura` (`factura_id`);

--
-- Indexes for table `facturas_pagos`
--
ALTER TABLE `facturas_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forma_pago_id` (`forma_pago_id`),
  ADD KEY `usuario_registra_id` (`usuario_registra_id`),
  ADD KEY `idx_factura` (`factura_id`),
  ADD KEY `idx_fecha` (`fecha_pago`),
  ADD KEY `idx_pagos_fecha` (`fecha_pago`);

--
-- Indexes for table `formas_pago`
--
ALTER TABLE `formas_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `impuestos`
--
ALTER TABLE `impuestos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_almacen` (`producto_id`,`almacen_id`),
  ADD KEY `almacen_id` (`almacen_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `listas_precios`
--
ALTER TABLE `listas_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moneda_id` (`moneda_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `listas_precios_detalle`
--
ALTER TABLE `listas_precios_detalle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lista_producto_tipo` (`lista_precio_id`,`producto_id`,`tipo_precio_id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `tipo_precio_id` (`tipo_precio_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `monedas`
--
ALTER TABLE `monedas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id_municipio`),
  ADD UNIQUE KEY `codigo_municipio` (`codigo_municipio`),
  ADD KEY `fk_municipio_departamento` (`departamento_id`),
  ADD KEY `fk_municipio_estado` (`estado_id`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `unidad_medida_id` (`unidad_medida_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`),
  ADD KEY `fk_productos_proveedores` (`proveedor_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indexes for table `salidas_almacen`
--
ALTER TABLE `salidas_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_salida` (`numero_salida`),
  ADD KEY `almacen_id` (`almacen_id`),
  ADD KEY `tipo_salida_id` (`tipo_salida_id`),
  ADD KEY `usuario_registra_id` (`usuario_registra_id`),
  ADD KEY `usuario_autoriza_id` (`usuario_autoriza_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indexes for table `salidas_detalle`
--
ALTER TABLE `salidas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salida_id` (`salida_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `series_facturacion`
--
ALTER TABLE `series_facturacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_factura_id` (`tipo_factura_id`,`serie`),
  ADD KEY `almacen_id` (`almacen_id`);

--
-- Indexes for table `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `tipos_entrada`
--
ALTER TABLE `tipos_entrada`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `tipos_factura`
--
ALTER TABLE `tipos_factura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `tipos_movimiento`
--
ALTER TABLE `tipos_movimiento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `tipos_precio`
--
ALTER TABLE `tipos_precio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `tipos_salida`
--
ALTER TABLE `tipos_salida`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `transferencias`
--
ALTER TABLE `transferencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_transferencia` (`numero_transferencia`),
  ADD KEY `almacen_origen_id` (`almacen_origen_id`),
  ADD KEY `almacen_destino_id` (`almacen_destino_id`),
  ADD KEY `transferencia_estado_id` (`transferencia_estado_id`),
  ADD KEY `usuario_solicita_id` (`usuario_solicita_id`),
  ADD KEY `usuario_autoriza_id` (`usuario_autoriza_id`),
  ADD KEY `usuario_envia_id` (`usuario_envia_id`),
  ADD KEY `usuario_recibe_id` (`usuario_recibe_id`);

--
-- Indexes for table `transferencias_detalle`
--
ALTER TABLE `transferencias_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transferencia_id` (`transferencia_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `transferencias_estados`
--
ALTER TABLE `transferencias_estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `abreviatura` (`abreviatura`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_creador_id` (`usuario_creador_id`),
  ADD KEY `usuario_modificador_id` (`usuario_modificador_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indexes for table `usuario_almacen`
--
ALTER TABLE `usuario_almacen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`usuario_id`),
  ADD KEY `fk_almacen` (`almacen_id`),
  ADD KEY `fk_estado` (`estado_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `almacenes`
--
ALTER TABLE `almacenes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bitacora_estados`
--
ALTER TABLE `bitacora_estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bitacora_inventario`
--
ALTER TABLE `bitacora_inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `bitacora_sesiones`
--
ALTER TABLE `bitacora_sesiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `canales_venta`
--
ALTER TABLE `canales_venta`
  MODIFY `id_canal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categorias_servicios`
--
ALTER TABLE `categorias_servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cli_estados`
--
ALTER TABLE `cli_estados`
  MODIFY `id_estado` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cli_tipos_cliente`
--
ALTER TABLE `cli_tipos_cliente`
  MODIFY `id_tipo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `entradas_almacen`
--
ALTER TABLE `entradas_almacen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `entradas_detalle`
--
ALTER TABLE `entradas_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `estados_factura`
--
ALTER TABLE `estados_factura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `facturas_detalle`
--
ALTER TABLE `facturas_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `facturas_historial_estados`
--
ALTER TABLE `facturas_historial_estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facturas_pagos`
--
ALTER TABLE `facturas_pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `formas_pago`
--
ALTER TABLE `formas_pago`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `impuestos`
--
ALTER TABLE `impuestos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `listas_precios`
--
ALTER TABLE `listas_precios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `listas_precios_detalle`
--
ALTER TABLE `listas_precios_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `monedas`
--
ALTER TABLE `monedas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id_municipio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rol_usuario`
--
ALTER TABLE `rol_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salidas_almacen`
--
ALTER TABLE `salidas_almacen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salidas_detalle`
--
ALTER TABLE `salidas_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `series_facturacion`
--
ALTER TABLE `series_facturacion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tipos_entrada`
--
ALTER TABLE `tipos_entrada`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tipos_factura`
--
ALTER TABLE `tipos_factura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tipos_movimiento`
--
ALTER TABLE `tipos_movimiento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tipos_precio`
--
ALTER TABLE `tipos_precio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tipos_salida`
--
ALTER TABLE `tipos_salida`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transferencias_detalle`
--
ALTER TABLE `transferencias_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transferencias_estados`
--
ALTER TABLE `transferencias_estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuario_almacen`
--
ALTER TABLE `usuario_almacen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- --------------------------------------------------------

--
-- Structure for view `vista_facturas`
--
DROP TABLE IF EXISTS `vista_facturas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_facturas`  AS SELECT `f`.`id` AS `id`, `f`.`numero_factura` AS `numero_factura`, `tf`.`nombre` AS `tipo_factura`, `c`.`primer_nombre` AS `primer_nombre`, `c`.`primer_apellido` AS `primer_apellido`, `c`.`razon_social` AS `razon_social`, `c`.`nit` AS `nit`, `f`.`fecha_emision` AS `fecha_emision`, `f`.`fecha_vencimiento` AS `fecha_vencimiento`, `f`.`subtotal` AS `subtotal`, `f`.`total_descuento` AS `total_descuento`, `f`.`total_impuestos` AS `total_impuestos`, `f`.`total` AS `total`, `fp`.`nombre` AS `forma_pago`, `ef`.`nombre` AS `estado`, `ef`.`color` AS `estado_color`, `f`.`saldo_pendiente` AS `saldo_pendiente`, `a`.`nombre` AS `almacen`, `u`.`nombre` AS `vendedor`, `f`.`observaciones` AS `observaciones` FROM ((((((`facturas` `f` join `tipos_factura` `tf` on((`f`.`tipo_factura_id` = `tf`.`id`))) join `clientes` `c` on((`f`.`cliente_id` = `c`.`id_cliente`))) join `formas_pago` `fp` on((`f`.`forma_pago_id` = `fp`.`id`))) join `estados_factura` `ef` on((`f`.`estado_factura_id` = `ef`.`id`))) left join `almacenes` `a` on((`f`.`almacen_id` = `a`.`id`))) left join `usuarios` `u` on((`f`.`vendedor_usuario_id` = `u`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vista_facturas_detalle`
--
DROP TABLE IF EXISTS `vista_facturas_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_facturas_detalle`  AS SELECT `fd`.`id` AS `id`, `fd`.`factura_id` AS `factura_id`, `f`.`numero_factura` AS `numero_factura`, `fd`.`numero_linea` AS `numero_linea`, `fd`.`tipo_item` AS `tipo_item`, `fd`.`codigo_item` AS `codigo_item`, `fd`.`descripcion` AS `descripcion`, `fd`.`cantidad` AS `cantidad`, `fd`.`unidad_medida` AS `unidad_medida`, `fd`.`precio_unitario` AS `precio_unitario`, `fd`.`descuento_porcentaje` AS `descuento_porcentaje`, `fd`.`descuento_monto` AS `descuento_monto`, `fd`.`subtotal` AS `subtotal`, `fd`.`impuesto_porcentaje` AS `impuesto_porcentaje`, `fd`.`impuesto_monto` AS `impuesto_monto`, `fd`.`total` AS `total`, (case when (`fd`.`tipo_item` = 'producto') then `p`.`nombre` when (`fd`.`tipo_item` = 'servicio') then `s`.`nombre` end) AS `nombre_item` FROM (((`facturas_detalle` `fd` join `facturas` `f` on((`fd`.`factura_id` = `f`.`id`))) left join `productos` `p` on(((`fd`.`tipo_item` = 'producto') and (`fd`.`item_id` = `p`.`id`)))) left join `servicios` `s` on(((`fd`.`tipo_item` = 'servicio') and (`fd`.`item_id` = `s`.`id`)))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `almacenes`
--
ALTER TABLE `almacenes`
  ADD CONSTRAINT `fk_almacenes_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_almacenes_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_almacenes_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_almacenes_responsable` FOREIGN KEY (`responsable_usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `bitacora_inventario`
--
ALTER TABLE `bitacora_inventario`
  ADD CONSTRAINT `fk_bitacora_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_bitacora_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_bitacora_tipo_movimiento` FOREIGN KEY (`tipo_movimiento_id`) REFERENCES `tipos_movimiento` (`id`),
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `bitacora_sesiones`
--
ALTER TABLE `bitacora_sesiones`
  ADD CONSTRAINT `bitacora_sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `canales_venta`
--
ALTER TABLE `canales_venta`
  ADD CONSTRAINT `fk_canales_estado` FOREIGN KEY (`estado_id`) REFERENCES `bitacora_estados` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_categorias_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_categorias_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_categorias_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias` (`id`);

--
-- Constraints for table `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cliente_canal` FOREIGN KEY (`id_canal`) REFERENCES `canales_venta` (`id_canal`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_estado` FOREIGN KEY (`id_estado`) REFERENCES `cli_estados` (`id_estado`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_municipio` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_tipo_cliente` FOREIGN KEY (`id_tipo_cliente`) REFERENCES `cli_tipos_cliente` (`id_tipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_usuario_modifica` FOREIGN KEY (`usuario_modifica_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cliente_usuario_registra` FOREIGN KEY (`usuario_registra_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `fk_departamento_estado` FOREIGN KEY (`estado_id`) REFERENCES `bitacora_estados` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `entradas_almacen`
--
ALTER TABLE `entradas_almacen`
  ADD CONSTRAINT `fk_entradas_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_entradas_autoriza` FOREIGN KEY (`usuario_autoriza_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_entradas_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_entradas_registra` FOREIGN KEY (`usuario_registra_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_entradas_tipo` FOREIGN KEY (`tipo_entrada_id`) REFERENCES `tipos_entrada` (`id`);

--
-- Constraints for table `entradas_detalle`
--
ALTER TABLE `entradas_detalle`
  ADD CONSTRAINT `fk_entradas_det_entrada` FOREIGN KEY (`entrada_id`) REFERENCES `entradas_almacen` (`id`),
  ADD CONSTRAINT `fk_entradas_det_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Constraints for table `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`tipo_factura_id`) REFERENCES `tipos_factura` (`id`),
  ADD CONSTRAINT `facturas_ibfk_10` FOREIGN KEY (`usuario_anula_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`serie_id`) REFERENCES `series_facturacion` (`id`),
  ADD CONSTRAINT `facturas_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `facturas_ibfk_4` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`),
  ADD CONSTRAINT `facturas_ibfk_5` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `facturas_ibfk_6` FOREIGN KEY (`vendedor_usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `facturas_ibfk_7` FOREIGN KEY (`estado_factura_id`) REFERENCES `estados_factura` (`id`),
  ADD CONSTRAINT `facturas_ibfk_8` FOREIGN KEY (`usuario_crea_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `facturas_ibfk_9` FOREIGN KEY (`usuario_modifica_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_facturas_listas_precios` FOREIGN KEY (`lista_precios_id`) REFERENCES `listas_precios` (`id`);

--
-- Constraints for table `facturas_detalle`
--
ALTER TABLE `facturas_detalle`
  ADD CONSTRAINT `facturas_detalle_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facturas_detalle_ibfk_2` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`),
  ADD CONSTRAINT `facturas_detalle_ibfk_3` FOREIGN KEY (`inventario_almacen_id`) REFERENCES `inventario_almacen` (`id`);

--
-- Constraints for table `facturas_historial_estados`
--
ALTER TABLE `facturas_historial_estados`
  ADD CONSTRAINT `facturas_historial_estados_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`),
  ADD CONSTRAINT `facturas_historial_estados_ibfk_2` FOREIGN KEY (`estado_anterior_id`) REFERENCES `estados_factura` (`id`),
  ADD CONSTRAINT `facturas_historial_estados_ibfk_3` FOREIGN KEY (`estado_nuevo_id`) REFERENCES `estados_factura` (`id`),
  ADD CONSTRAINT `facturas_historial_estados_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `facturas_pagos`
--
ALTER TABLE `facturas_pagos`
  ADD CONSTRAINT `facturas_pagos_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`),
  ADD CONSTRAINT `facturas_pagos_ibfk_2` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`),
  ADD CONSTRAINT `facturas_pagos_ibfk_3` FOREIGN KEY (`usuario_registra_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  ADD CONSTRAINT `fk_inventario_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_inventario_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_inventario_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Constraints for table `listas_precios`
--
ALTER TABLE `listas_precios`
  ADD CONSTRAINT `fk_listas_precios_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_listas_precios_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_listas_precios_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_listas_precios_moneda` FOREIGN KEY (`moneda_id`) REFERENCES `monedas` (`id`);

--
-- Constraints for table `listas_precios_detalle`
--
ALTER TABLE `listas_precios_detalle`
  ADD CONSTRAINT `fk_precios_det_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_precios_det_lista` FOREIGN KEY (`lista_precio_id`) REFERENCES `listas_precios` (`id`),
  ADD CONSTRAINT `fk_precios_det_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_precios_det_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_precios_det_tipo` FOREIGN KEY (`tipo_precio_id`) REFERENCES `tipos_precio` (`id`);

--
-- Constraints for table `monedas`
--
ALTER TABLE `monedas`
  ADD CONSTRAINT `fk_monedas_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_monedas_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_monedas_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `fk_municipio_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_municipio_estado` FOREIGN KEY (`estado_id`) REFERENCES `bitacora_estados` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_productos_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_productos_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_productos_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_productos_proveedores` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `fk_productos_unidad` FOREIGN KEY (`unidad_medida_id`) REFERENCES `unidades_medida` (`id`);

--
-- Constraints for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `bitacora_estados` (`id`);

--
-- Constraints for table `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD CONSTRAINT `rol_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_usuario_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salidas_almacen`
--
ALTER TABLE `salidas_almacen`
  ADD CONSTRAINT `fk_salidas_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_salidas_autoriza` FOREIGN KEY (`usuario_autoriza_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_salidas_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_salidas_registra` FOREIGN KEY (`usuario_registra_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_salidas_tipo` FOREIGN KEY (`tipo_salida_id`) REFERENCES `tipos_salida` (`id`);

--
-- Constraints for table `salidas_detalle`
--
ALTER TABLE `salidas_detalle`
  ADD CONSTRAINT `fk_salidas_det_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_salidas_det_salida` FOREIGN KEY (`salida_id`) REFERENCES `salidas_almacen` (`id`);

--
-- Constraints for table `series_facturacion`
--
ALTER TABLE `series_facturacion`
  ADD CONSTRAINT `series_facturacion_ibfk_1` FOREIGN KEY (`tipo_factura_id`) REFERENCES `tipos_factura` (`id`),
  ADD CONSTRAINT `series_facturacion_ibfk_2` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`);

--
-- Constraints for table `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `servicios_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `servicios_ibfk_2` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `servicios_ibfk_3` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `tipos_entrada`
--
ALTER TABLE `tipos_entrada`
  ADD CONSTRAINT `fk_tipos_entrada_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_tipos_entrada_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_tipos_entrada_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `tipos_precio`
--
ALTER TABLE `tipos_precio`
  ADD CONSTRAINT `fk_tipos_precio_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_tipos_precio_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_tipos_precio_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `tipos_salida`
--
ALTER TABLE `tipos_salida`
  ADD CONSTRAINT `fk_tipos_salida_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_tipos_salida_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_tipos_salida_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `transferencias`
--
ALTER TABLE `transferencias`
  ADD CONSTRAINT `fk_transferencias_autoriza` FOREIGN KEY (`usuario_autoriza_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_transferencias_destino` FOREIGN KEY (`almacen_destino_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_transferencias_envia` FOREIGN KEY (`usuario_envia_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_transferencias_estado` FOREIGN KEY (`transferencia_estado_id`) REFERENCES `transferencias_estados` (`id`),
  ADD CONSTRAINT `fk_transferencias_origen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`),
  ADD CONSTRAINT `fk_transferencias_recibe` FOREIGN KEY (`usuario_recibe_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_transferencias_solicita` FOREIGN KEY (`usuario_solicita_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `transferencias_detalle`
--
ALTER TABLE `transferencias_detalle`
  ADD CONSTRAINT `fk_transferencias_det_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_transferencias_det_transferencia` FOREIGN KEY (`transferencia_id`) REFERENCES `transferencias` (`id`);

--
-- Constraints for table `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD CONSTRAINT `fk_unidades_creador` FOREIGN KEY (`usuario_creador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_unidades_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_unidades_modificador` FOREIGN KEY (`usuario_modificador_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `bitacora_estados` (`id`);

--
-- Constraints for table `usuario_almacen`
--
ALTER TABLE `usuario_almacen`
  ADD CONSTRAINT `fk_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
