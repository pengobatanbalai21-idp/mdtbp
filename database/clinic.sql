-- =============================================================
-- Klinik Management System - Database Schema
-- CodeIgniter 3 + MySQL
-- Password default semua akun: "password"
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `clinic_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `clinic_db`;

-- -------------------------------------------------------------
-- Tabel: users
-- -------------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)  NOT NULL,
  `username`   VARCHAR(50)   NOT NULL,
  `email`      VARCHAR(100)  DEFAULT NULL,
  `password`   VARCHAR(255)  NOT NULL,
  `role`       ENUM('admin','pimpinan','user') NOT NULL DEFAULT 'user',
  `jabatan`    ENUM('kepala_klinik','perawat','magang') NOT NULL DEFAULT 'perawat',
  `status`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: attendance
-- -------------------------------------------------------------
CREATE TABLE `attendance` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)       NOT NULL,
  `date`       DATE          NOT NULL,
  `clock_in`   DATETIME      DEFAULT NULL,
  `clock_out`  DATETIME      DEFAULT NULL,
  `work_hours` DECIMAL(5,2)  DEFAULT 0.00,
  `status`     ENUM('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `notes`      TEXT          DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance` (`user_id`,`date`),
  CONSTRAINT `fk_att_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: medicines
-- 1 unit perban = 1 paket = 10 lembar
-- -------------------------------------------------------------
CREATE TABLE `medicines` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)  NOT NULL,
  `unit`       VARCHAR(30)   NOT NULL,
  `stock`      INT(11)       NOT NULL DEFAULT 0,
  `price`      DECIMAL(10,2) NOT NULL,
  `min_stock`  INT(11)       DEFAULT 5,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: packages
-- -------------------------------------------------------------
CREATE TABLE `packages` (
  `id`          INT(11)       NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)  NOT NULL,
  `description` TEXT          DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: package_items
-- -------------------------------------------------------------
CREATE TABLE `package_items` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `package_id`  INT(11) NOT NULL,
  `medicine_id` INT(11) NOT NULL,
  `quantity`    INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pi_package`  FOREIGN KEY (`package_id`)  REFERENCES `packages`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: sales
-- -------------------------------------------------------------
CREATE TABLE `sales` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)       NOT NULL,
  `sale_type`    ENUM('package','item') NOT NULL,
  `reference_id` INT(11)       NOT NULL,
  `patient_name` VARCHAR(100)  DEFAULT NULL,
  `quantity`     INT(11)       DEFAULT 1,
  `total_price`  DECIMAL(10,2) NOT NULL,
  `notes`        TEXT          DEFAULT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: sale_details
-- -------------------------------------------------------------
CREATE TABLE `sale_details` (
  `id`          INT(11)       NOT NULL AUTO_INCREMENT,
  `sale_id`     INT(11)       NOT NULL,
  `medicine_id` INT(11)       NOT NULL,
  `quantity`    INT(11)       NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sd_sale`     FOREIGN KEY (`sale_id`)     REFERENCES `sales`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sd_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel: finances
-- -------------------------------------------------------------
CREATE TABLE `finances` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `type`         ENUM('income','expense') NOT NULL,
  `category`     VARCHAR(50)   DEFAULT NULL,
  `amount`       DECIMAL(10,2) NOT NULL,
  `description`  TEXT          DEFAULT NULL,
  `date`         DATE          NOT NULL,
  `reference_id` INT(11)       DEFAULT NULL,
  `created_by`   INT(11)       NOT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_fin_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- DATA SEED
-- =============================================================

-- Default users  (password: "password")
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `jabatan`) VALUES
('Administrator',   'admin',    'admin@klinik.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    'kepala_klinik'),
('Pimpinan Klinik', 'pimpinan', 'pimpinan@klinik.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pimpinan', 'kepala_klinik'),
('Budi Santoso',    'budi',     'budi@klinik.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',     'perawat'),
('Siti Magang',     'siti',     'siti@klinik.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',     'magang');

-- Medicines  (perban: 1 unit = 1 paket = 10 lembar)
INSERT INTO `medicines` (`name`, `unit`, `stock`, `price`, `min_stock`) VALUES
('Perban',     'paket (10 pcs)', 50,  45000.00, 10),
('Heparin',    'pcs',           100,  20000.00, 15),
('Zat Besi',   'pcs',           100,  20000.00, 15),
('Betadine',   'pcs',            80,  20000.00, 10),
('Painkiller', 'pcs',            80,  20000.00, 10);

-- Packages
INSERT INTO `packages` (`name`, `description`, `price`) VALUES
('Paket Pusing',  '10 perban (1 paket), 1 heparin, 1 zat besi',                              85000.00),
('Paket Lengkap', '10 perban (1 paket), 1 heparin, 1 zat besi, 1 betadine, 1 painkiller', 125000.00);

-- Package items: Paket Pusing (id=1)
INSERT INTO `package_items` (`package_id`, `medicine_id`, `quantity`) VALUES
(1, 1, 1), (1, 2, 1), (1, 3, 1);

-- Package items: Paket Lengkap (id=2)
INSERT INTO `package_items` (`package_id`, `medicine_id`, `quantity`) VALUES
(2, 1, 1), (2, 2, 1), (2, 3, 1), (2, 4, 1), (2, 5, 1);

-- =============================================================
-- Tabel tambahan: dipakai kode (Leave_model, Activity_log_model)
-- tapi belum ada di dump awal clinic.sql
-- =============================================================

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11)      NOT NULL,
  `type`        VARCHAR(20)  NOT NULL DEFAULT 'izin',
  `start_date`  DATE         NOT NULL,
  `end_date`    DATE         NOT NULL,
  `days`        INT(11)      NOT NULL DEFAULT 1,
  `reason`      TEXT         DEFAULT NULL,
  `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `review_note` TEXT         DEFAULT NULL,
  `reviewed_by` INT(11)      DEFAULT NULL,
  `reviewed_at` DATETIME     DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_user` (`user_id`),
  KEY `idx_leave_status` (`status`),
  CONSTRAINT `fk_leave_user`     FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leave_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11)      DEFAULT NULL,
  `user_name`   VARCHAR(100) DEFAULT NULL,
  `action`      VARCHAR(50)  NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_act_user` (`user_id`),
  KEY `idx_act_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
