-- =============================================================
-- MIGRASI: fitur Rekap Tagihan (denda absensi + tagihan penjualan)
-- Jalankan SEKALI di database (mis. lewat phpMyAdmin).
-- Aman diulang — semua pakai IF [NOT] EXISTS.
-- =============================================================

-- 1) Status lunas pada transaksi penjualan (tagihan penjualan)
ALTER TABLE `sales`
    ADD COLUMN IF NOT EXISTS `is_paid`      TINYINT(1)   NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `paid_at`      DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `paid_by`      INT(11)      DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `payment_note` VARCHAR(255) DEFAULT NULL;

-- 2) Tabel status lunas denda absensi mingguan (per user x minggu ISO)
CREATE TABLE IF NOT EXISTS `weekly_fine_payments` (
  `id`      INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id` INT(11)      NOT NULL,
  `year`    SMALLINT     NOT NULL,
  `week`    TINYINT      NOT NULL,
  `is_paid` TINYINT(1)   NOT NULL DEFAULT 0,
  `paid_at` DATETIME     DEFAULT NULL,
  `paid_by` INT(11)      DEFAULT NULL,
  `note`    VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wfp` (`user_id`, `year`, `week`),
  CONSTRAINT `fk_wfp_user`    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wfp_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `weekly_fine_payments`
    ADD COLUMN IF NOT EXISTS `note` VARCHAR(255) DEFAULT NULL;
