#!/bin/sh
# activate maintenance mode
php artisan down || true

# pull the latest code from your main branch
git pull origin main

# install production dependencies without development packages
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# run live database migrations automatically
php artisan migrate --force

# clear old configurations and cache the updates
php artisan config:cache
php artisan route:cache
php artisan view:cache

# turn off maintenance mode to bring the site live
php artisan up

# send the current remaining slot availability email after deployment
php artisan slots:send-availability-emails

echo "🚀 Deployment successfully completed!"
