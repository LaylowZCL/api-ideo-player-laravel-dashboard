#!/bin/bash

# Complete deployment script for fernandozucula.com
# This script automates the entire deployment process

set -e  # Exit on any error

echo "🚀 Starting deployment of Laravel Video Player Dashboard to fernandozucula.com..."

# Configuration
PROJECT_PATH="/var/www/fernandozucula.com"
WEB_USER="www-data"
SERVICE_NAME="apache2"  # Change to "nginx" if using Nginx

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   exit 1
fi

# Step 1: Update system packages
print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Step 2: Install required packages
print_status "Installing required packages..."
sudo apt install -y apache2 mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bzip2 php8.2-intl php8.2-tidy unzip curl git composer

# Step 3: Setup database
print_status "Setting up database..."
if [ -f "deployment/database_setup.sql" ]; then
    sudo mysql -u root < deployment/database_setup.sql
else
    print_error "Database setup file not found"
    exit 1
fi

# Step 4: Create project directory
print_status "Creating project directory..."
sudo mkdir -p $PROJECT_PATH
sudo chown $USER:$USER $PROJECT_PATH

# Step 5: Copy application files
print_status "Copying application files..."
rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' --exclude='storage/logs/*' ./ $PROJECT_PATH/

# Step 6: Install dependencies
print_status "Installing PHP dependencies..."
cd $PROJECT_PATH
composer install --optimize-autoloader --no-dev

# Step 7: Setup environment
print_status "Setting up environment..."
if [ -f "deployment/production.env" ]; then
    cp deployment/production.env .env
    print_warning "Please edit .env file with your actual values before continuing"
    print_warning "Press Enter to continue after editing .env file..."
    read -r
else
    print_error "Production environment file not found"
    exit 1
fi

# Step 8: Generate application key
print_status "Generating application key..."
php artisan key:generate

# Step 9: Set file permissions
print_status "Setting file permissions..."
sudo ./deployment/permissions.sh

# Step 10: Setup web server
print_status "Setting up web server configuration..."
if [ "$SERVICE_NAME" = "apache2" ]; then
    sudo cp deployment/apache.conf /etc/apache2/sites-available/fernandozucula.com.conf
    sudo a2ensite fernandozucula.com.conf
    sudo a2enmod rewrite ssl headers
    sudo systemctl restart apache2
elif [ "$SERVICE_NAME" = "nginx" ]; then
    sudo cp deployment/nginx.conf /etc/nginx/sites-available/fernandozucula.com
    sudo ln -sf /etc/nginx/sites-available/fernandozucula.com /etc/nginx/sites-enabled/
    sudo nginx -t && sudo systemctl restart nginx
fi

# Step 11: Optimize Laravel
print_status "Optimizing Laravel for production..."
./deployment/optimize.sh

# Step 12: Setup SSL with Let's Encrypt
print_status "Setting up SSL certificate..."
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d fernandozucula.com -d www.fernandozucula.com --non-interactive --agree-tos --email admin@fernandozucula.com

# Step 13: Create admin user
print_status "Creating admin user..."
print_warning "You will be prompted to enter admin credentials"
php artisan make:superadmin

# Step 14: Setup cron job for Laravel scheduler
print_status "Setting up Laravel scheduler..."
(crontab -l 2>/dev/null; echo "* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Step 15: Final verification
print_status "Running final verification..."
if curl -s -o /dev/null -w "%{http_code}" https://fernandozucula.com | grep -q "200"; then
    print_status "✅ Deployment successful! Application is accessible at https://fernandozucula.com"
else
    print_warning "⚠️  Deployment completed but application may not be accessible. Please check logs."
fi

# Step 16: Display important information
echo ""
echo "🎉 Deployment Complete!"
echo "=========================="
echo "📁 Project Path: $PROJECT_PATH"
echo "🌐 URL: https://fernandozucula.com"
echo "📧 Admin: Login with credentials created during deployment"
echo ""
echo "📋 Important Commands:"
echo "  • View logs: sudo tail -f /var/log/apache2/fernandozucula.com_error.log"
echo "  • Clear cache: cd $PROJECT_PATH && php artisan cache:clear"
echo "  • Update: cd $PROJECT_PATH && git pull && composer install --no-dev"
echo ""
echo "🔐 Security Reminders:"
echo "  • Update all passwords in .env file"
echo "  • Configure firewall"
echo "  • Set up backups"
echo "  • Monitor logs regularly"
echo ""
print_status "Deployment script completed successfully!"
