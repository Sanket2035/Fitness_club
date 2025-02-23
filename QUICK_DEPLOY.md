# Quick Deployment Guide for Fitness Club Website

## 1. Quick Start (Shared Hosting)
```bash
# 1. Upload Files
- Upload all files to your hosting via FTP
- Ensure file structure remains intact

# 2. Create Database
- Access hosting control panel (cPanel/Plesk)
- Create new MySQL database
- Create database user and assign permissions
- Import database/schema.sql

# 3. Configure Website
- Edit includes/config.php with database credentials
- Update SITE_URL in config.php
- Set file permissions (usually 755 for folders, 644 for files)
```

## 2. Quick Start (VPS/Dedicated Server)
```bash
# 1. Install Requirements
sudo apt update
sudo apt install apache2 php mysql-server php-mysql

# 2. Upload Files
git clone [repository-url] /var/www/html/fitness-club
# OR upload via FTP

# 3. Database Setup
mysql -u root -p
CREATE DATABASE fitness_club;
USE fitness_club;
source database/schema.sql;

# 4. Configure Virtual Host
sudo nano /etc/apache2/sites-available/fitness-club.conf
# Add virtual host configuration
sudo a2ensite fitness-club.conf
sudo systemctl restart apache2
```

## 3. Default Credentials
```
Admin Panel:
URL: your-domain.com/admin
Username: admin@admin.com
Password: admin123

Database:
Default DB Name: fitness_club
Default DB User: fitness_user
Default DB Pass: your_password
```

## 4. Post-Installation
- Change default admin password
- Update site settings in admin panel
- Test all forms and functions
- Enable SSL certificate
- Set up backup schedule

## 5. Common Issues
1. White Screen
   - Check PHP error log
   - Verify PHP version (7.4+)
   - Check file permissions

2. Database Connection Error
   - Verify config.php credentials
   - Check database server status
   - Confirm database user permissions

3. Image Upload Issues
   - Check upload directory permissions
   - Verify PHP upload limits

## Need Help?
- Documentation: docs/
- Support: support@your-domain.com
- Emergency: +1234567890