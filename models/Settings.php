<?php
// File: /models/Settings.php - Optimized Settings Model
require_once __DIR__ . '/../config/database.php';

class Settings {
    private $db;
    private $conn;
    private $settingsCache = null;
    private $groupCache = [];

    const DEFAULTS = [
        'site_name' => 'Rays of Grace E-Learning',
        'site_description' => 'Quality education for every child, anywhere, anytime.',
        'contact_email' => 'info@raysofgrace.ac.ug',
        'monthly_price' => 15000,
        'termly_price' => 40000,
        'yearly_price' => 120000,
        'trial_days' => 60,
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'noreply@raysofgrace.ac.ug',
        'smtp_password' => '',
        'from_email' => 'noreply@raysofgrace.ac.ug',
        'enable_2fa' => true,
        'session_timeout' => 60,
        'strong_passwords' => true,
        'theme_color' => '#8B5CF6',
        'accent_color' => '#F97316',
        'dark_mode' => true
    ];
    
    private $defaultSettingsList = [
        ['site_name', 'Rays of Grace E-Learning'],
        ['site_description', 'Quality education for every child, anywhere, anytime.'],
        ['contact_email', 'info@raysofgrace.ac.ug'],
        ['monthly_price', '15000'],
        ['termly_price', '40000'],
        ['yearly_price', '120000'],
        ['trial_days', '60'],
        ['smtp_host', 'smtp.gmail.com'],
        ['smtp_port', '587'],
        ['smtp_username', 'noreply@raysofgrace.ac.ug'],
        ['smtp_password', ''],
        ['from_email', 'noreply@raysofgrace.ac.ug'],
        ['enable_2fa', '1'],
        ['session_timeout', '60'],
        ['strong_passwords', '1'],
        ['theme_color', '#8B5CF6'],
        ['accent_color', '#F97316'],
        ['dark_mode', '1']
    ];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // ==================== HELPER METHODS ====================
    private function loadAllSettings() {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }
        
        try {
            $query = "SELECT setting_key, setting_value FROM settings ORDER BY setting_key";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $this->settingsCache = [];
            $results = $stmt->fetchAll();
            
            foreach ($results as $row) {
                $this->settingsCache[$row['setting_key']] = $row['setting_value'];
            }
            
            return $this->settingsCache;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function invalidateCache() {
        $this->settingsCache = null;
        $this->groupCache = [];
    }
    
    private function getWithDefault($key, $default) {
        $settings = $this->loadAllSettings();
        $value = $settings[$key] ?? $default;
        
        if (in_array($key, ['enable_2fa', 'strong_passwords', 'dark_mode'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if (in_array($key, ['monthly_price', 'termly_price', 'yearly_price', 'trial_days', 'session_timeout', 'smtp_port'])) {
            return (int)$value;
        }
        
        return $value;
    }
    
    // ==================== PUBLIC METHODS ====================
    public function getAllSettings() {
        return $this->loadAllSettings();
    }
    
    public function getSetting($key) {
        $settings = $this->loadAllSettings();
        return $settings[$key] ?? null;
    }
    
    public function get($key, $default = null) {
        $settings = $this->loadAllSettings();
        return $settings[$key] ?? $default;
    }
    
    public function setSetting($key, $value) {
        try {
            $checkQuery = "SELECT id FROM settings WHERE setting_key = :key";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':key' => $key]);
            
            if ($checkStmt->fetch()) {
                $query = "UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key";
            } else {
                $query = "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())";
            }
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':key' => $key, ':value' => $value]);
            
            if ($result) {
                $this->invalidateCache();
            }
            
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateSettings($settings) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->setSetting($key, $value);
            }
            
            $this->conn->commit();
            $this->invalidateCache();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function getGeneralSettings() {
        return [
            'site_name' => $this->getWithDefault('site_name', self::DEFAULTS['site_name']),
            'site_description' => $this->getWithDefault('site_description', self::DEFAULTS['site_description']),
            'contact_email' => $this->getWithDefault('contact_email', self::DEFAULTS['contact_email'])
        ];
    }
    
    public function getSubscriptionSettings() {
        return [
            'monthly_price' => $this->getWithDefault('monthly_price', self::DEFAULTS['monthly_price']),
            'termly_price' => $this->getWithDefault('termly_price', self::DEFAULTS['termly_price']),
            'yearly_price' => $this->getWithDefault('yearly_price', self::DEFAULTS['yearly_price']),
            'trial_days' => $this->getWithDefault('trial_days', self::DEFAULTS['trial_days'])
        ];
    }
    
    public function getEmailSettings() {
        return [
            'smtp_host' => $this->getWithDefault('smtp_host', self::DEFAULTS['smtp_host']),
            'smtp_port' => $this->getWithDefault('smtp_port', self::DEFAULTS['smtp_port']),
            'smtp_username' => $this->getWithDefault('smtp_username', self::DEFAULTS['smtp_username']),
            'smtp_password' => $this->getWithDefault('smtp_password', self::DEFAULTS['smtp_password']),
            'from_email' => $this->getWithDefault('from_email', self::DEFAULTS['from_email'])
        ];
    }
    
    public function getSecuritySettings() {
        return [
            'enable_2fa' => $this->getWithDefault('enable_2fa', self::DEFAULTS['enable_2fa']),
            'session_timeout' => $this->getWithDefault('session_timeout', self::DEFAULTS['session_timeout']),
            'strong_passwords' => $this->getWithDefault('strong_passwords', self::DEFAULTS['strong_passwords'])
        ];
    }
    
    public function getAppearanceSettings() {
        return [
            'theme_color' => $this->getWithDefault('theme_color', self::DEFAULTS['theme_color']),
            'accent_color' => $this->getWithDefault('accent_color', self::DEFAULTS['accent_color']),
            'dark_mode' => $this->getWithDefault('dark_mode', self::DEFAULTS['dark_mode'])
        ];
    }
    
    public function resetToDefaults() {
        try {
            $this->conn->beginTransaction();
            
            $this->conn->exec("DELETE FROM settings");
            
            $insertQuery = "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())";
            $stmt = $this->conn->prepare($insertQuery);
            
            foreach ($this->defaultSettingsList as $default) {
                $stmt->execute([':key' => $default[0], ':value' => $default[1]]);
            }
            
            $this->conn->commit();
            $this->invalidateCache();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function clearCache() {
        $this->invalidateCache();
        return true;
    }
    
    public function getSettingsByGroup($group) {
        if (isset($this->groupCache[$group])) {
            return $this->groupCache[$group];
        }
        
        try {
            $sql = "SELECT setting_key, setting_value FROM settings WHERE setting_group = :group";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':group', $group);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            $this->groupCache[$group] = $settings;
            return $settings;
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>