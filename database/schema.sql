-- FitLife Winnipeg CMS Database Schema
-- Drop tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    category_icon VARCHAR(50),
    color_code VARCHAR(7) DEFAULT '#007bff',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Classes Table (Main Content Table)
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(150) NOT NULL,
    class_description TEXT NOT NULL,
    instructor_name VARCHAR(100) NOT NULL,
    duration_minutes INT DEFAULT 60,
    difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced', 'All Levels') DEFAULT 'All Levels',
    max_participants INT DEFAULT 20,
    current_enrolled INT DEFAULT 0,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    class_location ENUM('Downtown', 'St. Vital') NOT NULL,
    room_number VARCHAR(20),
    equipment_needed VARCHAR(200),
    calories_burned_avg INT,
    category_id INT,
    is_active BOOLEAN DEFAULT 1,
    is_featured BOOLEAN DEFAULT 0,
    instructor_image_path VARCHAR(255),
    slug VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_day (day_of_week),
    INDEX idx_location (class_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    user_role ENUM('admin', 'member') DEFAULT 'member',
    membership_type VARCHAR(50),
    membership_start_date DATE,
    fitness_goals TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews/Comments Table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    user_id INT NULL,
    member_name VARCHAR(100) NOT NULL,
    review_rating TINYINT CHECK (review_rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    difficulty_accurate BOOLEAN DEFAULT 1,
    would_recommend BOOLEAN DEFAULT 1,
    is_approved BOOLEAN DEFAULT 0,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_class (class_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, user_password, full_name, email, user_role, is_active) 
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System Administrator',
    'admin@fitlifewinnipeg.com',
    'admin',
    1
);