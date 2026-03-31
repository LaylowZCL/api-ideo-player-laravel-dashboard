#!/bin/bash

# Laravel Production Optimization Script for fernandozucula.com
# Run this after deploying to production

echo "🚀 Optimizing Laravel for production..."

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 Creating storage symbolic link..."
php artisan storage:link

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Optimize autoloader
echo "📦 Optimizing autoloader..."
composer dump-autoload --optimize

# Clear and warmup caches
echo "🔥 Warming up caches..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set production environment
echo "🏭 Setting production environment..."
php artisan env:set APP_ENV production

echo "✅ Laravel optimization complete!"
echo "🎯 Application is ready for production on fernandozucula.com"
