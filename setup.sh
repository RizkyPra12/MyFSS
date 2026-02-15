#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

clear
echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                        ║${NC}"
echo -e "${BLUE}║              MyFSS Interactive Setup                   ║${NC}"
echo -e "${BLUE}║          Fictional State Simulation System             ║${NC}"
echo -e "${BLUE}║                                                        ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo -e "${YELLOW}⚠️  Warning: Running as root. Consider running as web user instead.${NC}"
    read -p "Continue anyway? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check if config.php already exists
if [ -f "config.php" ]; then
    echo -e "${YELLOW}⚠️  config.php already exists!${NC}"
    read -p "Overwrite? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}✗ Setup cancelled${NC}"
        exit 1
    fi
    mv config.php config.php.backup
    echo -e "${GREEN}✓ Backed up to config.php.backup${NC}"
fi

echo ""
echo -e "${BLUE}=== Database Configuration ===${NC}"
echo ""

# Database Configuration
read -p "MySQL/MariaDB Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "MySQL/MariaDB Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database Name [myfss]: " DB_NAME
DB_NAME=${DB_NAME:-myfss}

read -p "Database Username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Database Password: " DB_PASS
echo ""

# Test database connection
echo -e "\n${YELLOW}Testing database connection...${NC}"
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Database connection failed!${NC}"
    echo -e "${YELLOW}Please check your credentials and try again.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Database connection successful${NC}"

# Check if database exists
DB_EXISTS=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME")

if [ -z "$DB_EXISTS" ]; then
    echo -e "\n${YELLOW}Database '$DB_NAME' does not exist.${NC}"
    read -p "Create it now? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Database created${NC}"
        else
            echo -e "${RED}✗ Failed to create database${NC}"
            exit 1
        fi
    else
        echo -e "${RED}✗ Setup cancelled${NC}"
        exit 1
    fi
fi

# Check if tables exist
TABLES_EXIST=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'users';" | grep "users")

if [ -z "$TABLES_EXIST" ]; then
    echo -e "\n${YELLOW}Database is empty. Need to import schema.${NC}"
    if [ ! -f "schema.sql" ]; then
        echo -e "${RED}✗ schema.sql not found!${NC}"
        exit 1
    fi
    read -p "Import schema.sql? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema.sql
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Schema imported${NC}"
        else
            echo -e "${RED}✗ Failed to import schema${NC}"
            exit 1
        fi
    fi
else
    echo -e "\n${YELLOW}Tables already exist. Running migration...${NC}"
    if [ -f "migration.sql" ]; then
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < migration.sql
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Migration completed${NC}"
        else
            echo -e "${YELLOW}⚠️  Migration had warnings (this is usually OK)${NC}"
        fi
    fi
fi

echo ""
echo -e "${BLUE}=== Site Configuration ===${NC}"
echo ""

read -p "Site Name [MyFSS]: " SITE_NAME
SITE_NAME=${SITE_NAME:-MyFSS}

read -p "Site Tagline [Fictional State Simulation]: " SITE_TAGLINE
SITE_TAGLINE=${SITE_TAGLINE:-Fictional State Simulation}

read -p "Site Version [1.0.2]: " SITE_VERSION
SITE_VERSION=${SITE_VERSION:-1.0.2}

echo ""
echo "Select Timezone:"
echo "1) Asia/Jakarta (WIB, UTC+7)"
echo "2) Asia/Makassar (WITA, UTC+8)"
echo "3) Asia/Jayapura (WIT, UTC+9)"
echo "4) UTC"
echo "5) Custom"
read -p "Choice [1]: " TZ_CHOICE
TZ_CHOICE=${TZ_CHOICE:-1}

case $TZ_CHOICE in
    1) TIMEZONE="Asia/Jakarta";;
    2) TIMEZONE="Asia/Makassar";;
    3) TIMEZONE="Asia/Jayapura";;
    4) TIMEZONE="UTC";;
    5) read -p "Enter timezone (e.g., America/New_York): " TIMEZONE;;
    *) TIMEZONE="Asia/Jakarta";;
esac

read -p "Starting FCD Balance [50000]: " START_BALANCE
START_BALANCE=${START_BALANCE:-50000}

read -p "Session Name [MYFSS_SESSION]: " SESSION_NAME
SESSION_NAME=${SESSION_NAME:-MYFSS_SESSION}

# Generate random session secret
SESSION_SECRET=$(openssl rand -hex 32 2>/dev/null || head -c 32 /dev/urandom | xxd -p)

# Create config.php
echo -e "\n${YELLOW}Creating config.php...${NC}"
cat > config.php << ENDCONFIG
<?php
/**
 * MyFSS Configuration
 * Generated by setup.sh on $(date)
 */

// Database Configuration
define('DB_HOST', '$DB_HOST');
define('DB_PORT', '$DB_PORT');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');

// Site Configuration
define('SITE_NAME', '$SITE_NAME');
define('SITE_TAGLINE', '$SITE_TAGLINE');
define('SITE_VERSION', '$SITE_VERSION');

