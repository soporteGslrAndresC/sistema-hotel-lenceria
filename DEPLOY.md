# Despliegue en VPS Ubuntu

Sistema de control de lencería hotelera (Laravel 11 + Reverb).
Antes de desplegar verifica que en local funcione el flujo completo (`php artisan serve`, `php artisan reverb:start`, `php artisan queue:work`, `php artisan schedule:work`).

---

## 0. Pruebas locales (resumen)

```bash
composer install
npm install
cp .env.example .env   # si fuera necesario
php artisan key:generate
php artisan migrate:fresh --seed
npm run build

# 4 procesos en paralelo:
php artisan serve                # http://127.0.0.1:8000
php artisan reverb:start         # ws://127.0.0.1:8080
php artisan queue:work
php artisan schedule:work
```

**Credenciales del seeder**:

- Admin: `admin@hotel.test` / `password`
- Empleados: `ana@hotel.test`, `bruno@hotel.test`, `carla@hotel.test` / `password`

---

## 1. Requisitos del VPS

- Ubuntu 22.04+
- PHP 8.3 + extensiones (`mbstring`, `xml`, `bcmath`, `curl`, `mysql`, `gd`, `zip`, `intl`, `sqlite3`)
- Composer 2.x
- Node.js 20+ (para `npm run build`)
- MySQL 8 o MariaDB 10.6+
- Nginx
- Supervisor
- Certbot

```bash
sudo apt update && sudo apt install -y nginx mysql-server supervisor certbot python3-certbot-nginx \
  php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-mysql \
  php8.3-gd php8.3-zip php8.3-intl php8.3-sqlite3 unzip git
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt install -y nodejs
```

## 2. Clonar y preparar

```bash
sudo mkdir -p /var/www && sudo chown $USER:$USER /var/www
cd /var/www
git clone <REPO_URL> sistemaHotelLenceria
cd sistemaHotelLenceria

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
```

Editar `.env`:

```ini
APP_NAME="Hotel Lencería"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_lenceria
DB_USERNAME=hotel_user
DB_PASSWORD=*****

QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=624179
REVERB_APP_KEY=4fei5nvhrhcod6h1dnz9
REVERB_APP_SECRET=9jp4ckzp0gwjmdqcuhd4
REVERB_HOST="tu-dominio.com"
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

> Después de cambiar variables `VITE_*` debes volver a ejecutar `npm run build`.

## 3. Crear base MySQL

```sql
CREATE DATABASE hotel_lenceria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hotel_user'@'localhost' IDENTIFIED BY '*****';
GRANT ALL ON hotel_lenceria.* TO 'hotel_user'@'localhost';
FLUSH PRIVILEGES;
```

```bash
php artisan migrate --force
php artisan db:seed --force      # opcional: crea admin/empleados de prueba
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 4. Nginx (`/etc/nginx/sites-available/hotel-lenceria`)

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/sistemaHotelLenceria/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }

    # Reverb (WebSocket) bajo el mismo dominio en /app y /apps
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_read_timeout 60m;
    }
    location /apps/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/hotel-lenceria /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## 5. SSL con Certbot

```bash
sudo certbot --nginx -d tu-dominio.com
```

> **El escáner QR del navegador móvil sólo funciona con HTTPS.** Sin SSL la cámara no se activa.

## 6. Supervisor — colas, Reverb y scheduler

`/etc/supervisor/conf.d/hotel-lenceria.conf`:

```ini
[program:hotel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sistemaHotelLenceria/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/hotel-queue.log
stopwaitsecs=3600

[program:hotel-reverb]
process_name=%(program_name)s
command=php /var/www/sistemaHotelLenceria/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/hotel-reverb.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Cron del scheduler (cada minuto):

```bash
sudo crontab -u www-data -e
```

```cron
* * * * * cd /var/www/sistemaHotelLenceria && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Despliegues posteriores

```bash
cd /var/www/sistemaHotelLenceria
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo supervisorctl restart hotel-queue hotel-reverb
```

## 8. Checklist post-deploy

- [ ] `https://tu-dominio.com/login` carga el formulario.
- [ ] Login admin → `/admin` muestra el tablero.
- [ ] Cambiar habitación a `en_limpieza` → aparece en panel del empleado del turno.
- [ ] Empleado escanea QR (cámara funciona en HTTPS) → admin recibe actualización en tiempo real.
- [ ] `wss://tu-dominio.com/app/...` se conecta sin errores (DevTools → Network → WS).
- [ ] `php artisan schedule:list` muestra el comando `app:revisar-habitaciones-lentas`.
