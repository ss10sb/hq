#!/usr/bin/env sh

php artisan dusk:chrome-driver
chmod -R 0755 /app/vendor/laravel/dusk/bin/
#php artisan serve --host=0.0.0.0 --env=dusk.local
APP_ENV=dusk.local /usr/sbin/apache2ctl -D FOREGROUND