// Timezone
date_default_timezone_set('$TIMEZONE');

// Session Configuration
define('SESSION_NAME', '$SESSION_NAME');
define('SESSION_SECRET', '$SESSION_SECRET');

// Starting Balance
define('START_BALANCE', $START_BALANCE);

// === ICONS (Optional - will use SVG if missing) ===
define('ICONS', [
    'wallet' => 'assets/icons/wallet.webp',
    'upload' => 'assets/icons/upload.webp',
    'doc' => 'assets/icons/upload.webp',  // Shares same icon with upload
    'certs' => 'assets/icons/certs.webp',
    'vote' => 'assets/icons/vote.webp',
    'events' => 'assets/icons/events.webp',
    'settings' => 'assets/icons/settings.webp',
    'about' => 'assets/icons/about.webp',
    'admin' => 'assets/icons/admin.webp',
    'logout' => 'assets/icons/logout.webp'
]);

function getIcon(\$name) {
    if(isset(ICONS[\$name]) && file_exists(ICONS[\$name])) {
        return ICONS[\$name];
    }
    return null;
}

// Paths
define('BASE_PATH', __DIR__);
define('UPLOAD_PATH', BASE_PATH . '/repository/public');
define('DOC_PATH', BASE_PATH . '/repository/users');

// Document Storage Limits (MB per tier)
define('DOC_STORAGE_LIMITS', [
    'free' => 512,
    'tier1' => 512,
    'tier2' => 716.8,
    'tier3' => 1024,
    'special' => 1536,
    'contributor' => 1536
]);

// Age Ranges
define('AGE_RANGES', [
    '13-17' => '13-17 years',
    '18-25' => '18-25 years',
    '26-35' => '26-35 years',
    '36-45' => '36-45 years',
    '46-55' => '46-55 years',
    '56+' => '56+ years'
]);
ENDCONFIG

echo -e "${GREEN}✓ config.php created${NC}"

# Create/fix directories
echo -e "\n${YELLOW}Setting up directories...${NC}"

mkdir -p repository/public repository/users assets/icons assets/css includes pages pages/admin
chmod 755 repository
chmod 775 repository/public repository/users

# Try to set proper ownership
WEB_USER=$(ps aux | grep -E 'apache|httpd|nginx|www-data' | grep -v grep | head -1 | awk '{print $1}')
if [ ! -z "$WEB_USER" ]; then
    echo -e "${YELLOW}Detected web server user: $WEB_USER${NC}"
    read -p "Set ownership to $WEB_USER? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if [ "$EUID" -eq 0 ]; then
            chown -R $WEB_USER:$WEB_USER repository/
            echo -e "${GREEN}✓ Ownership set to $WEB_USER${NC}"
        else
            echo -e "${YELLOW}⚠️  Run this as root to set ownership:${NC}"
            echo "  sudo chown -R $WEB_USER:$WEB_USER repository/"
        fi
    fi
fi

echo -e "${GREEN}✓ Directories created${NC}"

# Create default admin if needed
ADMIN_EXISTS=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM users WHERE username='admin';")

if [ "$ADMIN_EXISTS" -eq 0 ]; then
    echo -e "\n${YELLOW}No admin user found. Creating default admin...${NC}"
    
    # Default password: admin123 (bcrypt hash)
    ADMIN_HASH='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << ENDSQL
INSERT INTO users (
    uuid, username, password, country_name, government_form, ideology, 
    phone_number, tier, is_admin, fcd_balance, created_at
) VALUES (
    '00000000-0000-0000-0000-000000000001',
    'admin',
    '$ADMIN_HASH',
    'System',
    'Democracy',
    'Centrism',
    '000-000-0000',
    'special',
    TRUE,
    999999.00,
    NOW()
);
ENDSQL
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Admin user created${NC}"
        echo -e "${YELLOW}  Username: admin${NC}"
        echo -e "${YELLOW}  Password: admin123${NC}"
        echo -e "${RED}  ⚠️  CHANGE THIS PASSWORD IMMEDIATELY!${NC}"
    fi
fi

# Summary
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                        ║${NC}"
echo -e "${GREEN}║              Setup Completed Successfully!             ║${NC}"
echo -e "${GREEN}║                                                        ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Site Configuration:${NC}"
echo -e "  Name: ${GREEN}$SITE_NAME${NC}"
echo -e "  Database: ${GREEN}$DB_NAME${NC}"
echo -e "  Timezone: ${GREEN}$TIMEZONE${NC}"
echo -e "  Start Balance: ${GREEN}$START_BALANCE FCD${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Configure your web server (Apache/Nginx) to point to this directory"
echo -e "  2. Login with ${YELLOW}admin / admin123${NC}"
echo -e "  3. ${RED}Change admin password immediately!${NC}"
echo -e "  4. Update country information in Settings"
echo ""
echo -e "${BLUE}Documentation:${NC}"
echo -e "  README.md - User guide"
echo -e "  FEATURES.md - Feature list"
echo ""
echo -e "${GREEN}Thank you for using MyFSS!${NC}"
echo ""
