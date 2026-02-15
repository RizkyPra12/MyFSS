-- ============================================================================
-- RyPanel v3.0 FINAL - Complete Database Schema
-- ============================================================================

CREATE DATABASE IF NOT EXISTS fictional_country CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fictional_country;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    country_name VARCHAR(100) NOT NULL,
    government_form VARCHAR(100) NOT NULL,
    ideology VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    flag_url VARCHAR(500),
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    tier VARCHAR(20) DEFAULT 'free',
    credits BIGINT DEFAULT 100,
    last_credit_refill TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    age_range VARCHAR(20) DEFAULT '18-25',
    language VARCHAR(5) DEFAULT 'en',
    theme VARCHAR(10) DEFAULT 'auto',
    current_penalty INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_uuid (uuid),
    INDEX idx_tier (tier)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penalties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penalty_id VARCHAR(20) UNIQUE NOT NULL,
    user_uuid VARCHAR(36) NOT NULL,
    penalty_level INT NOT NULL,
    reason TEXT NOT NULL,
    issued_by VARCHAR(50) NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    revoked_at TIMESTAMP NULL,
    revoked_by VARCHAR(50) NULL,
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE,
    INDEX idx_user (user_uuid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id VARCHAR(20) UNIQUE NOT NULL,
    vote_title VARCHAR(200) NOT NULL,
    vote_description TEXT,
    vote_type ENUM('vote','election','polling') NOT NULL,
    options JSON NOT NULL,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    created_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id VARCHAR(20) NOT NULL,
    user_uuid VARCHAR(36) NOT NULL,
    selected_option VARCHAR(100) NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vote_id) REFERENCES votes(vote_id) ON DELETE CASCADE,
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE,
    UNIQUE KEY unique_user_vote (vote_id, user_uuid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(20) UNIQUE NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    event_participant TEXT,
    date_started DATE NOT NULL,
    date_ended DATE NOT NULL,
    max_participants INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id VARCHAR(50) UNIQUE NOT NULL,
    event_id VARCHAR(20) NOT NULL,
    user_uuid VARCHAR(36) NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_event_user (event_id, user_uuid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_rejoin_grace (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(20) NOT NULL,
    user_uuid VARCHAR(36) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_grace (event_id, user_uuid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cert_id VARCHAR(20) UNIQUE NOT NULL,
    cert_name VARCHAR(100) NOT NULL,
    assigned_user VARCHAR(50) NOT NULL,
    issued_date DATE NOT NULL,
    cert_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS credit_transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_uuid VARCHAR(36) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    credit_change BIGINT NOT NULL,
    credit_before BIGINT NOT NULL,
    credit_after BIGINT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE,
    INDEX idx_user (user_uuid)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS analytics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    device_category VARCHAR(20) NOT NULL,
    screen_width INT NOT NULL,
    screen_height INT NOT NULL,
    os_name VARCHAR(50),
    browser_name VARCHAR(50),
    country_code VARCHAR(5),
    language_code VARCHAR(5),
    duration INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS uploads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    file_id VARCHAR(40) UNIQUE NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    file_extension VARCHAR(20),
    mime_type VARCHAR(100),
    file_path VARCHAR(500) NOT NULL,
    public_url VARCHAR(500) NOT NULL,
    repository VARCHAR(20) NOT NULL,
    uploaded_by_uuid VARCHAR(36) NOT NULL,
    uploaded_by_username VARCHAR(50) NOT NULL,
    credits_charged INT DEFAULT 0,
    upload_date DATE NOT NULL,
    upload_time TIME NOT NULL,
    can_delete BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by_uuid) REFERENCES users(uuid) ON DELETE CASCADE,
    INDEX idx_repository (repository),
    INDEX idx_user (uploaded_by_uuid)
) ENGINE=InnoDB;

INSERT IGNORE INTO users (uuid, username, country_name, government_form, ideology, phone_number, password, is_admin, tier, credits, theme) VALUES
('00000000-0000-0000-0000-000000000001', 'admin', 'Admin Country', 'Republic', 'Democracy', '+0000000000', '$argon2id$v=19$m=65536,t=4,p=1$RzNYUzBXTlhKUVBOSFFJQQ$vfVQYWqmFOVqWpBvGEv9ROO7yMnK++RrPmhHrskKowo', TRUE, 'contributor', 999999999, 'dark');
