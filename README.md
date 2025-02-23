# 🏋️‍♂️ Fitness Club Website

A comprehensive web application for managing a modern fitness club, providing end-to-end solutions for membership management, class scheduling, trainer coordination, and member engagement.

![Fitness Club Banner](images/home.jpg)

## ✨ Features

### 🔐 User Management
- Secure member registration and authentication system
- Comprehensive profile management with photo upload
- Flexible membership plan subscription options
- Interactive class booking system with capacity management
- Personal training schedule tracking
- Booking history and membership status
- Password reset functionality

### 👨‍💼 Admin Panel
- Complete member management with activity tracking
- Dynamic class and schedule management
- Trainer profiles and assignment system
- Customizable membership plan management
- Contact message handling with email notifications
- Analytics dashboard with key metrics
- Booking and attendance tracking
- Revenue reporting system

### 🌐 Public Features
- Interactive class schedule viewing
- Detailed trainer profiles and specializations
- Transparent membership plan comparison
- Secure contact form with spam protection
- Rich about us section with gym history
- Image gallery of facilities
- Newsletter subscription
- Social media integration

## 🛠 Technical Stack

### Frontend
- HTML5 with semantic markup
- CSS3 with Bootstrap 5.1.3 framework
- JavaScript (ES6+) with jQuery 3.6.0
- Font Awesome 6.0.0 for icons
- DataTables for dynamic table management
- WOW.js for scroll animations
- Chart.js for admin analytics
- SweetAlert2 for beautiful alerts
- Responsive design for all devices

### Backend
- PHP 7.4+ with OOP principles
- MySQL 5.7+ database
- PDO for secure database operations
- PHPMailer for email handling
- Session-based authentication
- MVC architecture pattern

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependencies)
- Git

### Step-by-Step Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/fitness-club.git
cd fitness-club
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create and configure the database:
```bash
# Create database
mysql -u root -p
CREATE DATABASE fitness_club;
exit;

# Import schema
mysql -u your_username -p fitness_club < database/schema.sql
```

4. Configure the application:
```bash
# Copy configuration template
cp includes/config.sample.php includes/config.php

# Edit configuration with your details
nano includes/config.php
```

5. Set up file permissions:
```bash
# Create upload directories
mkdir -p uploads/{trainers,classes,members}

# Set proper permissions
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```

6. Configure web server:
- For Apache: Enable mod_rewrite and ensure .htaccess is working
- For Nginx: Configure server block with PHP-FPM

7. Set up scheduled tasks:
```bash
# Add to crontab
crontab -e

# Add these lines
0 0 * * * php /path/to/fitness-club/cron/daily_cleanup.php
0 1 * * * php /path/to/fitness-club/cron/membership_reminder.php
```

## Default Admin Access

- Email: admin@fitnessclub.com
- Password: password

## Directory Structure

```
fitness-club/
├── admin/             # Admin panel files
├── css/              # CSS files
├── database/         # Database schema
├── images/           # Static images
├── includes/         # PHP includes
├── js/              # JavaScript files
├── member/          # Member area files
├── uploads/         # User uploads
└── index.php        # Main entry point
```

## 🔒 Security Features

### Authentication & Authorization
- Secure password hashing with bcrypt
- Role-based access control (RBAC)
- Session timeout management
- Remember me functionality
- Failed login attempt tracking
- Password strength enforcement

### Data Protection
- PDO prepared statements
- CSRF token protection
- XSS prevention
- SQL injection protection
- Input sanitization and validation
- Secure file upload handling

### System Security
- Rate limiting on sensitive endpoints
- Security headers configuration
- Error logging and monitoring
- Secure session handling
- SSL/TLS enforcement
- Regular security updates

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support & Contact

### Technical Support
- Email: support@fitnessclub.com
- GitHub Issues: [Create an issue](https://github.com/yourusername/fitness-club/issues)
- Documentation: [Wiki](https://github.com/yourusername/fitness-club/wiki)

### Business Inquiries
- Email: business@fitnessclub.com
- Phone: +1 (555) 123-4567
- Website: https://fitnessclub.com/contact

## 👏 Acknowledgments

- Bootstrap team for their excellent framework
- Font Awesome for the comprehensive icon set
- jQuery team for the robust library
- Chart.js team for the visualization tools
- DataTables team for the table functionality
- All open-source contributors
- Our amazing community of users and testers

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for all notable changes.

## 🗺 Roadmap

See our [project roadmap](https://github.com/yourusername/fitness-club/projects) for planned features and improvements.

## 📊 Stats
![GitHub Stars](https://img.shields.io/github/stars/yourusername/fitness-club)
![GitHub Forks](https://img.shields.io/github/forks/yourusername/fitness-club)
![GitHub Issues](https://img.shields.io/github/issues/yourusername/fitness-club)