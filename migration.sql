-- MyFSS Migration Script - Safe Updates
-- This script is idempotent (safe to run multiple times)

-- Add is_admin column if not exists
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'is_admin');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT FALSE AFTER tier', 
    'SELECT "Column is_admin already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add military and country statistics columns
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'population');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN population BIGINT DEFAULT 0 AFTER email', 
    'SELECT "Column population already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'gdp');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN gdp DECIMAL(20,2) DEFAULT 0 AFTER population', 
    'SELECT "Column gdp already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'gdp_per_capita');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN gdp_per_capita DECIMAL(15,2) DEFAULT 0 AFTER gdp', 
    'SELECT "Column gdp_per_capita already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'active_navy');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN active_navy INT DEFAULT 0 AFTER gdp_per_capita', 
    'SELECT "Column active_navy already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'active_air_force');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN active_air_force INT DEFAULT 0 AFTER active_navy', 
    'SELECT "Column active_air_force already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'active_army');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN active_army INT DEFAULT 0 AFTER active_air_force', 
    'SELECT "Column active_army already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'reserve_personnel');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN reserve_personnel INT DEFAULT 0 AFTER active_army', 
    'SELECT "Column reserve_personnel already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'defense_equipment');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN defense_equipment INT DEFAULT 0 AFTER reserve_personnel', 
    'SELECT "Column defense_equipment already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'index_factor');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN index_factor DECIMAL(5,2) DEFAULT 1.00 AFTER defense_equipment', 
    'SELECT "Column index_factor already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'military_index');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN military_index DECIMAL(15,3) DEFAULT 0 AFTER index_factor', 
    'SELECT "Column military_index already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'military_spending');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN military_spending DECIMAL(15,2) DEFAULT 0 AFTER military_index', 
    'SELECT "Column military_spending already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'users' 
                   AND column_name = 'last_military_update');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN last_military_update TIMESTAMP NULL AFTER military_spending', 
    'SELECT "Column last_military_update already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Set existing special tier users as admin
UPDATE users SET is_admin = TRUE WHERE tier = 'special' AND is_admin = FALSE;

-- Performance indexes (only create if not exists)
CREATE INDEX IF NOT EXISTS idx_users_admin ON users(is_admin);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_military_index ON users(military_index DESC);
CREATE INDEX IF NOT EXISTS idx_events_dates ON events(date_started, date_ended);
CREATE INDEX IF NOT EXISTS idx_votes_dates ON votes(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_wallet_user ON wallet_transactions(from_uuid, to_uuid);

-- Clean orphaned data
DELETE FROM event_participants WHERE user_uuid NOT IN (SELECT uuid FROM users);
DELETE FROM vote_casts WHERE user_uuid NOT IN (SELECT uuid FROM users);
DELETE FROM wallet_transactions WHERE from_uuid NOT IN (SELECT uuid FROM users) AND from_uuid IS NOT NULL;
DELETE FROM user_documents WHERE user_uuid NOT IN (SELECT uuid FROM users);

SELECT 'Migration completed successfully!' as Result;
