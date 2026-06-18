-- Maji - Water Billing Management System
-- Database Schema

CREATE DATABASE IF NOT EXISTS maji_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maji_billing;

-- Admin users
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System settings (price per unit)
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    house_number VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    meter_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Monthly meter readings & bills
CREATE TABLE meter_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reading_month TINYINT NOT NULL CHECK (reading_month BETWEEN 1 AND 12),
    reading_year SMALLINT NOT NULL,
    previous_reading DECIMAL(12, 2) NOT NULL DEFAULT 0,
    current_reading DECIMAL(12, 2) NOT NULL,
    consumption DECIMAL(12, 2) NOT NULL DEFAULT 0,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    bill_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_month (customer_id, reading_month, reading_year)
);

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reading_id INT DEFAULT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'mobile_money', 'bank', 'other') DEFAULT 'cash',
    reference_number VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (reading_id) REFERENCES meter_readings(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Default admin: username=admin, password=admin123
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$E30mJsk8sWHwtaTe61rsJO/Ae5Nv1q73II50UNFuXbkadsWrsqQ32', 'Msimamizi Mkuu');

-- Default price per unit (TZS per cubic meter / unit)
INSERT INTO settings (setting_key, setting_value) VALUES
('price_per_unit', '500'),
('company_name', 'Maji Majumbani'),
('company_address', 'Dar es Salaam, Tanzania'),
('company_phone', '+255 700 000 000');
