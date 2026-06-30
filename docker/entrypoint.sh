#!/bin/sh
set -e

# Pastikan folder yang ditulis CI bisa ditulis Apache (www-data),
# berjalan setelah volume ter-mount sehingga juga berlaku untuk bind mount.
mkdir -p /var/www/html/application/logs /var/www/html/application/cache
# Hanya foldernya (bukan -R) supaya tidak mengubah mode file ter-track spt index.html
chmod 0777 /var/www/html/application/logs /var/www/html/application/cache 2>/dev/null || true

# Lanjutkan ke entrypoint asli image php (menjalankan apache2-foreground)
exec docker-php-entrypoint "$@"
