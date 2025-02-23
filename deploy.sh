#!/bin/bash

# Fitness Club Website Deployment Script
# This script automates the deployment process of the Fitness Club website

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
print_message() {
    echo -e "${2}${1}${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if running with root privileges
if [ "$EUID" -ne 0 ]; then 
    print_message "Please run as root or with sudo" "$RED"
    exit 1
fi

# Welcome message
print_message "Starting Fitness Club Website Deployment..." "$GREEN"
print_message "----------------------------------------" "$GREEN"

# Check requirements
print_message "Checking requirements..." "$YELLOW"

# Check for required commands
REQUIRED_COMMANDS="apache2 php mysql"
for cmd in $REQUIRED_COMMANDS; do
    if ! command_exists $cmd; then
        print_message "Installing $cmd..." "$YELLOW"
        apt-get install -y $cmd
    fi
done

# Create directory structure
print_message "Creating directory structure..." "$YELLOW"
INSTALL_DIR="/var/www/html/fitness-club"
mkdir -p $INSTALL_DIR
mkdir -p $INSTALL_DIR/uploads
mkdir -p $INSTALL_DIR/temp

# Set permissions
print_message "Setting file permissions..." "$YELLOW"
chown -R www-data:www-data $INSTALL_DIR
find $INSTALL_DIR -type d -exec chmod 755 {} \;
find $INSTALL_DIR -type f -exec chmod 644 {} \;
chmod -R 775 $INSTALL_DIR/uploads
chmod -R 775 $INSTALL_DIR/temp

# Database setup
print_message "Setting up database..." "$YELLOW"
read -p "Enter MySQL root password: " MYSQL_ROOT_PASS
read -p "Enter new database name (default: fitness_club): " DB_NAME
DB_NAME=${DB_NAME:-fitness_club}
read -p "Enter new database user (default: fitness_user): " DB_USER
DB_USER=${DB_USER:-fitness_user}
read -p "Enter new database password: " DB_PASS

# Create database and user
mysql -uroot -p$MYSQL_ROOT_PASS <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# Import database schema
if [ -f "database/schema.sql" ]; then
    mysql -u$DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
    print_message "Database schema imported successfully" "$GREEN"
else
    print_message "Warning: schema.sql not found!" "$RED"
fi

# Update configuration file
print_message "Updating configuration..." "$YELLOW"
read -p "Enter website URL (e.g., https://example.com): " SITE_URL

CONFIG_FILE="$INSTALL_DIR/includes/config.php"
sed -i "s/DB_HOST = .*/DB_HOST = 'localhost';/" $CONFIG_FILE
sed -i "s/DB_NAME = .*/DB_NAME = '$DB_NAME';/" $CONFIG_FILE
sed -i "s/DB_USER = .*/DB_USER = '$DB_USER';/" $CONFIG_FILE
sed -i "s/DB_PASS = .*/DB_PASS = '$DB_PASS';/" $CONFIG_FILE
sed -i "s|SITE_URL = .*|SITE_URL = '$SITE_URL';|" $CONFIG_FILE

# Configure Apache
print_message "Configuring Apache..." "$YELLOW"
cat > /etc/apache2/sites-available/fitness-club.conf <<EOF
<VirtualHost *:80>
    ServerName $SITE_URL
    DocumentRoot $INSTALL_DIR
    
    <Directory $INSTALL_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/fitness-club-error.log
    CustomLog \${APACHE_LOG_DIR}/fitness-club-access.log combined
</VirtualHost>
EOF

# Enable site and modules
a2ensite fitness-club.conf
a2enmod rewrite
systemctl restart apache2

# Run deployment checker
print_message "Running deployment checker..." "$YELLOW"
php $INSTALL_DIR/deploy_check.php > deployment_results.html

# Final steps
print_message "----------------------------------------" "$GREEN"
print_message "Deployment completed!" "$GREEN"
print_message "Please check deployment_results.html for detailed status" "$GREEN"
print_message "Don't forget to:" "$YELLOW"
print_message "1. Set up SSL certificate" "$YELLOW"
print_message "2. Change default admin password" "$YELLOW"
print_message "3. Configure backup system" "$YELLOW"
print_message "4. Remove deploy.sh and deploy_check.php from production" "$YELLOW"
print_message "----------------------------------------" "$GREEN"