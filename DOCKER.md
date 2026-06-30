# Menjalankan dengan Docker

Stack: **PHP 7.4 + Apache** (app), **MariaDB 10.6** (database, auto-import `clinic.sql`), **phpMyAdmin**.

## Jalankan
```bash
docker compose up -d --build
```
Tunggu sampai DB sehat (sekitar 10–20 detik saat pertama kali — proses import `clinic.sql`).

| Layanan | URL | Keterangan |
|---|---|---|
| Aplikasi | http://localhost:8080 | Login: `admin` / `password` |
| phpMyAdmin | http://localhost:8081 | Server `db`, user `clinic` / `clinic` (atau root/`root`) |
| MySQL (dari host) | `127.0.0.1:3307` | DB `clinic_db` |

## Perintah lain
```bash
docker compose logs -f app     # lihat log app
docker compose down            # stop (data DB tetap tersimpan di volume)
docker compose down -v         # stop + HAPUS data DB (reset, import ulang clinic.sql)
docker compose exec db mysql -uclinic -pclinic clinic_db   # masuk MySQL
```

## Catatan
- Kode di-*mount* (bind volume), jadi edit file langsung kebaca tanpa rebuild.
- DB pertama kali diimport dari `database/clinic.sql`. Kalau ubah schema dan ingin re-import, jalankan `docker compose down -v` lalu `up` lagi.
- Konfigurasi DB & `base_url` dibaca dari environment (lihat `docker-compose.yml`). Tanpa Docker (XAMPP) tetap pakai default lokal; produksi pakai `application/config/production/`.
- Versi PHP disamakan dengan server (7.4) agar perilaku konsisten.
