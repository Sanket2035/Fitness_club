# Fitness Club Website Deployment Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Installation Steps](#installation-steps)
4. [Database Setup](#database-setup)
5. [Configuration](#configuration)
6. [File Permissions](#file-permissions)
7. [Security Measures](#security-measures)
8. [Troubleshooting](#troubleshooting)

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate
- Composer (PHP package manager)
- Git (optional, for version control)

## Server Requirements
```
- PHP Extensions:
  - PDO
  - PDO_MYSQL
  - GD
  - mbstring
  - xml
  - curl
- Memory Limit: 128M or higher
- Max Upload Size: 10M or higher
- Max Execution Time: 30 seconds or higher
```

## Installation Steps

### 1. Server Setup
```bash
# For Apache
sudo apt update
sudo apt install apache2 php mysql-server php-mysql
sudo a2enmod rewrite
sudo systemctl restart apache2

# For Nginx
sudo apt update
sudo apt install nginx php-fpm mysql-server php-mysql
```

### 2. Clone/Upload Files
```bash
# Using Git
git clone [repository-url] /var/www/html/fitness-club

# Using FTP
# Upload all files to your web server's root directory
```

### 3. Directory Structure
Ensure your directory structure matches:
```
/var/www/html/fitness-club/
├── admin/
├── includes/
├── member/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── database/
└── [other files]
```

## Database Setup

### 1. Create Database
```sql
CREATE DATABASE fitness_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Schema
```bash
mysql -u username -p fitness_club < database/schema.sql
```

### 3. Create Database User
```sql
CREATE USER 'fitness_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON fitness_club.* TO 'fitness_user'@'localhost';
FLUSH PRIVILEGES;
```

## Configuration

### 1. Update Config File
Edit includes/config.php:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fitness_club');
define('DB_USER', 'fitness_user');
define('DB_PASS', 'your_strong_password');
define('SITE_URL', 'https://your-domain.com');
```

### 2. Apache Configuration
Create/edit .htaccess:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name your-domain.com;
    root /var/www/html/fitness-club;
    index index.php index.html;

    # SSL configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }
}
```

## File Permissions
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/fitness-club

# Set proper permissions
sudo find /var/www/html/fitness-club -type f -exec chmod 644 {} \;
sudo find /var/www/html/fitness-club -type d -exec chmod 755 {} \;

# Make specific directories writable
sudo chmod -R 775 /var/www/html/fitness-club/uploads
sudo chmod -R 775 /var/www/html/fitness-club/temp
```

## Security Measures

### 1. PHP Configuration
Edit php.ini:
```ini
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
max_execution_time = 30
max_input_time = 60
memory_limit = 128M
post_max_size = 10M
upload_max_filesize = 10M
```

### 2. MySQL Security
```sql
-- Change root password
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_strong_password';

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
```

### 3. SSL Configuration
```bash
# Install Let's Encrypt
sudo apt install certbot
sudo certbot --apache # For Apache
sudo certbot --nginx  # For Nginx
```

## Troubleshooting

### Common Issues and Solutions

1. **500 Internal Server Error**
- Check error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- Verify file permissions
- Check PHP configuration

2. **Database Connection Issues**
- Verify database credentials in config.php
- Check MySQL service status: `systemctl status mysql`
- Verify database user permissions

3. **Upload Issues**
- Check upload directory permissions
- Verify PHP upload limits in php.ini
- Check for disk space issues

### Maintenance

1. **Regular Backups**
```bash
# Database backup
mysqldump -u username -p fitness_club > backup.sql

# Files backup
tar -czf fitness_club_files.tar.gz /var/www/html/fitness-club
```

2. **Log Rotation**
```bash
# Add to /etc/logrotate.d/fitness-club
/var/www/html/fitness-club/logs/*.log {
    weekly
    rotate 52
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
}
```

## Post-Deployment Checklist
- [ ] Test all forms and functionality
- [ ] Verify email sending functionality
- [ ] Check mobile responsiveness
- [ ] Verify SSL certificate
- [ ] Test payment integration
- [ ] Monitor error logs
- [ ] Set up backup schedule
- [ ] Configure monitoring tools

## Support
For additional support or questions, contact:
- Technical Support: support@your-domain.com
- Emergency Contact: +1234567890