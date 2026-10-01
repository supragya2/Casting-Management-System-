
CREATE DATABASE IF NOT EXISTS newcastflow;
USE newcastflow;

CREATE TABLE designer (
    designer_id INT AUTO_INCREMENT PRIMARY KEY,
    designer_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    password VARCHAR(255),
    experience VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE model (
    model_id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100),
    email VARCHAR(150),
    password VARCHAR(255),
    phone VARCHAR(20),
    gender VARCHAR(20),
    age INT,
    height VARCHAR(20),
    weight VARCHAR(20),
    experience VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE design (
    design_id INT AUTO_INCREMENT PRIMARY KEY,
    designer_id INT,
    design_name VARCHAR(150),
    image VARCHAR(255),
    fabric VARCHAR(100),
    size VARCHAR(50),
    type VARCHAR(100),
    description TEXT
);

CREATE TABLE photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT,
    image VARCHAR(255),
    description VARCHAR(255)
);

CREATE TABLE model_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT,
    avail_date DATE,
    status VARCHAR(20),
    limitation VARCHAR(255)
);

CREATE TABLE invitation (
    invite_id INT AUTO_INCREMENT PRIMARY KEY,
    designer_id INT,
    model_id INT,
    design_id INT,
    description TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT,
    designer_id INT,
    design_id INT,
    status VARCHAR(20) DEFAULT 'pending',
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (email: admin@castflow.com | pass: admin123)
INSERT INTO admin (username, email, password, full_name, role)
VALUES ('admin', 'admin@castflow.com', '$2y$10$wTfZT4w7w2QxL5c1i4n6m.hCqj5nI7S0fE2m6p0sK4w.fO1d9oQxe', 'Super Administrator', 'superadmin')
ON DUPLICATE KEY UPDATE username=username;

