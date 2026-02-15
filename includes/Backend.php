<?php
/**
 * MyFSS Backend Classes
 * Minimal version - no errors
 */

// === DATABASE CLASS ===
class DB {
    private static $pdo = null;
    
    public static function connect() {
        if(self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch(PDOException $e) {
                die('Database connection failed. Check config.php');
            }
        }
        return self::$pdo;
    }
    
    public static function q($sql, $params = []) {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// === HELPER CLASS ===
class H {
    public static function s($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public static function date($date) {
        return date('Y-m-d', strtotime($date));
    }
    
    public static function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if($diff < 60) return 'just now';
        if($diff < 3600) return floor($diff/60) . ' min ago';
        if($diff < 86400) return floor($diff/3600) . ' hours ago';
        return floor($diff/86400) . ' days ago';
    }
}

// === AUTH CLASS ===
class Auth {
    public static function check() {
        return isset($_SESSION['user_uuid']);
    }
    
    public static function uid() {
        return $_SESSION['user_uuid'] ?? null;
    }
    
    public static function login($username, $password) {
        $user = DB::q("SELECT * FROM users WHERE username = ?", [$username])->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_uuid'] = $user['uuid'];
            DB::q("UPDATE users SET last_login = NOW() WHERE uuid = ?", [$user['uuid']]);
            return true;
        }
        return false;
    }
    
    public static function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }
    
    public static function isAdmin() {
        if(!self::check()) return false;
        $user = DB::q("SELECT tier FROM users WHERE uuid = ?", [self::uid()])->fetch();
        return $user && $user['tier'] === 'special';
    }
}

// === USER CLASS ===
class User {
    public static function get($uuid = null) {
        $uuid = $uuid ?? Auth::uid();
        return DB::q("SELECT * FROM users WHERE uuid = ?", [$uuid])->fetch();
    }
    
    public static function exists($username) {
        $user = DB::q("SELECT uuid FROM users WHERE username = ?", [$username])->fetch();
        return $user !== false;
    }
    
    public static function create($data) {
        $uuid = self::uuid();
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        DB::q("INSERT INTO users (uuid, username, password, country_name, government_form, ideology, phone_number, email, flag_url, age_range, fcd_balance, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
            $uuid,
            $data['username'],
            $password,
            $data['country_name'] ?? '',
            $data['government_form'] ?? '',
            $data['ideology'] ?? '',
            $data['phone_number'] ?? '',
            $data['email'] ?? null,
            $data['flag_url'] ?? null,
            $data['age_range'] ?? '18-25',
            FCD_STARTING_BALANCE
        ]);
        
        return $uuid;
    }
    
    public static function update($uuid, $data) {
        $sets = [];
        $params = [];
        
        foreach($data as $key => $value) {
            if($key !== 'uuid' && $key !== 'username') {
                $sets[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $uuid;
        DB::q("UPDATE users SET " . implode(', ', $sets) . " WHERE uuid = ?", $params);
    }
    
    private static function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

// === WALLET CLASS ===
class Wallet {
    public static function balance($uuid) {
        $user = DB::q("SELECT fcd_balance FROM users WHERE uuid = ?", [$uuid])->fetch();
        return $user ? (float)$user['fcd_balance'] : 0;
    }
    
    public static function hasPin($uuid) {
        $pin = DB::q("SELECT pin FROM wallet_pins WHERE user_uuid = ?", [$uuid])->fetch();
        return $pin !== false;
    }
    
    public static function setPin($uuid, $pin) {
        $hashed = password_hash($pin, PASSWORD_DEFAULT);
        DB::q("INSERT INTO wallet_pins (user_uuid, pin) VALUES (?, ?) ON DUPLICATE KEY UPDATE pin = ?, updated_at = NOW()", [$uuid, $hashed, $hashed]);
    }
    
    public static function verifyPin($uuid, $pin) {
        if(!self::hasPin($uuid)) return true; // No PIN set = allow
        
        $stored = DB::q("SELECT pin FROM wallet_pins WHERE user_uuid = ?", [$uuid])->fetch();
        return $stored && password_verify($pin, $stored['pin']);
    }
    
    public static function transfer($from_uuid, $to_username, $amount, $memo = '', $pin = '') {
        if(!self::verifyPin($from_uuid, $pin)) {
            return ['ok' => false, 'msg' => 'Incorrect PIN'];
        }
        
        if($amount < 1) {
            return ['ok' => false, 'msg' => 'Amount must be at least 1 FCD'];
        }
        
        $to = DB::q("SELECT uuid FROM users WHERE username = ?", [$to_username])->fetch();
        if(!$to) {
            return ['ok' => false, 'msg' => 'User not found'];
        }
        
        if(self::balance($from_uuid) < $amount) {
            return ['ok' => false, 'msg' => 'Insufficient balance'];
        }
        
        DB::q("UPDATE users SET fcd_balance = fcd_balance - ? WHERE uuid = ?", [$amount, $from_uuid]);
        DB::q("UPDATE users SET fcd_balance = fcd_balance + ? WHERE uuid = ?", [$amount, $to['uuid']]);
        
        $txid = 'TX' . strtoupper(bin2hex(random_bytes(6)));
        DB::q("INSERT INTO wallet_transactions (txn_id, from_uuid, to_uuid, type, amount, memo) VALUES (?, ?, ?, 'transfer', ?, ?)", [$txid, $from_uuid, $to['uuid'], $amount, $memo]);
        
        return ['ok' => true, 'msg' => 'Transfer successful'];
    }
    
    public static function history($uuid, $limit = 30) {
        return DB::q("SELECT t.*, 
            u1.username as from_user, 
            u2.username as to_user 
            FROM wallet_transactions t 
            LEFT JOIN users u1 ON t.from_uuid = u1.uuid 
            LEFT JOIN users u2 ON t.to_uuid = u2.uuid 
            WHERE t.from_uuid = ? OR t.to_uuid = ? 
            ORDER BY t.created_at DESC 
            LIMIT ?", [$uuid, $uuid, $limit])->fetchAll();
    }
}

// === MAINTENANCE CLASSES (Empty but present) ===
class Credits {
    public static function refillAll() {}
}

class Penalty {
    public static function checkExpired() {}
}

class Voting {
    public static function checkMissedElections() {}
}

class Events {
    public static function all() { return []; }
    public static function show($e) { return true; }
}

class Certs {
    public static function forUser($u) { return []; }
}
