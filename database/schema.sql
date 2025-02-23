-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS fitness_club;
USE fitness_club;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('member', 'admin') DEFAULT 'member',
    status ENUM('active', 'inactive') DEFAULT 'active',
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Membership plans table
CREATE TABLE membership_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT NOT NULL, -- in months
    price DECIMAL(10,2) NOT NULL,
    features JSON,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- User memberships table
CREATE TABLE user_memberships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    plan_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES membership_plans(id)
);

-- Trainers table
CREATE TABLE trainers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    bio TEXT,
    photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    trainer_id INT,
    capacity INT NOT NULL,
    duration INT NOT NULL, -- in minutes
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

-- Class schedules table
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    schedule_id INT,
    booking_date DATE NOT NULL,
    status ENUM('booked', 'cancelled', 'completed') DEFAULT 'booked',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (name, email, password, role, status)
VALUES (
    'Admin User',
    'admin@fitnessclub.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    'active'
);

-- Insert sample membership plans
INSERT INTO membership_plans (name, description, duration, price, features) VALUES
('Basic Plan', 'Perfect for beginners', 1, 29.99, '["Access to gym equipment", "Locker room access", "Fitness consultation"]'),
('Standard Plan', 'Most popular choice', 3, 49.99, '["All Basic features", "Group classes", "Personal trainer (2 sessions)", "Nutrition guidance"]'),
('Premium Plan', 'Ultimate fitness experience', 12, 99.99, '["All Standard features", "Unlimited personal training", "Spa access", "Priority booking", "Free supplements"]');

-- Create indexes for better performance
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_booking_date ON bookings(booking_date);
CREATE INDEX idx_schedule_day ON schedules(day_of_week);
CREATE INDEX idx_user_membership_dates ON user_memberships(start_date, end_date);