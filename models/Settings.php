<?php
// File: /models/Settings.php
require_once __DIR__ . '/../config/database.php';

class Settings {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings() {
        try {
            $query = "SELECT * FROM settings ORDER BY setting_key";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $settings = [];
            $results = $stmt->fetchAll();
            
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("Get all settings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a specific setting
     */
    public function getSetting($key) {
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':key' => $key]);
            $result = $stmt->fetch();
            
            return $result ? $result['setting_value'] : null;
        } catch (PDOException $e) {
            error_log("Get setting error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update or insert a setting
     */
    public function setSetting($key, $value) {
        try {
            // Check if setting exists
            $checkQuery = "SELECT id FROM settings WHERE setting_key = :key";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':key' => $key]);
            
            if ($checkStmt->fetch()) {
                // Update existing
                $query = "UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key";
            } else {
                // Insert new
                $query = "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())";
            }
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':key' => $key,
                ':value' => $value
            ]);
        } catch (PDOException $e) {
            error_log("Set setting error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateSettings($settings) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->setSetting($key, $value);
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Update settings error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get general settings
     */
    public function getGeneralSettings() {
        $settings = $this->getAllSettings();
        return [
            'site_name' => $settings['site_name'] ?? 'Rays of Grace E-Learning',
            'site_description' => $settings['site_description'] ?? 'Quality education for every child, anywhere, anytime.',
            'contact_email' => $settings['contact_email'] ?? 'info@raysofgrace.com'
        ];
    }
    
    /**
     * Get subscription settings
     */
    public function getSubscriptionSettings() {
        $settings = $this->getAllSettings();
        return [
            'monthly_price' => $settings['monthly_price'] ?? 15000,
            'termly_price' => $settings['termly_price'] ?? 40000,
            'yearly_price' => $settings['yearly_price'] ?? 120000,
            'trial_days' => $settings['trial_days'] ?? 60
        ];
    }
    
    /**
     * Get email settings
     */
    public function getEmailSettings() {
        $settings = $this->getAllSettings();
        return [
            'smtp_host' => $settings['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => $settings['smtp_port'] ?? 587,
            'smtp_username' => $settings['smtp_username'] ?? 'noreply@raysofgrace.com',
            'smtp_password' => $settings['smtp_password'] ?? '',
            'from_email' => $settings['from_email'] ?? 'noreply@raysofgrace.com'
        ];
    }
    
    /**
     * Get security settings
     */
    public function getSecuritySettings() {
        $settings = $this->getAllSettings();
        return [
            'enable_2fa' => $settings['enable_2fa'] ?? true,
            'session_timeout' => $settings['session_timeout'] ?? 60,
            'strong_passwords' => $settings['strong_passwords'] ?? true
        ];
    }
    
    /**
     * Get appearance settings
     */
    public function getAppearanceSettings() {
        $settings = $this->getAllSettings();
        return [
            'theme_color' => $settings['theme_color'] ?? '#8B5CF6',
            'accent_color' => $settings['accent_color'] ?? '#F97316',
            'dark_mode' => $settings['dark_mode'] ?? true
        ];
    }
    
    /**
     * Reset to defaults
     */
    public function resetToDefaults() {
        try {
            $this->conn->beginTransaction();
            
            // Delete all settings
            $this->conn->exec("DELETE FROM settings");
            
            // Insert defaults
            $defaults = [
                ['site_name', 'Rays of Grace E-Learning'],
                ['site_description', 'Quality education for every child, anywhere, anytime.'],
                ['contact_email', 'info@raysofgrace.com'],
                ['monthly_price', '15000'],
                ['termly_price', '40000'],
                ['yearly_price', '120000'],
                ['trial_days', '60'],
                ['smtp_host', 'smtp.gmail.com'],
                ['smtp_port', '587'],
                ['smtp_username', 'noreply@raysofgrace.com'],
                ['smtp_password', ''],
                ['from_email', 'noreply@raysofgrace.com'],
                ['enable_2fa', '1'],
                ['session_timeout', '60'],
                ['strong_passwords', '1'],
                ['theme_color', '#8B5CF6'],
                ['accent_color', '#F97316'],
                ['dark_mode', '1']
            ];
            
            $insertQuery = "INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())";
            $stmt = $this->conn->prepare($insertQuery);
            
            foreach ($defaults as $default) {
                $stmt->execute([
                    ':key' => $default[0],
                    ':value' => $default[1]
                ]);
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Reset to defaults error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear cache (you can implement your cache clearing logic here)
     */
    public function clearCache() {
        // Implement your cache clearing logic
        // For example, delete temporary files, clear session data, etc.
        return true;
    }
}
?>