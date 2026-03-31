# Deploy Laravel Video Player Dashboard to fernandozucula.com

This guide will help you deploy the Laravel Video Player Dashboard application to https://fernandozucula.com.

## Prerequisites

- Ubuntu/Debian server with SSH access
- LAMP/LEMP stack (Apache/Nginx, PHP 8.1+, MySQL)
- Composer installed
- SSL certificate for fernandozucula.com

## Step 1: Server Setup

### Install Required Packages

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bzip2 php8.2-intl php8.2-tidy unzip curl git

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Database Setup

```bash
# Create database and user
mysql -u root -p < deployment/database_setup.sql
```

## Step 2: Application Deployment

### Clone and Setup Application

```bash
# Navigate to web root
cd /var/www/

# Clone the repository
git clone <your-repository-url> fernandozucula.com
cd fernandozucula.com

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### Environment Configuration

```bash
# Copy production environment file
cp deployment/production.env .env

# Edit the .env file with your actual values
nano .env
```

**Important: Update these values in `.env`:**
- `APP_KEY`: Generate with `php artisan key:generate --show`
- `DB_PASSWORD`: Set your actual database password
- `VIDEO_CLIENT_API_KEY`: Generate a strong API key
- `APP_API_KEY`: Generate a strong app key
- `MAIL_PASSWORD`: Set your SMTP password

### Generate Application Key

```bash
php artisan key:generate
```

## Step 3: File Permissions

```bash
# Set proper permissions
chmod +x deployment/permissions.sh
sudo ./deployment/permissions.sh
```

## Step 4: Web Server Configuration

### Apache Configuration

```bash
# Copy Apache config
sudo cp deployment/apache.conf /etc/apache2/sites-available/fernandozucula.com.conf

# Enable site and modules
sudo a2ensite fernandozucula.com.conf
sudo a2enmod rewrite ssl headers

# Restart Apache
sudo systemctl restart apache2
```

### Nginx Configuration

```bash
# Copy Nginx config
sudo cp deployment/nginx.conf /etc/nginx/sites-available/fernandozucula.com

# Enable site
sudo ln -s /etc/nginx/sites-available/fernandozucula.com /etc/nginx/sites-enabled/

# Test and restart Nginx
sudo nginx -t
sudo systemctl restart nginx
```

## Step 5: Laravel Optimization

```bash
# Run optimization script
chmod +x deployment/optimize.sh
./deployment/optimize.sh
```

## Step 6: SSL Certificate

### Using Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # or python3-certbot-nginx

# Get SSL certificate
sudo certbot --apache -d fernandozucula.com -d www.fernandozucula.com
# or
sudo certbot --nginx -d fernandozucula.com -d www.fernandozucula.com
```

## Step 7: Create Admin User

### Via Browser (Hardcoded Route)

1. Open https://fernandozucula.com/master-admin-setup in your browser
2. The system will automatically create a superadmin with:
   - **Email:** masteradmin@zucula.com
   - **Password:** 20002004
3. You will see a JSON response confirming creation
4. Go to https://fernandozucula.com/login and use these credentials

**Important:** This route only works once. If the admin user already exists, it will return an error.

### Via Command Line (if terminal access is available)

```bash
# Create superadmin user
php artisan make:superadmin admin@fernandozucula.com --password="your_strong_password"
```

## Step 8: Verify Deployment

1. Open https://fernandozucula.com in your browser
2. Login with the admin credentials created
3. Verify all features are working

## Maintenance Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Application
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Backup Database
```bash
mysqldump -u fernandozucula_user -p fernandozucula_video > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Security Recommendations

1. **Regular Updates**: Keep PHP, Laravel, and server packages updated
2. **Firewall**: Configure UFW or similar firewall
3. **Monitoring**: Set up log monitoring and alerts
4. **Backups**: Implement automated daily backups
5. **SSL**: Ensure SSL certificate auto-renewal is configured

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**: Check file permissions and `.env` configuration
2. **Database Connection**: Verify database credentials and MySQL service
3. **SSL Issues**: Check certificate paths and Apache/Nginx configuration
4. **Cache Issues**: Clear all caches and re-run optimization

### Log Locations

- Apache: `/var/log/apache2/fernandozucula.com_error.log`
- Nginx: `/var/log/nginx/fernandozucula.com_error.log`
- Laravel: `/var/www/fernandozucula.com/storage/logs/laravel.log`

## Support

For deployment issues, check:
1. Server logs
2. Laravel logs
3. Web server configuration
4. File permissions
5. Environment variables
