<?php
// File: /models/Notification.php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;

    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }

    public static function create($type, $title, $message, $link = null) {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("INSERT INTO notifications (type, title, message, link, created_at) VALUES (?, ?, ?, ?, NOW())");
            return $stmt->execute([$type, $title, $message, $link]);
        } catch (PDOException $e) {
            error_log("Notification Create Error: " . $e->getMessage());
            return false;
        }
    }

    public function getLatestNotifications($limit = 8) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUnreadCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE is_read = 0");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['unread'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function markAllAsRead() {
        try {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}