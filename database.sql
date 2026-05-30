-- GeoTrack Pro Database Schema for MySQL
-- Import this into your InfinityFree PHPMyAdmin

CREATE TABLE IF NOT EXISTS devices (
    device_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100),
    lat DECIMAL(10, 8) DEFAULT 0,
    lon DECIMAL(11, 8) DEFAULT 0,
    speed INT DEFAULT 0,
    battery INT DEFAULT 0,
    battery_health VARCHAR(50) DEFAULT 'Unknown',
    charging BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    version VARCHAR(20) DEFAULT '0',
    status VARCHAR(20) DEFAULT 'offline'
);

CREATE TABLE IF NOT EXISTS commands (
    device_id VARCHAR(50) PRIMARY KEY,
    camera VARCHAR(10) DEFAULT 'back',
    audio VARCHAR(10) DEFAULT 'off',
    video VARCHAR(10) DEFAULT 'off',
    power VARCHAR(20) DEFAULT NULL,
    alarm VARCHAR(10) DEFAULT 'off',
    gps VARCHAR(10) DEFAULT 'on',
    file_cmd TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50),
    lat DECIMAL(10, 8),
    lon DECIMAL(11, 8),
    speed INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(device_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS file_responses (
    device_id VARCHAR(50),
    type VARCHAR(20),
    data LONGTEXT,
    file_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default User
INSERT INTO users (username, password) VALUES ('marvin', '$2y$10$KnCP87gmL9vJW04KHUOBI.Q/D82.M/ReWCF66KjEi6a1Y6AvZbic.'); 
-- Password is 'marvin' hashed with bcrypt. 
-- Wait, the user asked for username 'marvin', password 'marvin'. 
-- I will use a simple check or provide the hash. I'll use password_hash in PHP.
