CREATE DATABASE IF NOT EXISTS event_db;
USE event_db;

CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    username VARCHAR(50),
    password VARCHAR(255),
    referral VARCHAR(50),
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
);

-- Default admin login
INSERT INTO admin (username, password) VALUES ('admin', MD5('admin123'));
