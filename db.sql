-- =====================================================
-- AARAMBH by HeyyGuru — Database Schema
-- Domain: aarambh.heyyguru.in
-- =====================================================

CREATE DATABASE IF NOT EXISTS aarambh_heyyguru
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE aarambh_heyyguru;

-- ---------------------------------------------------
-- Students / Leads Table
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    student_class VARCHAR(20) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    status ENUM('lead', 'payment_initiated', 'paid', 'enrolled') DEFAULT 'lead',
    call_status ENUM('not_called', 'called', 'follow_up', 'converted', 'not_interested') DEFAULT 'not_called',
    razorpay_order_id VARCHAR(100) DEFAULT NULL,
    razorpay_payment_id VARCHAR(100) DEFAULT NULL,
    utm_source VARCHAR(100) DEFAULT NULL,
    utm_medium VARCHAR(100) DEFAULT NULL,
    utm_campaign VARCHAR(100) DEFAULT NULL,
    utm_content VARCHAR(100) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    email_sent TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_phone (phone),
    INDEX idx_status (status),
    INDEX idx_call_status (call_status),
    INDEX idx_created (created_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Payments Table
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    razorpay_order_id VARCHAR(100) NOT NULL,
    razorpay_payment_id VARCHAR(100) DEFAULT NULL,
    razorpay_signature VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 19.00,
    currency VARCHAR(10) DEFAULT 'INR',
    status ENUM('created', 'authorized', 'captured', 'failed', 'refunded') DEFAULT 'created',
    method VARCHAR(50) DEFAULT NULL,
    error_code VARCHAR(100) DEFAULT NULL,
    error_description TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_order (razorpay_order_id),
    INDEX idx_payment (razorpay_payment_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Page Visits / Analytics Table
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS page_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    referrer TEXT DEFAULT NULL,
    page_url VARCHAR(255) DEFAULT NULL,
    utm_source VARCHAR(100) DEFAULT NULL,
    utm_medium VARCHAR(100) DEFAULT NULL,
    utm_campaign VARCHAR(100) DEFAULT NULL,
    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_visited (visited_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Admin Users Table
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin@123 — CHANGE THIS!)
INSERT INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: This hash is for 'password'. Run: php -r "echo password_hash('admin@123', PASSWORD_DEFAULT);" to generate yours.
