#!/bin/bash

# File permissions setup for Laravel on fernandozucula.com
# Run this after deploying files to the server

echo "🔐 Setting up file permissions for Laravel..."

# Set ownership (replace www-data with your web server user)
echo "👤 Setting file ownership..."
sudo chown -R www-data:www-data /var/www/fernandozucula.com

# Set directory permissions
echo "📁 Setting directory permissions..."
sudo find /var/www/fernandozucula.com -type d -exec chmod 755 {} \;

# Set file permissions
echo "📄 Setting file permissions..."
sudo find /var/www/fernandozucula.com -type f -exec chmod 644 {} \;

# Set special permissions for storage and cache
echo "🗂️ Setting storage and cache permissions..."
sudo chmod -R 775 /var/www/fernandozucula.com/storage
sudo chmod -R 775 /var/www/fernandozucula.com/bootstrap/cache

# Set executable permissions for artisan
echo "⚙️ Setting artisan permissions..."
sudo chmod +x /var/www/fernandozucula.com/artisan

# Set permissions for optimization scripts
echo "📜 Setting script permissions..."
sudo chmod +x /var/www/fernandozucula.com/deployment/*.sh

# Create necessary directories if they don't exist
echo "📂 Creating necessary directories..."
sudo mkdir -p /var/www/fernandozucula.com/storage/app/public
sudo mkdir -p /var/www/fernandozucula.com/storage/framework/cache
sudo mkdir -p /var/www/fernandozucula.com/storage/framework/sessions
sudo mkdir -p /var/www/fernandozucula.com/storage/framework/testing
sudo mkdir -p /var/www/fernandozucula.com/storage/framework/views
sudo mkdir -p /var/www/fernandozucula.com/storage/logs

# Set permissions for new directories
sudo chmod -R 775 /var/www/fernandozucula.com/storage

echo "✅ File permissions setup complete!"
echo "🎯 Laravel files are ready for production on fernandozucula.com"
