-- =============================================================
-- MIGRASI: multi clock-in/out + recap semua role + checklist verifikasi
-- Jalankan SEKALI di database PRODUKSI (mis. lewat phpMyAdmin).
-- Aman diulang — semua pakai IF [NOT] EXISTS.
-- Untuk install baru / Docker tidak perlu ini (sudah ada di clinic.sql).
-- =============================================================

-- 1) Absensi boleh lebih dari 1 baris per hari:
--    ganti UNIQUE (user_id,date) jadi index biasa.
--    (tambah index pengganti DULU supaya foreign key tetap punya index, baru drop unique)
ALTER TABLE `attendance` ADD  INDEX IF NOT EXISTS `idx_att_user_date` (`user_id`,`date`);
ALTER TABLE `attendance` DROP INDEX IF EXISTS `uq_attendance`;

-- 2) Kolom verifikasi/checklist
ALTER TABLE `p3k_wd`
    ADD COLUMN IF NOT EXISTS `checked_by` INT(11)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `checked_at` DATETIME DEFAULT NULL;

ALTER TABLE `sales`
    ADD COLUMN IF NOT EXISTS `checked_by` INT(11)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `checked_at` DATETIME DEFAULT NULL;

-- 3) Tabel verifikasi rekap kehadiran mingguan (per user x minggu ISO)
CREATE TABLE IF NOT EXISTS `recap_checks` (
  `id`         INT(11)  NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)  NOT NULL,
  `tahun`      INT(11)  NOT NULL,
  `minggu`     INT(11)  NOT NULL,
  `checked_by` INT(11)  DEFAULT NULL,
  `checked_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recap` (`user_id`,`tahun`,`minggu`),
  CONSTRAINT `fk_recap_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recap_checker` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
